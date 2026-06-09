<?php
declare(strict_types=1);

final class Proposition extends Model
{
    public function findByUtilisateur(int $utilisateurId): array
    {
        $sql = 'SELECT * FROM proposition
                WHERE id_demandeur = :utilisateur_id
                ORDER BY date_proposition DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['utilisateur_id' => $utilisateurId]);
        return $statement->fetchAll();
    }

    public function findRecues(int $utilisateurId): array
    {
        $sql = 'SELECT * FROM proposition
                WHERE id_receveur = :utilisateur_id
                ORDER BY date_proposition DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['utilisateur_id' => $utilisateurId]);
        return $statement->fetchAll();
    }

    public function findByIdForUser(int $propositionId, int $utilisateurId): ?array
    {
        $sql = 'SELECT proposition.*,
                       objet.id_objet,
                       objet.nom AS objet_nom,
                       objet.image AS objet_image,
                       objet.prix AS objet_prix,
                       objet.est_dispo AS objet_est_dispo,
                       demandeur.pseudo AS demandeur_pseudo,
                       receveur.pseudo AS receveur_pseudo
                FROM proposition
                LEFT JOIN proposition_objet_demande pod ON pod.id_proposition = proposition.id_proposition
                LEFT JOIN objet ON objet.id_objet = pod.id_objet
                INNER JOIN utilisateur demandeur ON demandeur.id_utilisateur = proposition.id_demandeur
                INNER JOIN utilisateur receveur ON receveur.id_utilisateur = proposition.id_receveur
                WHERE proposition.id_proposition = :id_proposition
                  AND (proposition.id_demandeur = :id_demandeur_check OR proposition.id_receveur = :id_receveur_check)
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id_proposition' => $propositionId,
            'id_demandeur_check' => $utilisateurId,
            'id_receveur_check' => $utilisateurId,
        ]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    public function findConversationForObjet(int $objetId, int $demandeurId, int $receveurId): ?array
    {
        $sql = 'SELECT proposition.*
                FROM proposition
                INNER JOIN proposition_objet_demande pod ON pod.id_proposition = proposition.id_proposition
                WHERE pod.id_objet = :id_objet
                  AND proposition.id_demandeur = :id_demandeur
                  AND proposition.id_receveur = :id_receveur
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id_objet' => $objetId,
            'id_demandeur' => $demandeurId,
            'id_receveur' => $receveurId,
        ]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    public function createConversationForObjet(int $objetId, int $demandeurId, int $receveurId, float $prixPoisson): int
    {
        $this->db->beginTransaction();

        try {
            $propositionId = $this->create([
                'prix_poisson' => $prixPoisson,
                'etat_proposition' => 'ATTENTE',
                'id_demandeur' => $demandeurId,
                'id_receveur' => $receveurId,
            ]);

            $sql = 'INSERT INTO proposition_objet_demande (id_proposition, id_objet)
                    VALUES (:id_proposition, :id_objet)';
            $statement = $this->db->prepare($sql);
            $statement->execute([
                'id_proposition' => $propositionId,
                'id_objet' => $objetId,
            ]);

            $this->db->commit();
            return $propositionId;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO proposition (prix_poisson, etat_proposition, id_demandeur, id_receveur)
                VALUES (:prix_poisson, :etat_proposition, :id_demandeur, :id_receveur)';

        $statement = $this->db->prepare($sql);
        $statement->execute([
            'prix_poisson' => $data['prix_poisson'] ?? 0,
            'etat_proposition' => $data['etat_proposition'] ?? 'ATTENTE',
            'id_demandeur' => $data['id_demandeur'],
            'id_receveur' => $data['id_receveur'],
        ]);

        return (int)$this->db->lastInsertId();
    }
}
