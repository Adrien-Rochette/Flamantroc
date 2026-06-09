<?php
$objets = $objets ?? [];
$categories = $categories ?? [];
$regions = $regions ?? [];
$sellerLetters = $sellerLetters ?? [];
$filters = $filters ?? [];
$favoriteOnIcon = asset('images/favoriValide.png');
$favoriteOffIcon = asset('images/favoriNot.png');
$isLoggedIn = isset($_SESSION['user']['id']);

$selectedSeller = (string)($filters['vendeur'] ?? '');
$selectedCategory = (int)($filters['categorie'] ?? 0);
$selectedMinPrice = $filters['prix_min'] ?? null;
$selectedMaxPrice = $filters['prix_max'] ?? null;
$selectedRegion = (string)($filters['region'] ?? '');
$formatPriceFilter = static function ($value): string {
    if ($value === null) {
        return '';
    }

    return rtrim(rtrim(number_format((float)$value, 2, '.', ''), '0'), '.');
};
$selectedMinPriceValue = $formatPriceFilter($selectedMinPrice);
$selectedMaxPriceValue = $formatPriceFilter($selectedMaxPrice);
$hasActiveFilters = $selectedSeller !== ''
    || $selectedCategory > 0
    || $selectedMinPrice !== null
    || $selectedMaxPrice !== null
    || $selectedRegion !== '';
$redirectParameters = [];
if ($selectedSeller !== '') {
    $redirectParameters['vendeur'] = $selectedSeller;
}
if ($selectedCategory > 0) {
    $redirectParameters['categorie'] = $selectedCategory;
}
if ($selectedMinPriceValue !== '') {
    $redirectParameters['prix_min'] = $selectedMinPriceValue;
}
if ($selectedMaxPriceValue !== '') {
    $redirectParameters['prix_max'] = $selectedMaxPriceValue;
}
if ($selectedRegion !== '') {
    $redirectParameters['region'] = $selectedRegion;
}
$currentOffresPath = '/offres' . ($redirectParameters !== [] ? '?' . http_build_query($redirectParameters) : '');
?>
<article class="card offer-filters">
    <div class="offer-filters-header">
        <div>
            <h2>Filtrer les offres</h2>
            <p>Affiner la liste par vendeur, categorie, prix et region.</p>
        </div>
        <span class="offer-filter-count">
            <?= count($objets) ?> offre<?= count($objets) > 1 ? 's' : '' ?>
        </span>
    </div>

    <form class="offer-filter-form" method="get" action="<?= htmlspecialchars(url('/offres'), ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-group">
            <label for="filter-vendeur">Lettre du vendeur</label>
            <select id="filter-vendeur" name="vendeur">
                <option value="">Toutes</option>
                <?php foreach ($sellerLetters as $letter): ?>
                    <?php $letter = (string)$letter; ?>
                    <option value="<?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedSeller === $letter ? 'selected' : '' ?>>
                        <?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="filter-categorie">Categorie</label>
            <select id="filter-categorie" name="categorie">
                <option value="">Toutes</option>
                <?php foreach ($categories as $categorie): ?>
                    <?php $categorieId = (int)$categorie['id_categorie']; ?>
                    <option value="<?= $categorieId ?>" <?= $selectedCategory === $categorieId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string)$categorie['nom'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="filter-prix-min">Prix min</label>
            <input
                id="filter-prix-min"
                name="prix_min"
                type="number"
                min="0"
                step="0.01"
                value="<?= htmlspecialchars($selectedMinPriceValue, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="0"
            >
        </div>

        <div class="form-group">
            <label for="filter-prix-max">Prix max</label>
            <input
                id="filter-prix-max"
                name="prix_max"
                type="number"
                min="0"
                step="0.01"
                value="<?= htmlspecialchars($selectedMaxPriceValue, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="100"
            >
        </div>

        <div class="form-group">
            <label for="filter-region">Region</label>
            <select id="filter-region" name="region">
                <option value="">Toutes</option>
                <?php foreach ($regions as $region): ?>
                    <?php $region = (string)$region; ?>
                    <option value="<?= htmlspecialchars($region, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedRegion === $region ? 'selected' : '' ?>>
                        <?= htmlspecialchars($region, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="offer-filter-actions">
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/offres'), ENT_QUOTES, 'UTF-8') ?>">Reinitialiser</a>
        </div>
    </form>
</article>

<?php if ($objets === []): ?>
    <article class="card">
        <h2><?= $hasActiveFilters ? 'Aucune offre trouvee' : 'Aucune annonce' ?></h2>
        <p>
            <?= $hasActiveFilters
                ? 'Aucune annonce ne correspond aux filtres selectionnes.'
                : 'Les annonces publiees apparaitront ici.' ?>
        </p>
        <?php if ($hasActiveFilters): ?>
            <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/offres'), ENT_QUOTES, 'UTF-8') ?>">Retirer les filtres</a>
        <?php else: ?>
            <a class="btn btn-primary" href="<?= htmlspecialchars(url('/offres/creer'), ENT_QUOTES, 'UTF-8') ?>">Publier une annonce</a>
        <?php endif; ?>
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

            $sellerImagePath = trim((string)($objet['utilisateur_image_profil'] ?? ''));
            if ($sellerImagePath !== '' && str_starts_with($sellerImagePath, 'public/')) {
                $sellerImagePath = substr($sellerImagePath, 7);
            }
            $sellerImageUrl = $sellerImagePath !== '' ? asset($sellerImagePath) : '';
            $sellerPseudo = (string)($objet['utilisateur_pseudo'] ?? 'Utilisateur');
            $sellerInitial = strtoupper(substr($sellerPseudo, 0, 1));
            $sellerRegion = trim((string)($objet['utilisateur_region'] ?? ''));
            ?>
            <article class="card offer-card">
                <?php $isFavorite = (int)($objet['est_favori'] ?? 0) === 1; ?>
                <?php if ($isLoggedIn): ?>
                    <form method="post" action="<?= htmlspecialchars(url('/favoris/toggle'), ENT_QUOTES, 'UTF-8') ?>" class="favorite-form">
                        <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentOffresPath, ENT_QUOTES, 'UTF-8') ?>">
                        <button class="favorite-button" type="submit" aria-label="<?= $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
                            <img src="<?= htmlspecialchars($isFavorite ? $favoriteOnIcon : $favoriteOffIcon, ENT_QUOTES, 'UTF-8') ?>" alt="">
                        </button>
                    </form>
                <?php else: ?>
                    <a class="favorite-button favorite-link" href="<?= htmlspecialchars(url('/login'), ENT_QUOTES, 'UTF-8') ?>" aria-label="Se connecter pour ajouter aux favoris">
                        <img src="<?= htmlspecialchars($favoriteOffIcon, ENT_QUOTES, 'UTF-8') ?>" alt="">
                    </a>
                <?php endif; ?>

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
                        <?php if ($sellerRegion !== ''): ?>
                            <span><?= htmlspecialchars($sellerRegion, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>

                    <p class="offer-owner">
                        <span class="seller-avatar" aria-hidden="true">
                            <?php if ($sellerImageUrl !== ''): ?>
                                <img src="<?= htmlspecialchars($sellerImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
                            <?php else: ?>
                                <?= htmlspecialchars($sellerInitial, ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </span>
                        <span>Par <?= htmlspecialchars($sellerPseudo, ENT_QUOTES, 'UTF-8') ?></span>
                    </p>

                    <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/offres/detail?id=' . (int)$objet['id_objet']), ENT_QUOTES, 'UTF-8') ?>">Voir l'offre</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
