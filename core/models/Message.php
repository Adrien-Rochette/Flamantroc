<?php
declare(strict_types=1);

final class Message extends Model
{
    public function findByProposition(int $propositionId): array
    {
        $sql = 'SELECT message_chat.*, utilisateur.pseudo AS utilisateur_pseudo, utilisateur.image_profil
                FROM message_chat
                INNER JOIN utilisateur ON utilisateur.id_utilisateur = message_chat.id_utilisateur
                WHERE id_proposition = :id_proposition
                ORDER BY date_heure ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id_proposition' => $propositionId]);
        return $statement->fetchAll();
    }

    public function conversationsForUser(int $utilisateurId): array
    {
        $sql = 'SELECT proposition.id_proposition,
                       proposition.id_demandeur,
                       proposition.id_receveur,
                       proposition.date_proposition,
                       objet.id_objet,
                       objet.nom AS objet_nom,
                       objet.image AS objet_image,
                       autre.id_utilisateur AS autre_id,
                       autre.pseudo AS autre_pseudo,
                       autre.image_profil AS autre_image_profil,
                       dernier.contenu AS dernier_message,
                       dernier.date_heure AS dernier_message_date
                FROM proposition
                LEFT JOIN proposition_objet_demande pod ON pod.id_proposition = proposition.id_proposition
                LEFT JOIN objet ON objet.id_objet = pod.id_objet
                INNER JOIN utilisateur autre
                    ON autre.id_utilisateur = CASE
                        WHEN proposition.id_demandeur = :case_utilisateur THEN proposition.id_receveur
                        ELSE proposition.id_demandeur
                    END
                LEFT JOIN message_chat dernier
                    ON dernier.id_message = (
                        SELECT message_chat.id_message
                        FROM message_chat
                        WHERE message_chat.id_proposition = proposition.id_proposition
                        ORDER BY message_chat.date_heure DESC, message_chat.id_message DESC
                        LIMIT 1
                    )
                WHERE proposition.id_demandeur = :demandeur_utilisateur
                   OR proposition.id_receveur = :receveur_utilisateur
                ORDER BY COALESCE(dernier.date_heure, proposition.date_proposition) DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'case_utilisateur' => $utilisateurId,
            'demandeur_utilisateur' => $utilisateurId,
            'receveur_utilisateur' => $utilisateurId,
        ]);

        return $statement->fetchAll();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO message_chat (contenu, id_proposition, id_utilisateur)
                VALUES (:contenu, :id_proposition, :id_utilisateur)';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'contenu' => $data['contenu'],
            'id_proposition' => $data['id_proposition'],
            'id_utilisateur' => $data['id_utilisateur'],
        ]);

        return (int)$this->db->lastInsertId();
    }
}
