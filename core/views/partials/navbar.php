<?php
declare(strict_types=1);

$displayName = $_SESSION['user']['pseudo'] ?? 'Invite';
$isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'ADMIN';
$isGuest = !isset($_SESSION['user']['id']);
$publishUrl = $isGuest ? url('/login') : url('/offres/creer');
$favorisUrl = $isGuest ? url('/login') : url('/favoris');
$messagesUrl = $isGuest ? url('/login') : url('/messages');
$profilUrl = $isGuest ? url('/login') : url('/profil');
$userBalance = (float)($_SESSION['user']['poisson'] ?? 0);
$userBalanceLabel = number_format($userBalance, 2, ',', ' ');
$userImagePath = trim((string)($_SESSION['user']['image_profil'] ?? ''));
if ($userImagePath !== '' && str_starts_with($userImagePath, 'public/')) {
    $userImagePath = substr($userImagePath, 7);
}
$userImageUrl = $userImagePath !== '' ? asset($userImagePath) : '';
$userAvatarStyle = 'width:36px;height:36px;min-width:36px;max-width:36px;min-height:36px;max-height:36px;flex:0 0 36px;';
if ($userImageUrl !== '') {
    $safeBackgroundUrl = str_replace("'", '%27', $userImageUrl);
    $userAvatarStyle .= "background-image:url('" . $safeBackgroundUrl . "');background-size:cover;background-position:center;background-repeat:no-repeat;";
}
?>
<nav class="navbar" data-navbar>
    <div class="navbar-left">
        <a class="logo" href="<?= htmlspecialchars(url('/'), ENT_QUOTES, 'UTF-8') ?>">
            <img
                class="logo-image"
                src="<?= htmlspecialchars(asset('images/logoStart.png'), ENT_QUOTES, 'UTF-8') ?>"
                alt="Flamantroc"
            >
            <span class="logo-text">Flamantroc</span>
        </a>
    </div>

    <button
        class="navbar-burger"
        type="button"
        data-navbar-toggle
        aria-expanded="false"
        aria-controls="navbar-menu"
        aria-label="Ouvrir le menu"
    >
        <img
            class="burger-image"
            src="<?= htmlspecialchars(asset('images/menu-burger.png'), ENT_QUOTES, 'UTF-8') ?>"
            alt=""
        >
        <span class="sr-only">Menu</span>
    </button>

    <div class="navbar-menu" id="navbar-menu" data-navbar-menu>
        <ul class="navbar-center">
            <li><a class="<?= isActiveRoute('/') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/'), ENT_QUOTES, 'UTF-8') ?>">Accueil</a></li>
            <li><a class="<?= isActiveRoute('/offres') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/offres'), ENT_QUOTES, 'UTF-8') ?>">Offres</a></li>
            <li><a class="<?= isActiveRoute('/offres/creer') ? 'active' : '' ?>" href="<?= htmlspecialchars($publishUrl, ENT_QUOTES, 'UTF-8') ?>">Publier</a></li>
            <li><a class="<?= isActiveRoute('/favoris') || isActiveRoute('/propositions') ? 'active' : '' ?>" href="<?= htmlspecialchars($favorisUrl, ENT_QUOTES, 'UTF-8') ?>">Favoris</a></li>

            <?php if ($isAdmin): ?>
                <li><a class="<?= isActiveRoute('/admin') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/admin'), ENT_QUOTES, 'UTF-8') ?>">Administration</a></li>
                <li><a class="<?= isActiveRoute('/profil') ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/profil'), ENT_QUOTES, 'UTF-8') ?>">Profil</a></li>
            <?php else: ?>
                <li><a class="<?= isActiveRoute('/profil') ? 'active' : '' ?>" href="<?= htmlspecialchars($profilUrl, ENT_QUOTES, 'UTF-8') ?>">Profil</a></li>
            <?php endif; ?>
        </ul>

        <div class="navbar-right">
            <?php if (!$isGuest): ?>
                <a
                    class="nav-icon-link <?= isActiveRoute('/messages') || isActiveRoute('/messages/conversation') ? 'active' : '' ?>"
                    href="<?= htmlspecialchars($messagesUrl, ENT_QUOTES, 'UTF-8') ?>"
                    aria-label="Ouvrir les messages"
                    title="Messages"
                >
                    <img src="<?= htmlspecialchars(asset('images/mail.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
                </a>
                <div class="wallet-alert-wrap">
                    <button
                        class="wallet-chip"
                        type="button"
                        aria-label="Solde : <?= htmlspecialchars($userBalanceLabel, ENT_QUOTES, 'UTF-8') ?>"
                        aria-describedby="fish-alert-label"
                        aria-expanded="false"
                        data-fish-alert-toggle
                    >
                        <img class="token-icon" src="<?= htmlspecialchars(asset('images/token.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <span><?= htmlspecialchars($userBalanceLabel, ENT_QUOTES, 'UTF-8') ?></span>
                    </button>
                    <span class="fish-alert-label" id="fish-alert-label" role="alert" aria-hidden="true" data-fish-alert-label>
                        achat de poisson indisponible pour le moment, suivez les actualités à l'accueil pour plus d'info.
                    </span>
                </div>
            <?php endif; ?>
            <a class="btn btn-primary" href="<?= htmlspecialchars($publishUrl, ENT_QUOTES, 'UTF-8') ?>">Publier une offre</a>
            <div class="user-chip" aria-label="Zone utilisateur">
                <span
                    class="user-avatar <?= $userImageUrl !== '' ? 'user-avatar-photo' : '' ?>"
                    style="<?= htmlspecialchars($userAvatarStyle, ENT_QUOTES, 'UTF-8') ?>"
                >
                    <?php if ($userImageUrl === ''): ?>
                        <?= strtoupper(substr($displayName, 0, 1)) ?>
                    <?php endif; ?>
                </span>
                <span class="user-name"><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    </div>
</nav>
