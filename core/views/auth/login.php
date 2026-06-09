<div class="cards-grid auth-grid">
    <article class="card form-card">
        <h2>Connexion</h2>
        <form method="post" action="<?= htmlspecialchars(url('/auth/login'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input id="mot_de_passe" name="mot_de_passe" type="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
    </article>

    <article class="card auth-card">
        <h2>Pas encore de compte ?</h2>
        <p>Inscris-toi avec un pseudo, un email et un mot de passe.</p>
        <a class="btn btn-primary" href="<?= htmlspecialchars(url('/register'), ENT_QUOTES, 'UTF-8') ?>">S'inscrire</a>
    </article>
</div>
