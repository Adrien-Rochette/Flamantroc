<?php
declare(strict_types=1);

if (!isset($_SESSION['user']['id'])) {
    return;
}
?>
<aside class="app-sidebar" aria-label="Raccourcis">
    <a
        class="sidebar-icon-link <?= isActiveRoute('/messages') || isActiveRoute('/messages/conversation') ? 'active' : '' ?>"
        href="<?= htmlspecialchars(url('/messages'), ENT_QUOTES, 'UTF-8') ?>"
        aria-label="Ouvrir les messages"
        title="Messages"
    >
        <img src="<?= htmlspecialchars(asset('images/mail.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
    </a>
</aside>
