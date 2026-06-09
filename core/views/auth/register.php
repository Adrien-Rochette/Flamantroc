<?php
$oldPseudo = $old['pseudo'] ?? '';
$oldEmail = $old['email'] ?? '';
?>
<div class="cards-grid auth-grid">
    <article class="card form-card">
        <h2>Inscription</h2>
        <form method="post" action="<?= htmlspecialchars(url('/auth/register'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="pseudo">Pseudo</label>
                <input id="pseudo" name="pseudo" type="text" value="<?= htmlspecialchars((string)$oldPseudo, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= htmlspecialchars((string)$oldEmail, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input id="mot_de_passe" name="mot_de_passe" type="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Creer un compte</button>
        </form>
    </article>

    <article class="card auth-card">
        <h2>Deja inscrit ?</h2>
        <p>Retourne a la page de connexion pour utiliser tes identifiants.</p>
        <a class="btn btn-secondary" href="<?= htmlspecialchars(url('/login'), ENT_QUOTES, 'UTF-8') ?>">Se connecter</a>
    </article>
</div>
