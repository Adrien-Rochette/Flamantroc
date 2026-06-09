<?php
declare(strict_types=1);

final class ProfilController extends Controller
{
    private const FRENCH_REGIONS = [
        'Auvergne-Rhone-Alpes',
        'Bourgogne-Franche-Comte',
        'Bretagne',
        'Centre-Val de Loire',
        'Corse',
        'Grand Est',
        'Hauts-de-France',
        'Ile-de-France',
        'Normandie',
        'Nouvelle-Aquitaine',
        'Occitanie',
        'Pays de la Loire',
        'Provence-Alpes-Cote d\'Azur',
        'Guadeloupe',
        'Martinique',
        'Guyane',
        'La Reunion',
        'Mayotte',
    ];

    public function index(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $userModel = new User();
        $user = $userModel->findById((int)$_SESSION['user']['id']);

        if ($user === null) {
            unset($_SESSION['user']);
            $this->redirect('/login');
        }

        $_SESSION['user']['image_profil'] = $user['image_profil'] ?? null;
        $_SESSION['user']['poisson'] = $user['poisson'] ?? 0;
        $_SESSION['user']['description'] = $user['description'] ?? null;
        $_SESSION['user']['ville'] = $user['ville'] ?? null;
        $_SESSION['user']['region'] = $user['region'] ?? null;

        $objetModel = new Objet();

        $this->view('profil/index', [
            'pageTitle' => 'Mon profil',
            'user' => $user,
            'objets' => $objetModel->findByUtilisateur((int)$_SESSION['user']['id']),
            'regions' => self::FRENCH_REGIONS,
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function updatePhoto(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $file = $_FILES['image_profil'] ?? null;

        if (
            !is_array($file)
            || is_array($file['error'] ?? null)
            || is_array($file['tmp_name'] ?? null)
            || is_array($file['size'] ?? null)
        ) {
            $this->redirectWithMessage('error', 'Fichier invalide.');
        }

        if ((int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $this->redirectWithMessage('error', 'Choisis une image avant de valider.');
        }

        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            $this->redirectWithMessage('error', "Impossible d'envoyer cette image.");
        }

        if ((int)($file['size'] ?? 0) > 2 * 1024 * 1024) {
            $this->redirectWithMessage('error', 'La photo doit faire moins de 2 Mo.');
        }

        $temporaryPath = (string)($file['tmp_name'] ?? '');
        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            $this->redirectWithMessage('error', 'Fichier invalide.');
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
            $this->redirectWithMessage('error', 'Formats acceptes : JPG, PNG, WebP ou GIF.');
        }

        $uploadDirectory = PROJECT_ROOT . '/public/images/uploads';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
            $this->redirectWithMessage('error', "Impossible de preparer le dossier d'upload.");
        }

        $fileName = 'profil_' . (int)$_SESSION['user']['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $extensionsByMime[$mimeType];
        $destination = $uploadDirectory . '/' . $fileName;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            $this->redirectWithMessage('error', "Impossible d'enregistrer la photo.");
        }

        $imagePath = 'images/uploads/' . $fileName;

        $userModel = new User();
        $userModel->updateProfileImage((int)$_SESSION['user']['id'], $imagePath);

        $_SESSION['user']['image_profil'] = $imagePath;

        $this->redirectWithMessage('success', 'Profil mis a jour.');
    }

    public function updateDetails(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $description = trim((string)($_POST['description'] ?? ''));
        $ville = trim((string)($_POST['ville'] ?? ''));
        $region = trim((string)($_POST['region'] ?? ''));

        if (strlen($description) > 2000) {
            $this->redirectWithMessage('error', 'La description doit faire 2000 caracteres maximum.');
        }

        if (strlen($ville) > 100) {
            $this->redirectWithMessage('error', 'La ville doit faire 100 caracteres maximum.');
        }

        if (strlen($region) > 100) {
            $this->redirectWithMessage('error', 'La region doit faire 100 caracteres maximum.');
        }

        if ($region !== '' && !in_array($region, self::FRENCH_REGIONS, true)) {
            $this->redirectWithMessage('error', 'Choisis une region dans la liste.');
        }

        $descriptionValue = $description === '' ? null : $description;
        $villeValue = $ville === '' ? null : $ville;
        $regionValue = $region === '' ? null : $region;

        $userModel = new User();
        $userModel->updateProfileDetails((int)$_SESSION['user']['id'], $descriptionValue, $villeValue, $regionValue);

        $_SESSION['user']['description'] = $descriptionValue;
        $_SESSION['user']['ville'] = $villeValue;
        $_SESSION['user']['region'] = $regionValue;

        $this->redirectWithMessage('success', 'Informations du profil mises a jour.');
    }

    public function updateOfferAvailability(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $objetId = (int)($_POST['id_objet'] ?? 0);
        $isAvailable = (int)($_POST['est_dispo'] ?? 0) === 1;

        if ($objetId <= 0) {
            $this->redirectWithMessage('error', 'Annonce introuvable.');
        }

        $objetModel = new Objet();
        if (!$objetModel->updateAvailabilityForOwner($objetId, (int)$_SESSION['user']['id'], $isAvailable)) {
            $this->redirectWithMessage('error', "Impossible de modifier cette annonce.");
        }

        $this->redirectWithMessage('success', $isAvailable ? 'Annonce remise en ligne.' : 'Annonce retiree des offres.');
    }

    public function deleteOffer(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $objetId = (int)($_POST['id_objet'] ?? 0);

        if ($objetId <= 0) {
            $this->redirectWithMessage('error', 'Annonce introuvable.');
        }

        $objetModel = new Objet();
        $objet = $objetModel->findById($objetId);

        if ($objet === null || (int)$objet['id_utilisateur'] !== (int)$_SESSION['user']['id']) {
            $this->redirectWithMessage('error', "Impossible de supprimer cette annonce.");
        }

        if (!$objetModel->deleteForOwner($objetId, (int)$_SESSION['user']['id'])) {
            $this->redirectWithMessage('error', "Impossible de supprimer cette annonce.");
        }

        $imagePath = trim((string)($objet['image'] ?? ''));
        if ($imagePath !== '') {
            if (str_starts_with($imagePath, 'public/')) {
                $imagePath = substr($imagePath, 7);
            }

            $fullPath = realpath(PROJECT_ROOT . '/public/' . ltrim($imagePath, '/'));
            $uploadRoot = realpath(PROJECT_ROOT . '/public/images/uploads');
            if ($fullPath !== false && $uploadRoot !== false && str_starts_with($fullPath, $uploadRoot)) {
                @unlink($fullPath);
            }
        }

        $this->redirectWithMessage('success', 'Annonce supprimee.');
    }

    private function redirectWithMessage(string $type, string $message): void
    {
        $_SESSION[$type] = $message;
        $this->redirect('/profil');
    }
}
