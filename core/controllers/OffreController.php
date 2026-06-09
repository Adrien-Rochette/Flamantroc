<?php
declare(strict_types=1);

final class OffreController extends Controller
{
    public function liste(): void
    {
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);
        unset($_SESSION['show_purchase_confirmation']);

        $objetModel = new Objet();
        $categorieModel = new Categorie();
        $currentUserId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        $categories = $categorieModel->all();
        $regions = $objetModel->availableRegions();
        $sellerLetters = $objetModel->availableSellerInitials();
        $filters = $this->buildOfferFilters($categories, $regions, $sellerLetters);

        $this->view('offres/liste', [
            'pageTitle' => 'Offres',
            'objets' => $objetModel->search($currentUserId, $filters),
            'categories' => $categories,
            'regions' => $regions,
            'sellerLetters' => $sellerLetters,
            'filters' => $filters,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function creer(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $categorieModel = new Categorie();

        $this->view('offres/creer', [
            'pageTitle' => 'Publier une offre',
            'categories' => $categorieModel->all(),
            'old' => [],
        ]);
    }

    public function store(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $categorieModel = new Categorie();
        $categories = $categorieModel->all();

        $nom = trim((string)($_POST['nom'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $prixInput = trim((string)($_POST['prix'] ?? ''));
        $categorieId = (int)($_POST['id_categorie'] ?? 0);

        $old = [
            'nom' => $nom,
            'description' => $description,
            'prix' => $prixInput,
            'id_categorie' => $categorieId,
        ];

        $errors = [];

        if ($nom === '') {
            $errors[] = 'Le nom est obligatoire.';
        } elseif (strlen($nom) > 100) {
            $errors[] = 'Le nom doit faire 100 caracteres maximum.';
        }

        if ($categorieId <= 0 || $categorieModel->findById($categorieId) === null) {
            $errors[] = 'Choisis une categorie valide.';
        }

        if ($prixInput === '') {
            $errors[] = 'Le prix est obligatoire.';
        } elseif (!preg_match('/^\d+(?:[,.]\d{1,2})?$/', $prixInput)) {
            $errors[] = 'Le prix doit etre un nombre sans unite.';
        }

        if ($errors !== []) {
            $this->renderCreateWithError($categories, $old, implode(' ', $errors));
            return;
        }

        $prix = number_format((float)str_replace(',', '.', $prixInput), 2, '.', '');
        $uploadedImage = $this->storeUploadedImage();

        if (isset($uploadedImage['error'])) {
            $this->renderCreateWithError($categories, $old, $uploadedImage['error']);
            return;
        }

        try {
            $objetModel = new Objet();
            $objetModel->create([
                'nom' => $nom,
                'description' => $description === '' ? null : $description,
                'prix' => $prix,
                'image' => $uploadedImage['path'],
                'id_utilisateur' => (int)$_SESSION['user']['id'],
                'id_categorie' => $categorieId,
            ]);
        } catch (Throwable $exception) {
            if (isset($uploadedImage['full_path']) && is_string($uploadedImage['full_path'])) {
                @unlink($uploadedImage['full_path']);
            }

            $this->renderCreateWithError($categories, $old, "Impossible d'enregistrer l'annonce.");
            return;
        }

        $_SESSION['success'] = 'Annonce publiee.';
        $this->redirect('/profil');
    }

    public function detail(): void
    {
        $objetId = (int)($_GET['id'] ?? 0);
        if ($objetId <= 0) {
            $_SESSION['error'] = 'Offre introuvable.';
            $this->redirect('/offres');
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        $showPurchaseConfirmation = isset($_SESSION['show_purchase_confirmation'])
            && (int)($_SESSION['pending_purchase_id'] ?? 0) === $objetId;
        unset($_SESSION['success'], $_SESSION['error']);
        unset($_SESSION['show_purchase_confirmation']);

        $objetModel = new Objet();
        $currentUserId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        $objet = $objetModel->findById($objetId, $currentUserId);

        if ($objet === null || (int)$objet['est_dispo'] !== 1) {
            $_SESSION['error'] = 'Cette offre n est plus disponible.';
            $this->redirect('/offres');
        }

        $this->view('offres/detail', [
            'pageTitle' => 'Detail de l offre',
            'objet' => $objet,
            'success' => $success,
            'error' => $error,
            'showPurchaseConfirmation' => $showPurchaseConfirmation,
        ]);
    }

    public function contact(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $objetId = (int)($_POST['id_objet'] ?? 0);
        $contenu = trim((string)($_POST['contenu'] ?? ''));

        if ($objetId <= 0) {
            $_SESSION['error'] = 'Offre introuvable.';
            $this->redirect('/offres');
        }

        if ($contenu === '') {
            $_SESSION['error'] = 'Ecris un message avant de contacter le vendeur.';
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        if (strlen($contenu) > 2000) {
            $_SESSION['error'] = 'Le message doit faire 2000 caracteres maximum.';
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        $objetModel = new Objet();
        $objet = $objetModel->findById($objetId);

        if ($objet === null || (int)$objet['est_dispo'] !== 1) {
            $_SESSION['error'] = 'Cette offre n est plus disponible.';
            $this->redirect('/offres');
        }

        $currentUserId = (int)$_SESSION['user']['id'];
        $sellerId = (int)$objet['id_utilisateur'];

        if ($sellerId === $currentUserId) {
            $_SESSION['error'] = 'Tu ne peux pas te contacter toi-meme.';
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        $propositionModel = new Proposition();
        $conversation = $propositionModel->findConversationForObjet($objetId, $currentUserId, $sellerId);
        $propositionId = $conversation !== null
            ? (int)$conversation['id_proposition']
            : $propositionModel->createConversationForObjet($objetId, $currentUserId, $sellerId, (float)$objet['prix']);

        $messageModel = new Message();
        $messageModel->create([
            'contenu' => $contenu,
            'id_proposition' => $propositionId,
            'id_utilisateur' => $currentUserId,
        ]);

        $_SESSION['success'] = 'Message envoye.';
        $this->redirect('/messages/conversation?id=' . $propositionId);
    }

    private function renderCreateWithError(array $categories, array $old, string $error): void
    {
        $this->view('offres/creer', [
            'pageTitle' => 'Publier une offre',
            'categories' => $categories,
            'old' => $old,
            'error' => $error,
        ]);
    }

    private function buildOfferFilters(array $categories, array $regions, array $sellerLetters): array
    {
        $filters = [
            'vendeur' => '',
            'categorie' => 0,
            'prix_min' => null,
            'prix_max' => null,
            'region' => '',
        ];

        $sellerInitial = strtoupper($this->queryValue('vendeur'));
        if ($sellerInitial !== '' && in_array($sellerInitial, $sellerLetters, true)) {
            $filters['vendeur'] = $sellerInitial;
        }

        $categoryId = (int)$this->queryValue('categorie');
        $categoryIds = array_map(static fn (array $categorie): int => (int)$categorie['id_categorie'], $categories);
        if ($categoryId > 0 && in_array($categoryId, $categoryIds, true)) {
            $filters['categorie'] = $categoryId;
        }

        $minPrice = $this->parsePriceFilter($this->queryValue('prix_min'));
        $maxPrice = $this->parsePriceFilter($this->queryValue('prix_max'));

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $filters['prix_min'] = $minPrice;
        $filters['prix_max'] = $maxPrice;

        $region = $this->queryValue('region');
        if ($region !== '' && in_array($region, $regions, true)) {
            $filters['region'] = $region;
        }

        return $filters;
    }

    private function queryValue(string $key): string
    {
        $value = $_GET[$key] ?? '';
        if (!is_scalar($value)) {
            return '';
        }

        return trim((string)$value);
    }

    private function parsePriceFilter(string $value): ?float
    {
        $normalized = str_replace(',', '.', trim($value));
        if ($normalized === '' || !preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            return null;
        }

        return (float)$normalized;
    }

    private function storeUploadedImage(): array
    {
        $file = $_FILES['image'] ?? null;

        if (
            !is_array($file)
            || is_array($file['error'] ?? null)
            || is_array($file['tmp_name'] ?? null)
            || is_array($file['size'] ?? null)
        ) {
            return ['error' => 'Fichier invalide.'];
        }

        if ((int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['error' => 'Choisis une image pour ton annonce.'];
        }

        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => "Impossible d'envoyer cette image."];
        }

        if ((int)($file['size'] ?? 0) > 4 * 1024 * 1024) {
            return ['error' => "L'image doit faire moins de 4 Mo."];
        }

        $temporaryPath = (string)($file['tmp_name'] ?? '');
        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            return ['error' => 'Fichier invalide.'];
        }

        $extensionsByMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        $imageInfo = getimagesize($temporaryPath);
        $mimeType = is_array($imageInfo) ? ($imageInfo['mime'] ?? null) : null;

        if (!is_string($mimeType) || !isset($extensionsByMime[$mimeType])) {
            return ['error' => 'Formats acceptes : JPG, PNG, WebP ou GIF.'];
        }

        $uploadDirectory = PROJECT_ROOT . '/public/images/uploads';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
            return ['error' => "Impossible de preparer le dossier d'upload."];
        }

        $fileName = 'objet_' . (int)$_SESSION['user']['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $extensionsByMime[$mimeType];
        $destination = $uploadDirectory . '/' . $fileName;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            return ['error' => "Impossible d'enregistrer l'image."];
        }

        return [
            'path' => 'images/uploads/' . $fileName,
            'full_path' => $destination,
        ];
    }
}
