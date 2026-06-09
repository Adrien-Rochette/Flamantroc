<?php
$conversations = $conversations ?? [];
$messages = $messages ?? [];
$activeConversation = $activeConversation ?? null;
$currentUserId = (int)($_SESSION['user']['id'] ?? 0);
$activeId = $activeConversation !== null ? (int)$activeConversation['id_proposition'] : 0;
?>
<section class="messages-layout">
    <aside class="conversation-list" aria-label="Conversations">
        <?php if ($conversations === []): ?>
            <div class="empty-chat">
                <h2>Aucun message</h2>
                <p>Les conversations demarrent depuis une offre.</p>
            </div>
        <?php else: ?>
            <?php foreach ($conversations as $conversation): ?>
                <?php
                $conversationId = (int)$conversation['id_proposition'];
                $avatarPath = trim((string)($conversation['autre_image_profil'] ?? ''));
                if ($avatarPath !== '' && str_starts_with($avatarPath, 'public/')) {
                    $avatarPath = substr($avatarPath, 7);
                }
                $avatarUrl = $avatarPath !== '' ? asset($avatarPath) : '';
                $objectImagePath = trim((string)($conversation['objet_image'] ?? ''));
                if ($objectImagePath !== '' && str_starts_with($objectImagePath, 'public/')) {
                    $objectImagePath = substr($objectImagePath, 7);
                }
                $objectImageUrl = $objectImagePath !== '' ? asset($objectImagePath) : '';
                $lastMessage = trim((string)($conversation['dernier_message'] ?? ''));
                ?>
                <a class="conversation-item <?= $conversationId === $activeId ? 'active' : '' ?>" href="<?= htmlspecialchars(url('/messages/conversation?id=' . $conversationId), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="conversation-avatar">
                        <?php if ($avatarUrl !== ''): ?>
                            <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
                        <?php else: ?>
                            <?= htmlspecialchars(strtoupper(substr((string)($conversation['autre_pseudo'] ?? 'U'), 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    </span>
                    <span class="conversation-summary">
                        <strong><?= htmlspecialchars((string)($conversation['autre_pseudo'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?></strong>
                        <span><?= htmlspecialchars((string)($conversation['objet_nom'] ?? 'Offre supprimee'), ENT_QUOTES, 'UTF-8') ?></span>
                        <small><?= htmlspecialchars($lastMessage !== '' ? $lastMessage : 'Conversation ouverte', ENT_QUOTES, 'UTF-8') ?></small>
                    </span>
                    <?php if ($objectImageUrl !== ''): ?>
                        <img class="conversation-object" src="<?= htmlspecialchars($objectImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="">
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </aside>

    <div class="chat-panel">
        <?php if ($activeConversation === null): ?>
            <div class="empty-chat">
                <h2>Choisis une conversation</h2>
                <p>Ouvre un echange pour lire et envoyer des messages.</p>
            </div>
        <?php else: ?>
            <?php
            $otherName = (int)$activeConversation['id_demandeur'] === $currentUserId
                ? (string)$activeConversation['receveur_pseudo']
                : (string)$activeConversation['demandeur_pseudo'];
            $canHandleOffer = (int)$activeConversation['id_receveur'] === $currentUserId
                && (string)$activeConversation['etat_proposition'] === 'ATTENTE';
            $canBuyFromChat = (int)$activeConversation['id_demandeur'] === $currentUserId
                && (string)$activeConversation['etat_proposition'] === 'ATTENTE'
                && (int)($activeConversation['objet_est_dispo'] ?? 0) === 1
                && abs((float)$activeConversation['prix_poisson'] - (float)($activeConversation['objet_prix'] ?? 0)) < 0.01;
            ?>
            <header class="chat-header">
                <div>
                    <h2><?= htmlspecialchars($otherName, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars((string)($activeConversation['objet_nom'] ?? 'Offre supprimee'), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="chat-offer-summary">
                    <span><?= htmlspecialchars(number_format((float)$activeConversation['prix_poisson'], 2, ',', ' '), ENT_QUOTES, 'UTF-8') ?> poissons</span>
                    <strong><?= htmlspecialchars((string)$activeConversation['etat_proposition'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <?php if ($canHandleOffer): ?>
                    <div class="chat-offer-actions">
                        <form method="post" action="<?= htmlspecialchars(url('/transactions/accepter-offre'), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id_proposition" value="<?= (int)$activeConversation['id_proposition'] ?>">
                            <button class="btn btn-primary" type="submit">Accepter</button>
                        </form>
                        <form method="post" action="<?= htmlspecialchars(url('/transactions/refuser-offre'), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id_proposition" value="<?= (int)$activeConversation['id_proposition'] ?>">
                            <button class="btn btn-secondary" type="submit">Refuser</button>
                        </form>
                    </div>
                <?php elseif ($canBuyFromChat): ?>
                    <div class="chat-offer-actions">
                        <form method="post" action="<?= htmlspecialchars(url('/transactions/acheter-conversation'), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id_proposition" value="<?= (int)$activeConversation['id_proposition'] ?>">
                            <button class="btn btn-primary" type="submit">Acheter</button>
                        </form>
                    </div>
                <?php endif; ?>
            </header>

            <div class="message-thread">
                <?php if ($messages === []): ?>
                    <p class="form-help">Aucun message dans cette conversation.</p>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <?php
                        $isMine = (int)$message['id_utilisateur'] === $currentUserId;
                        $messageDate = (string)($message['date_heure'] ?? '');
                        $messageTimestamp = $messageDate !== '' ? strtotime($messageDate) : false;
                        $formattedDate = $messageTimestamp !== false ? date('d/m/Y H:i', $messageTimestamp) : '';
                        $content = (string)$message['contenu'];
                        $isSystemMessage = in_array($content, ['Transaction effectuée', 'achat effectué', 'Offre refusée'], true)
                            || str_starts_with($content, 'Offre de prix proposée :');
                        ?>
                        <article class="message-bubble <?= $isSystemMessage ? 'system-message' : ($isMine ? 'mine' : 'theirs') ?>">
                            <p><?= nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) ?></p>
                            <?php if ($formattedDate !== ''): ?>
                                <time datetime="<?= htmlspecialchars($messageDate, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?></time>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="post" action="<?= htmlspecialchars(url('/messages/envoyer'), ENT_QUOTES, 'UTF-8') ?>" class="message-compose">
                <input type="hidden" name="id_proposition" value="<?= (int)$activeConversation['id_proposition'] ?>">
                <label class="sr-only" for="message-contenu">Message</label>
                <textarea id="message-contenu" name="contenu" rows="2" maxlength="2000" placeholder="Ecrire un message" required></textarea>
                <button class="btn btn-primary" type="submit">Envoyer</button>
            </form>
        <?php endif; ?>
    </div>
</section>
