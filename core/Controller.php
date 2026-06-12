<?php
declare(strict_types=1);

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $this->syncAuthenticatedUser();

        $viewPath = PROJECT_ROOT . '/core/views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new RuntimeException('Vue introuvable: ' . $view);
        }

        $pageTitle = $data['pageTitle'] ?? 'Flamantroc';
        
        extract($data, EXTR_SKIP);

        require PROJECT_ROOT . '/core/views/partials/layout.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    private function syncAuthenticatedUser(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            return;
        }

        try {
            $userModel = new User();
            $userModel->clearExpiredBans();
            $user = $userModel->findById((int)$_SESSION['user']['id']);
        } catch (Throwable $exception) {
            return;
        }

        if ($user === null) {
            unset($_SESSION['user']);
            return;
        }

        if ($userModel->isBanActive($user)) {
            unset($_SESSION['user']);
            $_SESSION['error'] = $userModel->getBanMessage($user);
            $this->redirect('/login');
        }

        $_SESSION['user']['pseudo'] = $user['pseudo'];
        $_SESSION['user']['role'] = $user['role'];
        $_SESSION['user']['image_profil'] = $user['image_profil'] ?? null;
        $_SESSION['user']['poisson'] = $user['poisson'] ?? 0;
    }
}
