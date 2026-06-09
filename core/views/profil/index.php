<?php
$profileImage = trim((string)($user['image_profil'] ?? ''));
if ($profileImage !== '' && str_starts_with($profileImage, 'public/')) {
    $profileImage = substr($profileImage, 7);
}
$profileImageUrl = $profileImage !== '' ? asset($profileImage) : '';
$objets = $objets ?? [];
$regions = $regions ?? [];
$deleteIconUrl = asset('images/poubelle-de-recyclage.png');
?>
<article class="card profile-card">
    <h2>Mon profil</h2>

    <div class="profile-header">
        <div class="profile-avatar-large">
            <?php if ($profileImageUrl !== ''): ?>
                <img src="<?= htmlspecialchars($profileImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Photo de profil">
            <?php else: ?>
                <span><?= htmlspecialchars(strtoupper(substr((string)($user['pseudo'] ?? 'U'), 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        <div>
            <h3><?= htmlspecialchars((string)($user['pseudo'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars((string)($user['mail'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (trim((string)($user['ville'] ?? '')) !== '' || trim((string)($user['region'] ?? '')) !== ''): ?>
                <p><?= htmlspecialchars(trim((string)($user['ville'] ?? '') . ' ' . (string)($user['region'] ?? '')), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>
    </div>

    <section class="profile-section">
        <h3>Photo de profil</h3>
        <form method="post" action="<?= htmlspecialchars(url('/profil/photo'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="image_profil">Photo de profil</label>
                <input id="image_profil" name="image_profil" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required>
            </div>

            <button class="btn btn-primary" type="submit">Mettre &agrave; jour le profil</button>
        </form>
    </section>

    <section class="profile-section">
        <h3>Informations</h3>
        <form method="post" action="<?= htmlspecialchars(url('/profil/details'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    maxlength="2000"
                    placeholder="Presente-toi en quelques lignes"
                ><?= htmlspecialchars((string)($user['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="profile-fields-grid">
                <div class="form-group">
                    <label for="ville">Ville</label>
                    <input
                        id="ville"
                        name="ville"
                        type="text"
                        maxlength="100"
                        value="<?= htmlspecialchars((string)($user['ville'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Ex: Paris"
                    >
                </div>

                <div class="form-group">
                    <label for="region">Region</label>
                    <select
                        id="region"
                        name="region"
                    >
                        <option value="">Choisir une region</option>
                        <?php foreach ($regions as $region): ?>
                            <?php $selected = (string)($user['region'] ?? '') === (string)$region ? ' selected' : ''; ?>
                            <option value="<?= htmlspecialchars((string)$region, ENT_QUOTES, 'UTF-8') ?>"<?= $selected ?>>
                                <?= htmlspecialchars((string)$region, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button class="btn btn-primary" type="submit">Mettre &agrave; jour les informations</button>
        </form>
    </section>

    <section class="profile-section">
        <div class="section-heading">
            <h3>Mes annonces</h3>
            <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/offres/creer'), ENT_QUOTES, 'UTF-8') ?>">Publier</a>
        </div>

        <?php if ($objets === []): ?>
            <p>Aucune annonce publiee pour le moment.</p>
        <?php else: ?>
            <div class="profile-offers-list">
                <?php foreach ($objets as $objet): ?>
                    <?php
                    $imagePath = trim((string)($objet['image'] ?? ''));
                    if ($imagePath !== '' && str_starts_with($imagePath, 'public/')) {
                        $imagePath = substr($imagePath, 7);
                    }
                    $imageUrl = $imagePath !== '' ? asset($imagePath) : '';
                    $createdAt = (string)($objet['date_creation'] ?? '');
                    $createdTimestamp = $createdAt !== '' ? strtotime($createdAt) : false;
                    $formattedDate = $createdTimestamp !== false ? date('d/m/Y H:i', $createdTimestamp) : '';
                    ?>
                    <article class="profile-offer">
                        <?php if ($imageUrl !== ''): ?>
                            <img class="profile-offer-image" src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <?php endif; ?>

                        <div class="profile-offer-content">
                            <div class="offer-header">
                                <h4><?= htmlspecialchars((string)$objet['nom'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <span class="status-pill <?= (int)$objet['est_dispo'] === 1 ? 'status-available' : 'status-unavailable' ?>">
                                    <?= (int)$objet['est_dispo'] === 1 ? 'Disponible' : 'Indisponible' ?>
                                </span>
                            </div>

                            <p><?= htmlspecialchars((string)($objet['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

                            <div class="offer-meta">
                                <span><?= htmlspecialchars((string)($objet['categorie_nom'] ?? 'Categorie'), ENT_QUOTES, 'UTF-8') ?></span>
                                <span><?= htmlspecialchars(number_format((float)$objet['prix'], 2, ',', ' '), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ($formattedDate !== ''): ?>
                                    <span>Cr&eacute;&eacute; le <?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="profile-offer-actions">
                                <form method="post" action="<?= htmlspecialchars(url('/profil/offres/disponibilite'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                                    <input type="hidden" name="est_dispo" value="<?= (int)$objet['est_dispo'] === 1 ? 0 : 1 ?>">
                                    <button class="btn btn-secondary" type="submit">
                                        <?= (int)$objet['est_dispo'] === 1 ? 'Rendre indisponible' : 'Remettre disponible' ?>
                                    </button>
                                </form>

                                <form method="post" action="<?= htmlspecialchars(url('/profil/offres/supprimer'), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="id_objet" value="<?= (int)$objet['id_objet'] ?>">
                                    <button class="icon-action danger-action" type="submit" aria-label="Supprimer cette annonce">
                                        <img src="<?= htmlspecialchars($deleteIconUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if (isset($_SESSION['user']['id'])): ?>
        <form method="post" action="<?= htmlspecialchars(url('/auth/logout'), ENT_QUOTES, 'UTF-8') ?>">
            <button class="btn btn-secondary" type="submit">Se d&eacute;connecter</button>
        </form>
    <?php endif; ?>
</article>
