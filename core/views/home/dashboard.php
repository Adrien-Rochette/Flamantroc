<div class="home-dashboard">
    <article class="card">
        <h2>Bienvenue sur flamantroc</h2>
        <p>Des objets qui trainent à la maison ? Marre de toujours devoir jeter bêtement ou batailler
            pour trouver un acheteur qui négociera vos prix ? Découvre Flamantroc ! Plus besoin de
            votre tondeuse ? FLAMANTROC ! Vous cherchez quelqu'un pour garder votre chat ?
            FLAMANTROC !</p>
    </article>

    <article class="card">
        <h2>Notre Logique</h2>
        <div class="flexBoxAccueil">
            <div class="accueil-logic-box">
                <img src="<?= htmlspecialchars(asset('images/iconfeuille.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
                <div>
                    <h3>Donner une seconde vie</h3>
                    <p>Valorise les objets et services utiles au lieu de les laisser dormir ou partir à la poubelle.</p>
                </div>
            </div>

            <div class="accueil-logic-box">
                <img src="<?= htmlspecialchars(asset('images/iconidee.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
                <div>
                    <h3>Imaginer un échange juste</h3>
                    <p>Propose des trocs simples, adaptés aux besoins de chacun et sans négociation interminable.</p>
                </div>
            </div>

            <div class="accueil-logic-box">
                <img src="<?= htmlspecialchars(asset('images/iconmain.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
                <div>
                    <h3>Créer du lien local</h3>
                    <p>Favorise les rencontres de confiance autour d'objets, de coups de main et de services partagés.</p>
                </div>
            </div>
        </div>
    </article>
    <article>
        <h2>Un projet avant tout humain</h2>
        <div class="slideshow-container">
            <div class="slide"><img src="<?= htmlspecialchars(asset('images/carousel/img1.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="slide 1"></div>
            <div class="slide"><img src="<?= htmlspecialchars(asset('images/carousel/img2.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="slide 2"></div>
            <div class="slide"><img src="<?= htmlspecialchars(asset('images/carousel/img3.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="slide 3"></div>
            <div class="slide"><img src="<?= htmlspecialchars(asset('images/carousel/img4.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="slide 4"></div>
            <div class="slide"><img src="<?= htmlspecialchars(asset('images/carousel/img5.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="slide 5"></div>
        </div>

        <div class="controls">
            <a class="prev" onclick="changeSlide(-1)">&#10094;</a>

            <div class="slide-progress">
                <span class="dot" onclick="showSlide(0)"></span>
                <span class="dot" onclick="showSlide(1)"></span>
                <span class="dot" onclick="showSlide(2)"></span>
                <span class="dot" onclick="showSlide(3)"></span>
                <span class="dot" onclick="showSlide(4)"></span>
            </div>
            <a class="prev" onclick="changeSlide(1)">&#10095;</a>
        </div>

        <script>
            let slideIndex = 0;
            showSlide(slideIndex);

            function changeSlide(n) {
                showSlide(slideIndex + n);
            }

            function showSlide(n) {
                const slides = document.querySelectorAll(".slide");
                const dots = document.querySelectorAll(".dot");

                if (n >= slides.length) { n = 0; }
                if (n < 0) { n = slides.length - 1; }

                slides.forEach(s => s.style.display = "none");
                dots.forEach(d => d.classList.remove("active"));

                // On affiche la slide actuelle
                slides[n].style.display = "block";
                dots[n].classList.add("active");

                slideIndex = n;
            }

            let autoPlay = setInterval(() => {
                changeSlide(1);
            }, 3000);

        </script>
    </article>
    <article>
        <h2>
            L'Économie : Le Système "Poisson"
        </h2>
        <div class="accueil-logic-box-poisson">
            <img class="icon-action-2" src="<?= htmlspecialchars(asset('images/token.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
            <div>
                <h3>Le Poisson : Notre Monnaie Interne</h3>
                <p>Utilisez vos poissons pour acquérir des biens sans débourser un euro.</p>
            </div>
        </div>
    </article>
    <article class="card">
        <h2>Notre Business Plan</h2>
        <ul class="business-list">
            <li><strong>Système Freemium :</strong> Le troc de base est gratuit.</li>
            <li><strong>Achat de Poissons :</strong> Boostez votre pouvoir d'échange (1€ = 10 poissons).</li>
            <li><strong>Offres Boostées :</strong> Mettez vos objets en avant contre quelques poissons.</li>
            <li><strong>Sponsoring :</strong> Partenariats éthiques intégrés discrètement.</li>
        </ul>
    </article>
    <article class="card">
        <h2>Guide des Onglets</h2>
        <div>
            <div >
                <h3>🏠 Accueil</h3>
                <p>Comprendre l'esprit <strong>Flamantroc</strong>, une plateforme écologique et coopérative.</p>
            </div>
            <div >
                <h3>🔍 Les Offres</h3>
                <p>Parcourir les trésors et services disponibles filtrés par catégorie ou région.</p>
            </div>
            <div >
                <h3>📤 Publier</h3>
                <p>Proposer vos objets ou vos talents à la communauté.</p>
            </div>
            <div >
                <h3>❤️ Favoris</h3>
                <p>Gardez une trace des objets qui répondent à vos besoins pour une consultation ultérieure.</p>
            </div>
            <div >
                <h3>💬 Messagerie</h3>
                <p>Négocier et organiser la rencontre via le chat sécurisé.</p>
            </div>
        </div>
    </article>
</div>
