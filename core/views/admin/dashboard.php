<?php
$users = $users ?? [];
$usersByRegionJson = $usersByRegionJson ?? '{}';
$registrationsByDayJson = $registrationsByDayJson ?? '[]';
?>
<article class="card admin-map-card">
    <div class="section-heading">
        <h2>Densite par region</h2>
        <span class="admin-count">Utilisateurs inscrits</span>
    </div>

    <div class="france-map-wrap">
        <svg class="france-map" viewBox="0 0 760 560" role="img" aria-label="Carte de France des utilisateurs par region">
            <g class="france-mainland">
                <polygon class="france-region" id="Hauts-de-France" data-region="Hauts-de-France" points="350,34 445,56 458,118 382,138 322,104"></polygon>
                <polygon class="france-region" id="Normandie" data-region="Normandie" points="205,92 322,104 382,138 350,190 230,172 172,132"></polygon>
                <polygon class="france-region" id="Grand-Est" data-region="Grand Est" points="458,118 565,100 628,160 604,250 500,232 438,170"></polygon>
                <polygon class="france-region" id="Ile-de-France" data-region="Ile-de-France" points="350,190 382,138 438,170 430,228 365,242 326,212"></polygon>
                <polygon class="france-region" id="Bretagne" data-region="Bretagne" points="62,158 172,132 230,172 190,242 78,238 30,198"></polygon>
                <polygon class="france-region" id="Pays-de-la-Loire" data-region="Pays de la Loire" points="190,242 230,172 326,212 316,308 210,320 130,278"></polygon>
                <polygon class="france-region" id="Centre-Val-de-Loire" data-region="Centre-Val de Loire" points="326,212 365,242 430,228 455,314 378,365 316,308"></polygon>
                <polygon class="france-region" id="Bourgogne-Franche-Comte" data-region="Bourgogne-Franche-Comte" points="430,228 500,232 604,250 558,348 455,314"></polygon>
                <polygon class="france-region" id="Nouvelle-Aquitaine" data-region="Nouvelle-Aquitaine" points="130,278 210,320 316,308 378,365 330,456 205,500 118,420"></polygon>
                <polygon class="france-region" id="Auvergne-Rhone-Alpes" data-region="Auvergne-Rhone-Alpes" points="378,365 455,314 558,348 596,430 512,500 410,462"></polygon>
                <polygon class="france-region" id="Occitanie" data-region="Occitanie" points="205,500 330,456 410,462 432,530 288,545 190,528"></polygon>
                <polygon class="france-region" id="Provence-Alpes-Cote-d-Azur" data-region="Provence-Alpes-Cote d'Azur" points="512,500 596,430 672,458 642,522 560,540"></polygon>
                <polygon class="france-region" id="Corse" data-region="Corse" points="676,486 704,508 698,548 666,532"></polygon>
            </g>

            <g class="france-overseas" aria-label="Regions d outre-mer">
                <rect class="france-region" id="Guadeloupe" data-region="Guadeloupe" x="650" y="42" width="58" height="34" rx="6"></rect>
                <rect class="france-region" id="Martinique" data-region="Martinique" x="650" y="88" width="58" height="34" rx="6"></rect>
                <rect class="france-region" id="Guyane" data-region="Guyane" x="650" y="134" width="58" height="34" rx="6"></rect>
                <rect class="france-region" id="La-Reunion" data-region="La Reunion" x="650" y="180" width="58" height="34" rx="6"></rect>
                <rect class="france-region" id="Mayotte" data-region="Mayotte" x="650" y="226" width="58" height="34" rx="6"></rect>
            </g>
        </svg>
        <div class="map-tooltip" data-map-tooltip role="status" aria-live="polite"></div>
    </div>

    <script type="application/json" id="admin-region-data"><?= $usersByRegionJson ?></script>
    <script>
        (() => {
            const dataNode = document.getElementById('admin-region-data');
            const map = document.querySelector('.france-map');
            const tooltip = document.querySelector('[data-map-tooltip]');

            if (!dataNode || !map || !tooltip) {
                return;
            }

            const normalizeRegion = (value) => String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, ' ')
                .trim();

            const rawCounts = JSON.parse(dataNode.textContent || '{}');
            const counts = {};
            Object.entries(rawCounts).forEach(([region, total]) => {
                counts[normalizeRegion(region)] = Number(total) || 0;
            });

            const regions = Array.from(map.querySelectorAll('.france-region'));
            const maxCount = Math.max(1, ...Object.values(counts));

            regions.forEach((region) => {
                const regionName = region.dataset.region || region.id;
                const count = counts[normalizeRegion(regionName)] || 0;
                const intensity = count === 0 ? 0 : count / maxCount;
                const lightness = Math.round(94 - (intensity * 38));

                region.dataset.count = String(count);
                region.style.fill = count === 0 ? '#f3f4f6' : `hsl(330, 78%, ${lightness}%)`;

                region.addEventListener('mouseenter', () => {
                    tooltip.textContent = `${regionName} : ${count} inscrit${count > 1 ? 's' : ''}`;
                    tooltip.classList.add('visible');
                });

                region.addEventListener('mousemove', (event) => {
                    const bounds = map.parentElement.getBoundingClientRect();
                    tooltip.style.left = `${event.clientX - bounds.left + 12}px`;
                    tooltip.style.top = `${event.clientY - bounds.top + 12}px`;
                });

                region.addEventListener('mouseleave', () => {
                    tooltip.classList.remove('visible');
                });
            });
        })();
    </script>
</article>

<article class="card admin-analytics-card">
    <div class="section-heading">
        <h2>Inscriptions par jour</h2>
        <span class="admin-count">30 derniers jours</span>
    </div>

    <div class="registration-chart-wrap">
        <svg class="registration-chart" data-registration-chart role="img" aria-label="Courbe du nombre d inscriptions par jour"></svg>
    </div>

    <script type="application/json" id="admin-registration-data"><?= $registrationsByDayJson ?></script>
    <script>
        (() => {
            const dataNode = document.getElementById('admin-registration-data');
            const chart = document.querySelector('[data-registration-chart]');

            if (!dataNode || !chart) {
                return;
            }

            const data = JSON.parse(dataNode.textContent || '[]');
            if (!Array.isArray(data) || data.length === 0) {
                return;
            }

            const svgNamespace = 'http://www.w3.org/2000/svg';
            const width = 760;
            const height = 260;
            const padding = { top: 20, right: 24, bottom: 42, left: 48 };
            const innerWidth = width - padding.left - padding.right;
            const innerHeight = height - padding.top - padding.bottom;
            const maxTotal = Math.max(1, ...data.map((item) => Number(item.total) || 0));
            const labelStep = Math.max(1, Math.ceil(data.length / 6));

            chart.setAttribute('viewBox', `0 0 ${width} ${height}`);
            chart.replaceChildren();

            const createSvgElement = (name, attributes = {}) => {
                const element = document.createElementNS(svgNamespace, name);
                Object.entries(attributes).forEach(([key, value]) => {
                    element.setAttribute(key, String(value));
                });
                return element;
            };

            const xForIndex = (index) => {
                if (data.length === 1) {
                    return padding.left + innerWidth / 2;
                }

                return padding.left + (index / (data.length - 1)) * innerWidth;
            };

            const yForTotal = (total) => padding.top + innerHeight - ((Number(total) || 0) / maxTotal) * innerHeight;

            for (let index = 0; index <= 4; index += 1) {
                const ratio = index / 4;
                const y = padding.top + innerHeight - ratio * innerHeight;
                const value = Math.round(maxTotal * ratio);

                chart.appendChild(createSvgElement('line', {
                    class: 'registration-chart-grid',
                    x1: padding.left,
                    y1: y,
                    x2: width - padding.right,
                    y2: y,
                }));

                const axisLabel = createSvgElement('text', {
                    class: 'registration-chart-axis-label',
                    x: padding.left - 10,
                    y: y + 4,
                    'text-anchor': 'end',
                });
                axisLabel.textContent = String(value);
                chart.appendChild(axisLabel);
            }

            const linePoints = data.map((item, index) => `${xForIndex(index)},${yForTotal(item.total)}`);
            const areaPoints = [
                `${xForIndex(0)},${padding.top + innerHeight}`,
                ...linePoints,
                `${xForIndex(data.length - 1)},${padding.top + innerHeight}`,
            ];

            chart.appendChild(createSvgElement('polygon', {
                class: 'registration-chart-area',
                points: areaPoints.join(' '),
            }));

            chart.appendChild(createSvgElement('polyline', {
                class: 'registration-chart-line',
                points: linePoints.join(' '),
            }));

            data.forEach((item, index) => {
                const x = xForIndex(index);
                const y = yForTotal(item.total);
                const total = Number(item.total) || 0;

                chart.appendChild(createSvgElement('circle', {
                    class: 'registration-chart-point',
                    cx: x,
                    cy: y,
                    r: total > 0 ? 4 : 3,
                }));

                if (index % labelStep === 0 || index === data.length - 1) {
                    const label = createSvgElement('text', {
                        class: 'registration-chart-date-label',
                        x,
                        y: height - 12,
                        'text-anchor': 'middle',
                    });
                    label.textContent = String(item.label || item.date || '');
                    chart.appendChild(label);
                }
            });
        })();
    </script>
</article>

<article class="card admin-users-card">
    <div class="section-heading">
        <h2>Utilisateurs inscrits</h2>
        <span class="admin-count"><?= count($users) ?> utilisateur<?= count($users) > 1 ? 's' : '' ?></span>
    </div>

    <?php if ($users === []): ?>
        <p>Aucun utilisateur inscrit.</p>
    <?php else: ?>
        <div class="admin-users-list">
            <?php foreach ($users as $user): ?>
                <?php
                $userImagePath = trim((string)($user['image_profil'] ?? ''));
                if ($userImagePath !== '' && str_starts_with($userImagePath, 'public/')) {
                    $userImagePath = substr($userImagePath, 7);
                }
                $userImageUrl = $userImagePath !== '' ? asset($userImagePath) : '';
                $initial = strtoupper(substr((string)($user['pseudo'] ?? 'U'), 0, 1));
                $isCurrentUser = isset($_SESSION['user']['id']) && (int)$_SESSION['user']['id'] === (int)$user['id_utilisateur'];
                $isBanned = (int)($user['est_banni'] ?? 0) === 1;
                $banEndDate = trim((string)($user['date_fin_ban'] ?? ''));
                $banEndTimestamp = $banEndDate !== '' ? strtotime($banEndDate) : false;
                $formattedBanEndDate = $banEndTimestamp !== false ? date('d/m/Y H:i', $banEndTimestamp) : '';
                ?>
                <article class="admin-user-row">
                    <span class="admin-user-avatar">
                        <?php if ($userImageUrl !== ''): ?>
                            <img src="<?= htmlspecialchars($userImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <?php else: ?>
                            <?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    </span>

                    <div class="admin-user-main">
                        <strong><?= htmlspecialchars((string)($user['pseudo'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars((string)($user['mail'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if ($isBanned): ?>
                            <small class="admin-ban-details">
                                Banni<?= $formattedBanEndDate !== '' ? ' jusqu au ' . htmlspecialchars($formattedBanEndDate, ENT_QUOTES, 'UTF-8') : ' sans date de fin' ?>
                                <?php if (trim((string)($user['raison_ban'] ?? '')) !== ''): ?>
                                    - <?= htmlspecialchars((string)$user['raison_ban'], ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="admin-user-meta">
                        <span class="status-pill <?= ($user['role'] ?? '') === 'ADMIN' ? 'status-available' : 'status-unavailable' ?>">
                            <?= htmlspecialchars((string)($user['role'] ?? 'UTILISATEUR'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <?php if ($isBanned): ?>
                            <span class="status-pill status-banned">Banni</span>
                        <?php endif; ?>
                        <span><?= htmlspecialchars(number_format((float)($user['poisson'] ?? 0), 2, ',', ' '), ENT_QUOTES, 'UTF-8') ?> poisson</span>
                    </div>

                    <div class="admin-ban-actions">
                        <?php if ($isBanned): ?>
                            <form method="post" action="<?= htmlspecialchars(url('/admin/users/unban'), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="id_utilisateur" value="<?= (int)$user['id_utilisateur'] ?>">
                                <button class="btn btn-secondary" type="submit">Debannir</button>
                            </form>
                        <?php elseif (!$isCurrentUser): ?>
                            <form method="post" action="<?= htmlspecialchars(url('/admin/users/ban'), ENT_QUOTES, 'UTF-8') ?>" class="admin-ban-form">
                                <input type="hidden" name="id_utilisateur" value="<?= (int)$user['id_utilisateur'] ?>">
                                <div class="form-group">
                                    <label for="raison_ban_<?= (int)$user['id_utilisateur'] ?>">Raison</label>
                                    <textarea id="raison_ban_<?= (int)$user['id_utilisateur'] ?>" name="raison_ban" rows="2" maxlength="1000" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="date_fin_ban_<?= (int)$user['id_utilisateur'] ?>">Fin</label>
                                    <input id="date_fin_ban_<?= (int)$user['id_utilisateur'] ?>" name="date_fin_ban" type="datetime-local">
                                </div>
                                <button class="btn btn-secondary btn-danger" type="submit">Bannir</button>
                            </form>
                        <?php else: ?>
                            <p class="form-help">Compte admin actuel.</p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</article>
