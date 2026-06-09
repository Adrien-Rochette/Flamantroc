<?php
$categories = $categories ?? [];
?>
<article class="card">
    <h2>Categories</h2>

    <?php if ($categories === []): ?>
        <p>Aucune categorie disponible.</p>
    <?php else: ?>
        <div class="category-list">
            <?php foreach ($categories as $categorie): ?>
                <span class="category-pill"><?= htmlspecialchars((string)$categorie['nom'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>
