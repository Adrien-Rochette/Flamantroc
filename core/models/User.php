<?php
declare(strict_types=1);

final class User extends Model
{
    public function all(): array
    {
        $sql = 'SELECT id_utilisateur, pseudo, mail, description, image_profil, ville, region, poisson, role,
                       est_banni, raison_ban, date_fin_ban
                FROM utilisateur
                ORDER BY pseudo ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function getUsersCountByRegion(): array
    {
        $sql = 'SELECT TRIM(region) AS region, COUNT(*) AS total
                FROM utilisateur
                WHERE region IS NOT NULL
                  AND TRIM(region) <> \'\'
                GROUP BY TRIM(region)
                ORDER BY region ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute();

        $counts = [];
        foreach ($statement->fetchAll() as $row) {
            $counts[(string)$row['region']] = (int)$row['total'];
        }

        return $counts;
    }

    public function getRegistrationsByDay(int $days = 30): array
    {
        $days = max(1, min($days, 365));
        $endDate = new DateTimeImmutable('today');
        $startDate = $endDate->modify('-' . ($days - 1) . ' days');
        $series = [];

        for ($date = $startDate; $date <= $endDate; $date = $date->modify('+1 day')) {
            $key = $date->format('Y-m-d');
            $series[$key] = [
                'date' => $key,
                'label' => $date->format('d/m'),
                'total' => 0,
            ];
        }

        $sql = 'SELECT DATE(date_inscription) AS inscription_day, COUNT(*) AS total
                FROM utilisateur
                WHERE date_inscription >= :start_date
                GROUP BY DATE(date_inscription)
                ORDER BY inscription_day ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'start_date' => $startDate->format('Y-m-d 00:00:00'),
        ]);

        foreach ($statement->fetchAll() as $row) {
            $day = (string)$row['inscription_day'];
            if (isset($series[$day])) {
                $series[$day]['total'] = (int)$row['total'];
            }
        }

        return array_values($series);
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT id_utilisateur, pseudo, mail, description, image_profil, ville, region, poisson, role,
                       est_banni, raison_ban, date_fin_ban
                FROM utilisateur
                WHERE id_utilisateur = :id
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function ban(int $id, string $reason, ?string $endDate): void
    {
        $sql = 'UPDATE utilisateur
                SET est_banni = 1,
                    raison_ban = :raison_ban,
                    date_fin_ban = :date_fin_ban
                WHERE id_utilisateur = :id';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id' => $id,
            'raison_ban' => $reason,
            'date_fin_ban' => $endDate,
        ]);
    }

    public function unban(int $id): void
    {
        $sql = 'UPDATE utilisateur
                SET est_banni = 0,
                    raison_ban = NULL,
                    date_fin_ban = NULL
                WHERE id_utilisateur = :id';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
    }

    public function clearExpiredBans(): void
    {
        $sql = 'UPDATE utilisateur
                SET est_banni = 0,
                    raison_ban = NULL,
                    date_fin_ban = NULL
                WHERE est_banni = 1
                  AND date_fin_ban IS NOT NULL
                  AND date_fin_ban <= NOW()';
        $statement = $this->db->prepare($sql);
        $statement->execute();
    }

    public function isBanActive(array $user): bool
    {
        if ((int)($user['est_banni'] ?? 0) !== 1) {
            return false;
        }

        $endDate = trim((string)($user['date_fin_ban'] ?? ''));
        if ($endDate === '') {
            return true;
        }

        $endTimestamp = strtotime($endDate);
        return $endTimestamp === false || $endTimestamp > time();
    }

    public function getBanMessage(array $user): string
    {
        $reason = trim((string)($user['raison_ban'] ?? ''));
        $endDate = trim((string)($user['date_fin_ban'] ?? ''));

        $message = 'Ton compte est banni.';
        $message .= ' Raison : ' . ($reason !== '' ? $reason : 'non precisee') . '.';

        if ($endDate !== '') {
            $timestamp = strtotime($endDate);
            if ($timestamp !== false) {
                $message .= ' Fin du bannissement : ' . date('d/m/Y H:i', $timestamp) . '.';
                return $message;
            }
        }

        $message .= ' Fin du bannissement : non definie.';

        return $message;
    }

    public function findByMail(string $mail): ?array
    {
        $sql = 'SELECT * FROM utilisateur WHERE mail = :mail LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['mail' => $mail]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function create(array $data): int
    {
        $hashedPassword = password_hash((string)$data['mot_de_passe'], PASSWORD_DEFAULT);

        $sql = 'INSERT INTO utilisateur (pseudo, mail, mot_de_passe, role, date_inscription)
                VALUES (:pseudo, :mail, :mot_de_passe, :role, NOW())';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'pseudo' => $data['pseudo'],
            'mail' => $data['mail'],
            'mot_de_passe' => $hashedPassword,
            'role' => $data['role'] ?? 'UTILISATEUR',
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateProfileImage(int $id, string $imagePath): void
    {
        $sql = 'UPDATE utilisateur
                SET image_profil = :image_profil
                WHERE id_utilisateur = :id';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id' => $id,
            'image_profil' => $imagePath,
        ]);
    }

    public function updateProfileDetails(int $id, ?string $description, ?string $ville, ?string $region): void
    {
        $sql = 'UPDATE utilisateur
                SET description = :description,
                    ville = :ville,
                    region = :region
                WHERE id_utilisateur = :id';
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'id' => $id,
            'description' => $description,
            'ville' => $ville,
            'region' => $region,
        ]);
    }

    public function verifyCredentials(string $mail, string $plainPassword): ?array
    {
        $user = $this->findByMail($mail);
        if ($user === null) {
            return null;
        }

        if (!password_verify($plainPassword, (string)$user['mot_de_passe'])) {
            return null;
        }

        return $user;
    }
}
