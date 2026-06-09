<?php
declare(strict_types=1);

final class FavoriController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $favoriModel = new Favori();

        $this->view('favoris/index', [
            'pageTitle' => 'Favoris',
            'objets' => $favoriModel->findByUtilisateur((int)$_SESSION['user']['id']),
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function toggle(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $objetId = (int)($_POST['id_objet'] ?? 0);
        $redirectTo = $this->sanitizeRedirect((string)($_POST['redirect_to'] ?? '/offres'));

        if ($objetId <= 0) {
            $_SESSION['error'] = 'Offre introuvable.';
            $this->redirect($redirectTo);
        }

        $objetModel = new Objet();
        $objet = $objetModel->findById($objetId);

        if ($objet === null || (int)$objet['est_dispo'] !== 1) {
            $_SESSION['error'] = 'Cette offre n est plus disponible.';
            $this->redirect($redirectTo);
        }

        $favoriModel = new Favori();
        $isFavorite = $favoriModel->toggle((int)$_SESSION['user']['id'], $objetId);

        $_SESSION['success'] = $isFavorite ? 'Offre ajoutee aux favoris.' : 'Offre retiree des favoris.';
        $this->redirect($redirectTo);
    }

    private function sanitizeRedirect(string $redirectTo): string
    {
        $redirectTo = trim($redirectTo);

        if ($redirectTo === '' || str_starts_with($redirectTo, 'http://') || str_starts_with($redirectTo, 'https://')) {
            return '/offres';
        }

        return '/' . ltrim($redirectTo, '/');
    }
}
