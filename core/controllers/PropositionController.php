<?php
declare(strict_types=1);

final class PropositionController extends Controller
{
    public function mesPropositions(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $this->view('propositions/mes_propositions', [
            'pageTitle' => 'Mes propositions',
        ]);
    }

    public function recues(): void
    {
        if (!isset($_SESSION['user']['id'])) {
            $this->redirect('/login');
        }

        $this->view('propositions/recues', [
            'pageTitle' => 'Propositions recues',
        ]);
    }
}
