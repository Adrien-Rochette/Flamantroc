<?php
$objets = $objets ?? [];
$favoriteOnIcon = asset('images/favoriValide.png');
?>
<?php if ($objets === []): ?>
    <article class="card">
        <h2>Aucun favori</h2>
        <p>Ajoute des offres en favori depuis la liste des offres.</p>
        <a class="btn btn-primary" href="<?= htmlspecialchars(url('/offres'), ENT_QUOTES, 'UTF-8') ?>">Voir les offres</a>
    </article>
<?php else: ?>
    <div class="offers-grid">
        <?php foreach ($objets as $objet): ?>
            <?php
            $imagePath = trim((string)($objet['image'] ?? ''));
            if ($imagePath !== '' && str_starts_with($imagePath, 'public/')) {
                $imagePath = substr($imagePath, 7);
            }
            $imageUrl = $imagePath !== '' ? asset($imagePath) : '';
            ?>
            <article class="card offer-card">
                <form method="post" action="<?= htmlspecialchars(url('/favoris/toggle'), ENT_QUOTES, 'UTF-8') ?>" class="favorite-form">
                    <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                    <input type="hidden" name="redirect_to" value="/favoris">
                    <button class="favorite-button" type="submit" aria-label="Retirer des favoris">
                        <img src="<?= htmlspecialchars($favoriteOnIcon, ENT_QUOTES, 'UTF-8') ?>" alt="">
                    </button>
                </form>

                <?php if ($imageUrl !== ''): ?>
                    <img class="offer-image" src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
                <?php endif; ?>

                <div class="offer-content">
                    <div class="offer-header">
                        <h2><?= htmlspecialchars((string)$objet['nom'], ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>

                    <p><?= htmlspecialchars((string)($objet['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

                    <div class="offer-meta">
                        <span><?= htmlspecialchars((string)($objet['categorie_nom'] ?? 'Categorie'), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><?= htmlspecialchars(number_format((float)$objet['prix'], 2, ',', ' '), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>

                    <p class="offer-owner">Par <?= htmlspecialchars((string)($objet['utilisateur_pseudo'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?></p>

                    <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/offres/detail?id=' . (int)$objet['id_objet']), ENT_QUOTES, 'UTF-8') ?>">Voir l'offre</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
