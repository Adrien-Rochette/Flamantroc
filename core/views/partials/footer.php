<?php
declare(strict_types=1);
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <strong>Flamantroc</strong>
                <p>Plateforme de troc d'objets et services pensée pour des échanges simples, utiles et respectueux.</p>
            </div>

            <nav class="footer-section" aria-label="Navigation secondaire">
                <h2>Explorer</h2>
                <a href="<?= htmlspecialchars(url('/'), ENT_QUOTES, 'UTF-8') ?>">Accueil</a>
                <a href="<?= htmlspecialchars(url('/offres'), ENT_QUOTES, 'UTF-8') ?>">Offres</a>
                <a href="<?= htmlspecialchars(url('/categories'), ENT_QUOTES, 'UTF-8') ?>">Catégories</a>
            </nav>

            <nav class="footer-section" aria-label="Informations légales">
                <h2>Informations</h2>
                <a href="<?= htmlspecialchars(url('/conditions-utilisation'), ENT_QUOTES, 'UTF-8') ?>">Conditions d'utilisation</a>
                <a href="<?= htmlspecialchars(url('/droits'), ENT_QUOTES, 'UTF-8') ?>">Droits</a>
            </nav>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Flamantroc. Tous droits réservés.</p>
            <p>Les échanges restent sous la responsabilité des utilisateurs.</p>
        </div>
    </div>
</footer>
