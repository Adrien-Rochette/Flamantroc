<?php
declare(strict_types=1);

final class AdminController extends Controller
{
    public function dashboard(): void
    {
        $this->requireAdmin();

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $userModel = new User();
        $userModel->clearExpiredBans();

        $this->view('admin/dashboard', [
            'pageTitle' => 'Administration',
            'users' => $userModel->all(),
            'usersByRegionJson' => json_encode(
                $userModel->getUsersCountByRegion(),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            ),
            'registrationsByDayJson' => json_encode(
                $userModel->getRegistrationsByDay(),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            ),
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function banUser(): void
    {
        $this->requireAdmin();

        $userId = (int)($_POST['id_utilisateur'] ?? 0);
        $reason = trim((string)($_POST['raison_ban'] ?? ''));
        $endDateInput = trim((string)($_POST['date_fin_ban'] ?? ''));

        if ($userId <= 0) {
            $this->redirectWithMessage('error', 'Utilisateur introuvable.');
        }

        if ($userId === (int)$_SESSION['user']['id']) {
            $this->redirectWithMessage('error', 'Tu ne peux pas bannir ton propre compte.');
        }

        if ($reason === '') {
            $this->redirectWithMessage('error', 'Indique une raison de bannissement.');
        }

        if (strlen($reason) > 1000) {
            $this->redirectWithMessage('error', 'La raison doit faire 1000 caracteres maximum.');
        }

        $endDate = $this->normalizeBanEndDate($endDateInput);
        if ($endDateInput !== '' && $endDate === null) {
            $this->redirectWithMessage('error', 'Date de fin de bannissement invalide.');
        }

        if ($endDate !== null && strtotime($endDate) <= time()) {
            $this->redirectWithMessage('error', 'La date de fin doit etre dans le futur.');
        }

        $userModel = new User();
        if ($userModel->findById($userId) === null) {
            $this->redirectWithMessage('error', 'Utilisateur introuvable.');
        }

        $userModel->ban($userId, $reason, $endDate);
        $this->redirectWithMessage('success', 'Utilisateur banni.');
    }

    public function unbanUser(): void
    {
        $this->requireAdmin();

        $userId = (int)($_POST['id_utilisateur'] ?? 0);
        if ($userId <= 0) {
            $this->redirectWithMessage('error', 'Utilisateur introuvable.');
        }

        $userModel = new User();
        if ($userModel->findById($userId) === null) {
            $this->redirectWithMessage('error', 'Utilisateur introuvable.');
        }

        $userModel->unban($userId);
        $this->redirectWithMessage('success', 'Bannissement leve.');
    }

    private function requireAdmin(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        if (($_SESSION['user']['role'] ?? '') !== 'ADMIN') {
            $this->redirect('/');
        }
    }

    private function normalizeBanEndDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value)
            ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value)
            ?: DateTimeImmutable::createFromFormat('Y-m-d H:i', $value);

        if (!$date instanceof DateTimeImmutable) {
            return null;
        }

        return $date->format('Y-m-d H:i:s');
    }

    private function redirectWithMessage(string $type, string $message): void
    {
        $_SESSION[$type] = $message;
        $this->redirect('/admin');
    }
}
