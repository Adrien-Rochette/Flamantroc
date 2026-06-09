<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Flamantroc', ENT_QUOTES, 'UTF-8') ?> | Flamantroc</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/main.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/layout.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/navbar.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/cards.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/forms.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('styles/responsive.css'), ENT_QUOTES, 'UTF-8') ?>">
    <script defer src="<?= htmlspecialchars(asset('scripts/app.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</head>
<body>
<?php require PROJECT_ROOT . '/core/views/partials/header.php'; ?>

<main class="page-main">
    <div class="container">
        <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Flamantroc', ENT_QUOTES, 'UTF-8') ?></h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="page-content">
            <?php require $contentView; ?>
        </section>
    </div>
</main>

<aside class="help-widget" data-help-widget data-help-endpoint="<?= htmlspecialchars(url('/assistant/help'), ENT_QUOTES, 'UTF-8') ?>">
    <button
        type="button"
        class="help-widget-toggle"
        data-help-toggle
        aria-expanded="false"
        aria-controls="help-widget-panel"
    >
        Aide
    </button>

    <section id="help-widget-panel" class="help-widget-panel" data-help-panel hidden>
        <header class="help-widget-header">
            <h2>Assistant</h2>
            <button type="button" class="help-widget-close" data-help-close aria-label="Fermer l'assistant">x</button>
        </header>

        <div class="help-widget-messages" data-help-messages>
            <p class="help-message help-message-assistant">
                Bonjour, je peux t'aider sur cette page.
            </p>
        </div>

        <form class="help-widget-form" data-help-form>
            <label class="sr-only" for="help-widget-input">Question</label>
            <input
                id="help-widget-input"
                name="question"
                type="text"
                maxlength="800"
                placeholder="Pose ta question..."
                required
            >
            <button type="submit" class="btn btn-primary" data-help-submit>Envoyer</button>
        </form>
    </section>
</aside>

<?php require PROJECT_ROOT . '/core/views/partials/footer.php'; ?>
</body>
</html>
