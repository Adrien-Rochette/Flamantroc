<?php
$objet = $objet ?? [];
$imagePath = trim((string)($objet['image'] ?? ''));
if ($imagePath !== '' && str_starts_with($imagePath, 'public/')) {
    $imagePath = substr($imagePath, 7);
}
$imageUrl = $imagePath !== '' ? asset($imagePath) : '';
$isLoggedIn = isset($_SESSION['user']['id']);
$isOwner = $isLoggedIn && (int)$_SESSION['user']['id'] === (int)($objet['id_utilisateur'] ?? 0);
$isFavorite = (int)($objet['est_favori'] ?? 0) === 1;
$showPurchaseConfirmation = (bool)($showPurchaseConfirmation ?? false);
$favoriteOnIcon = asset('images/favoriValide.png');
$favoriteOffIcon = asset('images/favoriNot.png');
$sellerImagePath = trim((string)($objet['utilisateur_image_profil'] ?? ''));
if ($sellerImagePath !== '' && str_starts_with($sellerImagePath, 'public/')) {
    $sellerImagePath = substr($sellerImagePath, 7);
}
$sellerImageUrl = $sellerImagePath !== '' ? asset($sellerImagePath) : '';
$sellerPseudo = (string)($objet['utilisateur_pseudo'] ?? 'Utilisateur');
$sellerInitial = strtoupper(substr($sellerPseudo, 0, 1));
?>
<article class="offer-detail">
    <div class="offer-detail-media">
        <?php if ($imageUrl !== ''): ?>
            <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
        <?php endif; ?>
    </div>

    <div class="offer-detail-panel">
        <div class="offer-detail-heading">
            <h2><?= htmlspecialchars((string)($objet['nom'] ?? 'Offre'), ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="offer-detail-actions">
                <strong><?= htmlspecialchars(number_format((float)($objet['prix'] ?? 0), 2, ',', ' '), ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if ($isLoggedIn): ?>
                    <form method="post" action="<?= htmlspecialchars(url('/favoris/toggle'), ENT_QUOTES, 'UTF-8') ?>" class="favorite-inline-form">
                        <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                        <input type="hidden" name="redirect_to" value="/offres/detail?id=<?= (int)$objet['id_objet'] ?>">
                        <button class="favorite-button favorite-button-inline" type="submit" aria-label="<?= $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
                            <img src="<?= htmlspecialchars($isFavorite ? $favoriteOnIcon : $favoriteOffIcon, ENT_QUOTES, 'UTF-8') ?>" alt="">
                        </button>
                    </form>
                <?php else: ?>
                    <a class="favorite-button favorite-button-inline" href="<?= htmlspecialchars(url('/login'), ENT_QUOTES, 'UTF-8') ?>" aria-label="Se connecter pour ajouter aux favoris">
                        <img src="<?= htmlspecialchars($favoriteOffIcon, ENT_QUOTES, 'UTF-8') ?>" alt="">
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="offer-meta">
            <span><?= htmlspecialchars((string)($objet['categorie_nom'] ?? 'Categorie'), ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <p class="offer-owner offer-owner-detail">
            <span class="seller-avatar" aria-hidden="true">
                <?php if ($sellerImageUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($sellerImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
                <?php else: ?>
                    <?= htmlspecialchars($sellerInitial, ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </span>
            <span>Par <?= htmlspecialchars($sellerPseudo, ENT_QUOTES, 'UTF-8') ?></span>
        </p>

        <p><?= nl2br(htmlspecialchars((string)($objet['description'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>

        <?php if (!$isLoggedIn): ?>
            <a class="btn btn-primary" href="<?= htmlspecialchars(url('/login'), ENT_QUOTES, 'UTF-8') ?>">Se connecter pour contacter</a>
        <?php elseif ($isOwner): ?>
            <p class="form-help">Tu ne peux pas contacter ta propre annonce.</p>
        <?php else: ?>
            <div class="transaction-actions">
                <form method="post" action="<?= htmlspecialchars(url('/transactions/acheter'), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                    <button class="btn btn-primary" type="submit">Acheter</button>
                </form>

                <button class="btn btn-secondary" type="button" data-price-offer-toggle>Faire une offre de prix</button>
            </div>

            <form method="post" action="<?= htmlspecialchars(url('/transactions/offre-prix'), ENT_QUOTES, 'UTF-8') ?>" class="price-offer-form" data-price-offer-form hidden>
                <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                <div class="form-group">
                    <label for="prix-propose">Prix propose en poissons</label>
                    <input
                        id="prix-propose"
                        name="prix_propose"
                        type="number"
                        min="0.01"
                        step="0.01"
                        placeholder="<?= htmlspecialchars(number_format((float)($objet['prix'] ?? 0), 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>
                <button class="btn btn-primary" type="submit">Envoyer l'offre</button>
            </form>

            <form method="post" action="<?= htmlspecialchars(url('/offres/contact'), ENT_QUOTES, 'UTF-8') ?>" class="contact-form">
                <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                <div class="form-group">
                    <label for="contenu">Message</label>
                    <textarea id="contenu" name="contenu" rows="5" maxlength="2000" placeholder="Bonjour, ton offre m'interesse." required></textarea>
                </div>
                <button class="btn btn-primary" type="submit">Envoyer un message</button>
            </form>
        <?php endif; ?>
    </div>
</article>

<?php if ($showPurchaseConfirmation && !$isOwner && $isLoggedIn): ?>
    <div class="purchase-modal-backdrop" role="presentation">
        <section class="purchase-modal" role="dialog" aria-modal="true" aria-labelledby="purchase-modal-title">
            <h2 id="purchase-modal-title">Valider l'achat ?</h2>
            <p>Confirme l'achat de cette offre pour <?= htmlspecialchars(number_format((float)($objet['prix'] ?? 0), 2, ',', ' '), ENT_QUOTES, 'UTF-8') ?> poissons.</p>
            <div class="purchase-modal-actions">
                <form method="post" action="<?= htmlspecialchars(url('/transactions/acheter'), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                    <input type="hidden" name="confirm_achat" value="1">
                    <button class="btn btn-primary" type="submit">Oui</button>
                </form>
                <form method="post" action="<?= htmlspecialchars(url('/transactions/annuler'), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                    <button class="btn btn-secondary" type="submit">Non</button>
                </form>
            </div>
        </section>
    </div>
<?php endif; ?>
