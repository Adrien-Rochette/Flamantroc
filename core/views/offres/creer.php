<?php
$categories = $categories ?? [];
$old = $old ?? [];
$selectedCategorie = (int)($old['id_categorie'] ?? 0);
?>
<article class="card form-card">
    <h2>Nouvelle annonce</h2>

    <form method="post" action="<?= htmlspecialchars(url('/offres/creer'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nom">Nom</label>
            <input
                id="nom"
                name="nom"
                type="text"
                maxlength="100"
                value="<?= htmlspecialchars((string)($old['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Ex: Guitare acoustique"
                required
            >
        </div>

        <div class="form-group">
            <label for="id_categorie">Categorie</label>
            <select id="id_categorie" name="id_categorie" required>
                <option value="">Choisir une categorie</option>
                <?php foreach ($categories as $categorie): ?>
                    <?php $categorieId = (int)$categorie['id_categorie']; ?>
                    <option value="<?= $categorieId ?>" <?= $selectedCategorie === $categorieId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string)$categorie['nom'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea
                id="description"
                name="description"
                rows="5"
                placeholder="Details utiles pour les autres utilisateurs"
            ><?= htmlspecialchars((string)($old['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Image</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required>
        </div>

        <div class="form-group">
            <label for="prix">Prix</label>
            <input
                id="prix"
                name="prix"
                type="number"
                min="0"
                step="0.01"
                value="<?= htmlspecialchars((string)($old['prix'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Ex: 25"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary" <?= $categories === [] ? 'disabled' : '' ?>>Publier</button>
    </form>

    <?php if ($categories === []): ?>
        <p class="form-help">Ajoute au moins une categorie dans la base avant de publier une annonce.</p>
    <?php endif; ?>
</article>
