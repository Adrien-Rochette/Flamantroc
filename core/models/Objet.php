<?php
declare(strict_types=1);

final class Objet extends Model
{
    public function all(?int $utilisateurId = null): array
    {
        return $this->search($utilisateurId);
    }

    public function search(?int $utilisateurId = null, array $filters = []): array
    {
        $favoriteSelect = $utilisateurId !== null ? ', CASE WHEN favori.id_objet IS NULL THEN 0 ELSE 1 END AS est_favori' : ', 0 AS est_favori';
        $favoriteJoin = $utilisateurId !== null
            ? ' LEFT JOIN favori ON favori.id_objet = objet.id_objet AND favori.id_utilisateur = :id_utilisateur_favori'
            : '';

        $where = ['objet.est_dispo = 1'];
        $parameters = [];

        $sellerInitial = trim((string)($filters['vendeur'] ?? ''));
        if ($sellerInitial !== '') {
            $where[] = 'UPPER(SUBSTRING(TRIM(utilisateur.pseudo), 1, 1)) = :vendeur';
            $parameters['vendeur'] = $sellerInitial;
        }

        $categorieId = (int)($filters['categorie'] ?? 0);
        if ($categorieId > 0) {
            $where[] = 'objet.id_categorie = :id_categorie';
            $parameters['id_categorie'] = $categorieId;
        }

        if (isset($filters['prix_min']) && $filters['prix_min'] !== null) {
            $where[] = 'objet.prix >= :prix_min';
            $parameters['prix_min'] = (float)$filters['prix_min'];
        }

        if (isset($filters['prix_max']) && $filters['prix_max'] !== null) {
            $where[] = 'objet.prix <= :prix_max';
            $parameters['prix_max'] = (float)$filters['prix_max'];
        }

        $region = trim((string)($filters['region'] ?? ''));
        if ($region !== '') {
            $where[] = 'TRIM(utilisateur.region) = :region';
            $parameters['region'] = $region;
        }

        $sql = 'SELECT objet.*, categorie.nom AS categorie_nom, utilisateur.pseudo AS utilisateur_pseudo,
                       utilisateur.image_profil AS utilisateur_image_profil,
                       TRIM(utilisateur.region) AS utilisateur_region' . $favoriteSelect . '
                FROM objet
                INNER JOIN categorie ON categorie.id_categorie = objet.id_categorie
                INNER JOIN utilisateur ON utilisateur.id_utilisateur = objet.id_utilisateur
                ' . $favoriteJoin . '
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY objet.date_creation DESC';
        $statement = $this->db->prepare($sql);
        if ($utilisateurId !== null) {
            $parameters['id_utilisateur_favori'] = $utilisateurId;
        }
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    public function availableSellerInitials(): array
    {
        $sql = 'SELECT DISTINCT UPPER(SUBSTRING(TRIM(utilisateur.pseudo), 1, 1)) AS initiale
                FROM objet
                INNER JOIN utilisateur ON utilisateur.id_utilisateur = objet.id_utilisateur
                WHERE objet.est_dispo = 1
                  AND TRIM(utilisateur.pseudo) <> \'\'
                ORDER BY initiale ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute();

        $initials = [];
        foreach ($statement->fetchAll() as $row) {
            $initial = trim((string)($row['initiale'] ?? ''));
            if ($initial !== '') {
                $initials[] = $initial;
            }
        }

        return $initials;
    }

    public function availableRegions(): array
    {
        $sql = 'SELECT DISTINCT TRIM(utilisateur.region) AS region
                FROM objet
                INNER JOIN utilisateur ON utilisateur.id_utilisateur = objet.id_utilisateur
                WHERE objet.est_dispo = 1
                  AND utilisateur.region IS NOT NULL
                  AND TRIM(utilisateur.region) <> \'\'
                ORDER BY region ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute();

        $regions = [];
        foreach ($statement->fetchAll() as $row) {
            $region = trim((string)($row['region'] ?? ''));
            if ($region !== '') {
                $regions[] = $region;
            }
        }

        return $regions;
    }

    public function findById(int $id, ?int $utilisateurId = null): ?array
    {
        $favoriteSelect = $utilisateurId !== null ? ', CASE WHEN favori.id_objet IS NULL THEN 0 ELSE 1 END AS est_favori' : ', 0 AS est_favori';
        $favoriteJoin = $utilisateurId !== null
            ? ' LEFT JOIN favori ON favori.id_objet = objet.id_objet AND favori.id_utilisateur = :id_utilisateur_favori'
            : '';

        $sql = 'SELECT objet.*, categorie.nom AS categorie_nom, utilisateur.pseudo AS utilisateur_pseudo,
                       utilisateur.image_profil AS utilisateur_image_profil' . $favoriteSelect . '
                FROM objet
                INNER JOIN categorie ON categorie.id_categorie = objet.id_categorie
                INNER JOIN utilisateur ON utilisateur.id_utilisateur = objet.id_utilisateur
                ' . $favoriteJoin . '
                WHERE objet.id_objet = :id
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $parameters = ['id' => $id];
        if ($utilisateurId !== null) {
            $parameters['id_utilisateur_favori'] = $utilisateurId;
        }
        $statement->execute($parameters);
        $row = $statement->fetch();
        return $row ?: null;
    }

    public function findByUtilisateur(int $utilisateurId): array
    {
        $sql = 'SELECT objet.*, categorie.nom AS categorie_nom
                FROM objet
                INNER JOIN categorie ON categorie.id_categorie = objet.id_categorie
                WHERE objet.id_utilisateur = :id_utilisateur
                ORDER BY objet.date_creation DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id_utilisateur' => $utilisateurId]);

        return $statement->fetchAll();
    }

    public function updateAvailabilityForOwner(int $objetId, int $utilisateurId, bool $isAvailable): bool
    {
        $sql = 'UPDATE objet
                SET est_dispo = :est_dispo
                WHERE id_objet = :id_objet
                  AND id_utilisateur = :id_utilisateur';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'est_dispo' => $isAvailable ? 1 : 0,
            'id_objet' => $objetId,
            'id_utilisateur' => $utilisateurId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function deleteForOwner(int $objetId, int $utilisateurId): bool
    {
        $sql = 'DELETE FROM objet
                WHERE id_objet = :id_objet
                  AND id_utilisateur = :id_utilisateur';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id_objet' => $objetId,
            'id_utilisateur' => $utilisateurId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO objet (nom, image, description, prix, est_dispo, date_creation, id_utilisateur, id_categorie)
                VALUES (:nom, :image, :description, :prix, :est_dispo, NOW(), :id_utilisateur, :id_categorie)';

        $statement = $this->db->prepare($sql);
        $statement->execute([
            'nom' => $data['nom'],
            'image' => $data['image'] ?? null,
            'description' => $data['description'] ?? null,
            'prix' => $data['prix'] ?? 0,
            'est_dispo' => 1,
            'id_utilisateur' => $data['id_utilisateur'],
            'id_categorie' => $data['id_categorie'],
        ]);

        return (int)$this->db->lastInsertId();
    }
}
