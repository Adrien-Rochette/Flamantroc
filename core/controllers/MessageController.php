<?php
declare(strict_types=1);

final class MessageController extends Controller
{
    public function index(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $messageModel = new Message();

        $this->view('messages/index', [
            'pageTitle' => 'Messages',
            'conversations' => $messageModel->conversationsForUser((int)$_SESSION['user']['id']),
            'activeConversation' => null,
            'messages' => [],
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function conversation(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $propositionId = (int)($_GET['id'] ?? 0);
        if ($propositionId <= 0) {
            $_SESSION['error'] = 'Conversation introuvable.';
            $this->redirect('/messages');
        }

        $success = $_SESSION['success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        $currentUserId = (int)$_SESSION['user']['id'];
        $propositionModel = new Proposition();
        $activeConversation = $propositionModel->findByIdForUser($propositionId, $currentUserId);

        if ($activeConversation === null) {
            $_SESSION['error'] = 'Conversation introuvable.';
            $this->redirect('/messages');
        }

        $messageModel = new Message();

        $this->view('messages/index', [
            'pageTitle' => 'Messages',
            'conversations' => $messageModel->conversationsForUser($currentUserId),
            'activeConversation' => $activeConversation,
            'messages' => $messageModel->findByProposition($propositionId),
            'success' => $success,
            'error' => $error,
        ]);
    }

    public function send(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $propositionId = (int)($_POST['id_proposition'] ?? 0);
        $contenu = trim((string)($_POST['contenu'] ?? ''));

        if ($propositionId <= 0) {
            $_SESSION['error'] = 'Conversation introuvable.';
            $this->redirect('/messages');
        }

        if ($contenu === '') {
            $_SESSION['error'] = 'Ecris un message avant de l envoyer.';
            $this->redirect('/messages/conversation?id=' . $propositionId);
        }

        if (strlen($contenu) > 2000) {
            $_SESSION['error'] = 'Le message doit faire 2000 caracteres maximum.';
            $this->redirect('/messages/conversation?id=' . $propositionId);
        }

        $currentUserId = (int)$_SESSION['user']['id'];
        $propositionModel = new Proposition();
        $conversation = $propositionModel->findByIdForUser($propositionId, $currentUserId);

        if ($conversation === null) {
            $_SESSION['error'] = 'Conversation introuvable.';
            $this->redirect('/messages');
        }

        $messageModel = new Message();
        $messageModel->create([
            'contenu' => $contenu,
            'id_proposition' => $propositionId,
            'id_utilisateur' => $currentUserId,
        ]);

        $this->redirect('/messages/conversation?id=' . $propositionId);
    }
}
