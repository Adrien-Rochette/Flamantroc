<?php
declare(strict_types=1);

final class TransactionController extends Controller
{
    public function acheter(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $objetId = (int)($_POST['id_objet'] ?? 0);
        $isConfirmed = (string)($_POST['confirm_achat'] ?? '') === '1';

        if ($objetId <= 0) {
            $_SESSION['error'] = 'Offre introuvable.';
            $this->redirect('/offres');
        }

        if (!$isConfirmed) {
            $this->preparePurchase($objetId);
            return;
        }

        $pendingId = (int)($_SESSION['pending_purchase_id'] ?? 0);
        if ($pendingId !== $objetId) {
            $_SESSION['error'] = 'Achat non confirme.';
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        try {
            $propositionId = $this->completePurchase($objetId);
        } catch (RuntimeException $exception) {
            $_SESSION['error'] = $exception->getMessage();
            $this->redirect('/offres/detail?id=' . $objetId);
        } catch (Throwable $exception) {
            $_SESSION['error'] = "Impossible de finaliser l'achat.";
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        unset($_SESSION['pending_purchase_id']);
        $_SESSION['success'] = 'Transaction effectuee.';
        $this->redirect('/messages/conversation?id=' . $propositionId);
    }

    public function proposerPrix(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $objetId = (int)($_POST['id_objet'] ?? 0);
        $prixInput = trim((string)($_POST['prix_propose'] ?? ''));

        if ($objetId <= 0) {
            $_SESSION['error'] = 'Offre introuvable.';
            $this->redirect('/offres');
        }

        if (!preg_match('/^\d+(?:[,.]\d{1,2})?$/', $prixInput)) {
            $_SESSION['error'] = 'Entre un prix propose valide.';
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        $prixPropose = (float)str_replace(',', '.', $prixInput);
        if ($prixPropose <= 0) {
            $_SESSION['error'] = 'Le prix propose doit etre superieur a 0.';
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        try {
            $propositionId = $this->createNegotiation($objetId, $prixPropose);
        } catch (RuntimeException $exception) {
            $_SESSION['error'] = $exception->getMessage();
            $this->redirect('/offres/detail?id=' . $objetId);
        } catch (Throwable $exception) {
            $_SESSION['error'] = "Impossible d'envoyer cette offre de prix.";
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        $_SESSION['success'] = 'Offre de prix envoyee.';
        $this->redirect('/messages/conversation?id=' . $propositionId);
    }

    public function acheterConversation(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $propositionId = (int)($_POST['id_proposition'] ?? 0);
        if ($propositionId <= 0) {
            $_SESSION['error'] = 'Conversation introuvable.';
            $this->redirect('/messages');
        }

        try {
            $this->completePurchaseFromConversation($propositionId);
        } catch (RuntimeException $exception) {
            $_SESSION['error'] = $exception->getMessage();
            $this->redirect('/messages/conversation?id=' . $propositionId);
        } catch (Throwable $exception) {
            $_SESSION['error'] = "Impossible de finaliser l'achat.";
            $this->redirect('/messages/conversation?id=' . $propositionId);
        }

        $_SESSION['success'] = 'Achat effectue.';
        $this->redirect('/messages/conversation?id=' . $propositionId);
    }

    public function annulerAchat(): void
    {
        $objetId = (int)($_POST['id_objet'] ?? 0);
        unset($_SESSION['pending_purchase_id'], $_SESSION['show_purchase_confirmation']);

        if ($objetId > 0) {
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        $this->redirect('/offres');
    }

    public function accepterOffre(): void
    {
        $this->handleNegotiationDecision(true);
    }

    public function refuserOffre(): void
    {
        $this->handleNegotiationDecision(false);
    }

    private function preparePurchase(int $objetId): void
    {
        $objetModel = new Objet();
        $objet = $objetModel->findById($objetId);

        if ($objet === null || (int)$objet['est_dispo'] !== 1) {
            $_SESSION['error'] = 'Cette offre n est plus disponible.';
            $this->redirect('/offres');
        }

        $buyerId = (int)$_SESSION['user']['id'];
        if ((int)$objet['id_utilisateur'] === $buyerId) {
            $_SESSION['error'] = 'Tu ne peux pas acheter ta propre annonce.';
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        $userModel = new User();
        $buyer = $userModel->findById($buyerId);
        if ($buyer === null || (float)$buyer['poisson'] < (float)$objet['prix']) {
            $_SESSION['error'] = 'Solde de Poissons insuffisant';
            $this->redirect('/offres/detail?id=' . $objetId);
        }

        $_SESSION['pending_purchase_id'] = $objetId;
        $_SESSION['show_purchase_confirmation'] = true;
        $this->redirect('/offres/detail?id=' . $objetId);
    }

    private function completePurchase(int $objetId): int
    {
        $db = Database::getConnection();
        $buyerId = (int)$_SESSION['user']['id'];

        $db->beginTransaction();

        try {
            $objet = $this->findObjectForUpdate($db, $objetId);
            if ($objet === null || (int)$objet['est_dispo'] !== 1) {
                throw new RuntimeException('Cette offre n est plus disponible.');
            }

            $sellerId = (int)$objet['id_utilisateur'];
            if ($sellerId === $buyerId) {
                throw new RuntimeException('Tu ne peux pas acheter ta propre annonce.');
            }

            $price = (float)$objet['prix'];
            $buyer = $this->findUserForUpdate($db, $buyerId);
            $seller = $this->findUserForUpdate($db, $sellerId);

            if ($buyer === null || $seller === null) {
                throw new RuntimeException('Utilisateur introuvable.');
            }

            if ((float)$buyer['poisson'] < $price) {
                throw new RuntimeException('Solde de Poissons insuffisant');
            }

            $this->transferPoissons($db, $buyerId, $sellerId, $price);
            $this->markObjectUnavailable($db, $objetId);
            $propositionId = $this->createProposition($db, $price, 'ACCEPTEE', $buyerId, $sellerId);
            $this->linkRequestedObject($db, $propositionId, $objetId);
            $this->createSystemMessage($db, $propositionId, $buyerId, 'achat effectué');

            $db->commit();
            $_SESSION['user']['poisson'] = (float)$buyer['poisson'] - $price;

            return $propositionId;
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    private function createNegotiation(int $objetId, float $prixPropose): int
    {
        $db = Database::getConnection();
        $buyerId = (int)$_SESSION['user']['id'];

        $db->beginTransaction();

        try {
            $objet = $this->findObjectForUpdate($db, $objetId);
            if ($objet === null || (int)$objet['est_dispo'] !== 1) {
                throw new RuntimeException('Cette offre n est plus disponible.');
            }

            $sellerId = (int)$objet['id_utilisateur'];
            if ($sellerId === $buyerId) {
                throw new RuntimeException('Tu ne peux pas faire une offre sur ta propre annonce.');
            }

            $propositionId = $this->createProposition($db, $prixPropose, 'ATTENTE', $buyerId, $sellerId);
            $this->linkRequestedObject($db, $propositionId, $objetId);
            $this->createSystemMessage(
                $db,
                $propositionId,
                $buyerId,
                'Offre de prix proposée : ' . number_format($prixPropose, 2, ',', ' ') . ' poissons'
            );

            $db->commit();
            return $propositionId;
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    private function completePurchaseFromConversation(int $propositionId): void
    {
        $db = Database::getConnection();
        $buyerId = (int)$_SESSION['user']['id'];

        $db->beginTransaction();

        try {
            $proposition = $this->findPropositionForUpdate($db, $propositionId);
            if ($proposition === null || (int)$proposition['id_demandeur'] !== $buyerId) {
                throw new RuntimeException('Conversation introuvable.');
            }

            if ((string)$proposition['etat_proposition'] !== 'ATTENTE') {
                throw new RuntimeException('Cette proposition a deja ete traitee.');
            }

            $objetId = (int)$proposition['id_objet'];
            $objet = $this->findObjectForUpdate($db, $objetId);
            if ($objet === null || (int)$objet['est_dispo'] !== 1) {
                throw new RuntimeException('Cette offre n est plus disponible.');
            }

            $sellerId = (int)$objet['id_utilisateur'];
            if ($sellerId === $buyerId) {
                throw new RuntimeException('Tu ne peux pas acheter ta propre annonce.');
            }

            $price = (float)$objet['prix'];
            $buyer = $this->findUserForUpdate($db, $buyerId);
            $seller = $this->findUserForUpdate($db, $sellerId);

            if ($buyer === null || $seller === null) {
                throw new RuntimeException('Utilisateur introuvable.');
            }

            if ((float)$buyer['poisson'] < $price) {
                throw new RuntimeException('Solde de Poissons insuffisant');
            }

            $this->transferPoissons($db, $buyerId, $sellerId, $price);
            $this->markObjectUnavailable($db, $objetId);
            $this->updatePropositionPriceAndState($db, $propositionId, $price, 'ACCEPTEE');
            $this->createSystemMessage($db, $propositionId, $buyerId, 'achat effectué');

            $db->commit();
            $_SESSION['user']['poisson'] = (float)$buyer['poisson'] - $price;
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    private function handleNegotiationDecision(bool $accept): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $propositionId = (int)($_POST['id_proposition'] ?? 0);
        if ($propositionId <= 0) {
            $_SESSION['error'] = 'Proposition introuvable.';
            $this->redirect('/messages');
        }

        try {
            if ($accept) {
                $this->acceptNegotiation($propositionId);
                $_SESSION['success'] = 'Offre acceptee.';
            } else {
                $this->refuseNegotiation($propositionId);
                $_SESSION['success'] = 'Offre refusee.';
            }
        } catch (RuntimeException $exception) {
            $_SESSION['error'] = $exception->getMessage();
        } catch (Throwable $exception) {
            $_SESSION['error'] = 'Impossible de traiter cette proposition.';
        }

        $this->redirect('/messages/conversation?id=' . $propositionId);
    }

    private function acceptNegotiation(int $propositionId): void
    {
        $db = Database::getConnection();
        $sellerId = (int)$_SESSION['user']['id'];

        $db->beginTransaction();

        try {
            $proposition = $this->findPropositionForUpdate($db, $propositionId);
            if ($proposition === null || (int)$proposition['id_receveur'] !== $sellerId) {
                throw new RuntimeException('Proposition introuvable.');
            }

            if ((string)$proposition['etat_proposition'] !== 'ATTENTE') {
                throw new RuntimeException('Cette proposition a deja ete traitee.');
            }

            $objetId = (int)$proposition['id_objet'];
            $buyerId = (int)$proposition['id_demandeur'];
            $price = (float)$proposition['prix_poisson'];

            $objet = $this->findObjectForUpdate($db, $objetId);
            if ($objet === null || (int)$objet['est_dispo'] !== 1) {
                throw new RuntimeException('Cette offre n est plus disponible.');
            }

            $buyer = $this->findUserForUpdate($db, $buyerId);
            $seller = $this->findUserForUpdate($db, $sellerId);
            if ($buyer === null || $seller === null) {
                throw new RuntimeException('Utilisateur introuvable.');
            }

            if ((float)$buyer['poisson'] < $price) {
                throw new RuntimeException('Solde de Poissons insuffisant');
            }

            $this->transferPoissons($db, $buyerId, $sellerId, $price);
            $this->markObjectUnavailable($db, $objetId);
            $this->updatePropositionState($db, $propositionId, 'ACCEPTEE');
            $this->createSystemMessage($db, $propositionId, $sellerId, 'achat effectué');

            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    private function refuseNegotiation(int $propositionId): void
    {
        $db = Database::getConnection();
        $sellerId = (int)$_SESSION['user']['id'];

        $db->beginTransaction();

        try {
            $proposition = $this->findPropositionForUpdate($db, $propositionId);
            if ($proposition === null || (int)$proposition['id_receveur'] !== $sellerId) {
                throw new RuntimeException('Proposition introuvable.');
            }

            if ((string)$proposition['etat_proposition'] !== 'ATTENTE') {
                throw new RuntimeException('Cette proposition a deja ete traitee.');
            }

            $this->updatePropositionState($db, $propositionId, 'REFUSEE');
            $this->createSystemMessage($db, $propositionId, $sellerId, 'Offre refusée');

            $db->commit();
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    private function findObjectForUpdate(PDO $db, int $objetId): ?array
    {
        $statement = $db->prepare('SELECT * FROM objet WHERE id_objet = :id_objet LIMIT 1 FOR UPDATE');
        $statement->execute(['id_objet' => $objetId]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    private function findUserForUpdate(PDO $db, int $userId): ?array
    {
        $statement = $db->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = :id_utilisateur LIMIT 1 FOR UPDATE');
        $statement->execute(['id_utilisateur' => $userId]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    private function findPropositionForUpdate(PDO $db, int $propositionId): ?array
    {
        $sql = 'SELECT proposition.*, pod.id_objet
                FROM proposition
                INNER JOIN proposition_objet_demande pod ON pod.id_proposition = proposition.id_proposition
                WHERE proposition.id_proposition = :id_proposition
                LIMIT 1
                FOR UPDATE';
        $statement = $db->prepare($sql);
        $statement->execute(['id_proposition' => $propositionId]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    private function transferPoissons(PDO $db, int $buyerId, int $sellerId, float $amount): void
    {
        $debit = $db->prepare('UPDATE utilisateur SET poisson = poisson - :amount WHERE id_utilisateur = :buyer_id');
        $debit->execute([
            'amount' => $amount,
            'buyer_id' => $buyerId,
        ]);

        $credit = $db->prepare('UPDATE utilisateur SET poisson = poisson + :amount WHERE id_utilisateur = :seller_id');
        $credit->execute([
            'amount' => $amount,
            'seller_id' => $sellerId,
        ]);
    }

    private function markObjectUnavailable(PDO $db, int $objetId): void
    {
        $statement = $db->prepare('UPDATE objet SET est_dispo = 0 WHERE id_objet = :id_objet');
        $statement->execute(['id_objet' => $objetId]);
    }

    private function createProposition(PDO $db, float $price, string $state, int $buyerId, int $sellerId): int
    {
        $sql = 'INSERT INTO proposition (prix_poisson, etat_proposition, id_demandeur, id_receveur)
                VALUES (:prix_poisson, :etat_proposition, :id_demandeur, :id_receveur)';
        $statement = $db->prepare($sql);
        $statement->execute([
            'prix_poisson' => $price,
            'etat_proposition' => $state,
            'id_demandeur' => $buyerId,
            'id_receveur' => $sellerId,
        ]);

        return (int)$db->lastInsertId();
    }

    private function linkRequestedObject(PDO $db, int $propositionId, int $objetId): void
    {
        $sql = 'INSERT INTO proposition_objet_demande (id_proposition, id_objet)
                VALUES (:id_proposition, :id_objet)';
        $statement = $db->prepare($sql);
        $statement->execute([
            'id_proposition' => $propositionId,
            'id_objet' => $objetId,
        ]);
    }

    private function updatePropositionState(PDO $db, int $propositionId, string $state): void
    {
        $statement = $db->prepare(
            'UPDATE proposition SET etat_proposition = :etat_proposition WHERE id_proposition = :id_proposition'
        );
        $statement->execute([
            'etat_proposition' => $state,
            'id_proposition' => $propositionId,
        ]);
    }

    private function updatePropositionPriceAndState(PDO $db, int $propositionId, float $price, string $state): void
    {
        $statement = $db->prepare(
            'UPDATE proposition
             SET prix_poisson = :prix_poisson,
                 etat_proposition = :etat_proposition
             WHERE id_proposition = :id_proposition'
        );
        $statement->execute([
            'prix_poisson' => $price,
            'etat_proposition' => $state,
            'id_proposition' => $propositionId,
        ]);
    }

    private function createSystemMessage(PDO $db, int $propositionId, int $userId, string $content): void
    {
        $sql = 'INSERT INTO message_chat (contenu, id_proposition, id_utilisateur)
                VALUES (:contenu, :id_proposition, :id_utilisateur)';
        $statement = $db->prepare($sql);
        $statement->execute([
            'contenu' => $content,
            'id_proposition' => $propositionId,
            'id_utilisateur' => $userId,
        ]);
    }
}
