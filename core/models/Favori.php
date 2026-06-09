<?php
declare(strict_types=1);

final class Favori extends Model
{
    public function exists(int $utilisateurId, int $objetId): bool
    {
        $sql = 'SELECT 1
                FROM favori
                WHERE id_utilisateur = :id_utilisateur
                  AND id_objet = :id_objet
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id_utilisateur' => $utilisateurId,
            'id_objet' => $objetId,
        ]);

        return (bool)$statement->fetchColumn();
    }

    public function add(int $utilisateurId, int $objetId): void
    {
        $sql = 'INSERT IGNORE INTO favori (id_utilisateur, id_objet)
                VALUES (:id_utilisateur, :id_objet)';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id_utilisateur' => $utilisateurId,
            'id_objet' => $objetId,
        ]);
    }

    public function remove(int $utilisateurId, int $objetId): void
    {
        $sql = 'DELETE FROM favori
                WHERE id_utilisateur = :id_utilisateur
                  AND id_objet = :id_objet';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id_utilisateur' => $utilisateurId,
            'id_objet' => $objetId,
        ]);
    }

    public function toggle(int $utilisateurId, int $objetId): bool
    {
        if ($this->exists($utilisateurId, $objetId)) {
            $this->remove($utilisateurId, $objetId);
            return false;
        }

        $this->add($utilisateurId, $objetId);
        return true;
    }

    public function findByUtilisateur(int $utilisateurId): array
    {
        $sql = 'SELECT objet.*,
                       categorie.nom AS categorie_nom,
                       utilisateur.pseudo AS utilisateur_pseudo,
                       favori.date_ajout,
                       1 AS est_favori
                FROM favori
                INNER JOIN objet ON objet.id_objet = favori.id_objet
                INNER JOIN categorie ON categorie.id_categorie = objet.id_categorie
                INNER JOIN utilisateur ON utilisateur.id_utilisateur = objet.id_utilisateur
                WHERE favori.id_utilisateur = :id_utilisateur
                  AND objet.est_dispo = 1
                ORDER BY favori.date_ajout DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id_utilisateur' => $utilisateurId]);

        return $statement->fetchAll();
    }
}
