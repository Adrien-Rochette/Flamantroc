<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    public function login(): void
    {
        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $this->view('auth/login', [
            'pageTitle' => 'Connexion',
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function register(): void
    {
        $this->view('auth/register', [
            'pageTitle' => 'Inscription',
        ]);
    }

    public function handleLogin(): void
    {
        $mail = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['mot_de_passe'] ?? '');

        if ($mail === '' || $password === '') {
            $this->view('auth/login', [
                'pageTitle' => 'Connexion',
                'error' => 'Email et mot de passe requis.',
            ]);
            return;
        }

        $userModel = new User();
        $userModel->clearExpiredBans();
        $user = $userModel->verifyCredentials($mail, $password);

        if ($user === null) {
            $this->view('auth/login', [
                'pageTitle' => 'Connexion',
                'error' => 'Identifiants invalides.',
            ]);
            return;
        }

        if ($userModel->isBanActive($user)) {
            $this->view('auth/login', [
                'pageTitle' => 'Connexion',
                'error' => $userModel->getBanMessage($user),
            ]);
            return;
        }

        $_SESSION['user'] = [
            'id' => $user['id_utilisateur'],
            'pseudo' => $user['pseudo'],
            'role' => $user['role'],
            'image_profil' => $user['image_profil'] ?? null,
            'poisson' => $user['poisson'] ?? 0,
        ];

        $this->redirect('/');
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        $this->redirect('/login');
    }

    public function handleRegister(): void
    {
        $pseudo = trim((string)($_POST['pseudo'] ?? ''));
        $mail = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['mot_de_passe'] ?? '');

        if ($pseudo === '' || $mail === '' || $password === '') {
            $this->view('auth/register', [
                'pageTitle' => 'Inscription',
                'error' => 'Tous les champs sont obligatoires.',
                'old' => [
                    'pseudo' => $pseudo,
                    'email' => $mail,
                ],
            ]);
            return;
        }

        if (filter_var($mail, FILTER_VALIDATE_EMAIL) === false) {
            $this->view('auth/register', [
                'pageTitle' => 'Inscription',
                'error' => 'Email invalide.',
                'old' => [
                    'pseudo' => $pseudo,
                    'email' => $mail,
                ],
            ]);
            return;
        }

        try {
            $userModel = new User();
            $userModel->create([
                'pseudo' => $pseudo,
                'mail' => $mail,
                'mot_de_passe' => $password,
            ]);
        } catch (Throwable $exception) {
            $this->view('auth/register', [
                'pageTitle' => 'Inscription',
                'error' => "Impossible d'enregistrer l'utilisateur.",
                'old' => [
                    'pseudo' => $pseudo,
                    'email' => $mail,
                ],
            ]);
            return;
        }

        $_SESSION['success'] = 'Compte cree. Tu peux maintenant te connecter.';

        $this->redirect('/login');
    }
}
