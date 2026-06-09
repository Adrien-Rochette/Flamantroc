<?php
declare(strict_types=1);

session_start();

define('PROJECT_ROOT', __DIR__);

$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($baseUrl === '/' || $baseUrl === '.') {
    $baseUrl = '';
}
define('BASE_URL', $baseUrl);

require_once PROJECT_ROOT . '/core/helpers.php';

spl_autoload_register(static function (string $className): void {
    $paths = [
        PROJECT_ROOT . '/core/' . $className . '.php',
        PROJECT_ROOT . '/core/controllers/' . $className . '.php',
        PROJECT_ROOT . '/core/models/' . $className . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

Env::load(PROJECT_ROOT . '/config/.env');

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/conditions-utilisation', [LegalController::class, 'conditions']);
$router->get('/droits', [LegalController::class, 'droits']);

$router->get('/login', [AuthController::class, 'login']);
$router->post('/auth/login', [AuthController::class, 'handleLogin']);
$router->post('/auth/logout', [AuthController::class, 'logout']);
$router->get('/register', [AuthController::class, 'register']);
$router->post('/auth/register', [AuthController::class, 'handleRegister']);

$router->get('/offres', [OffreController::class, 'liste']);
$router->get('/offres/creer', [OffreController::class, 'creer']);
$router->post('/offres/creer', [OffreController::class, 'store']);
$router->get('/offres/detail', [OffreController::class, 'detail']);
$router->post('/offres/contact', [OffreController::class, 'contact']);
$router->post('/transactions/acheter', [TransactionController::class, 'acheter']);
$router->post('/transactions/acheter-conversation', [TransactionController::class, 'acheterConversation']);
$router->post('/transactions/annuler', [TransactionController::class, 'annulerAchat']);
$router->post('/transactions/offre-prix', [TransactionController::class, 'proposerPrix']);
$router->post('/transactions/accepter-offre', [TransactionController::class, 'accepterOffre']);
$router->post('/transactions/refuser-offre', [TransactionController::class, 'refuserOffre']);

$router->get('/messages', [MessageController::class, 'index']);
$router->get('/messages/conversation', [MessageController::class, 'conversation']);
$router->post('/messages/envoyer', [MessageController::class, 'send']);

$router->get('/favoris', [FavoriController::class, 'index']);
$router->post('/favoris/toggle', [FavoriController::class, 'toggle']);

$router->get('/propositions', [FavoriController::class, 'index']);
$router->get('/propositions/recues', [PropositionController::class, 'recues']);

$router->get('/categories', [CategorieController::class, 'liste']);
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->post('/admin/users/ban', [AdminController::class, 'banUser']);
$router->post('/admin/users/unban', [AdminController::class, 'unbanUser']);
$router->get('/profil', [ProfilController::class, 'index']);
$router->post('/profil/photo', [ProfilController::class, 'updatePhoto']);
$router->post('/profil/details', [ProfilController::class, 'updateDetails']);
$router->post('/profil/offres/disponibilite', [ProfilController::class, 'updateOfferAvailability']);
$router->post('/profil/offres/supprimer', [ProfilController::class, 'deleteOffer']);
$router->post('/assistant/help', [HelpController::class, 'reply']);

$router->dispatch();
