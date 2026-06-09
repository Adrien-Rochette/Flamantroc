<?php
declare(strict_types=1);

final class Categorie extends Model
{
    public function all(): array
    {
        $sql = 'SELECT * FROM categorie ORDER BY nom ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM categorie WHERE id_categorie = :id LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ?: null;
    }

    public function create(string $nom, bool $estUnService = false, ?int $idCategorieParent = null): int
    {
        $sql = 'INSERT INTO categorie (nom, est_un_service, id_categorie_parent)
                VALUES (:nom, :est_un_service, :id_categorie_parent)';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'nom' => $nom,
            'est_un_service' => $estUnService ? 1 : 0,
            'id_categorie_parent' => $idCategorieParent,
        ]);

        return (int)$this->db->lastInsertId();
    }
}
