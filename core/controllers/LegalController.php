<?php
declare(strict_types=1);

final class LegalController extends Controller
{
    public function conditions(): void
    {
        $this->view('legal/conditions', [
            'pageTitle' => "Conditions d'utilisation",
        ]);
    }

    public function droits(): void
    {
        $this->view('legal/droits', [
            'pageTitle' => 'Droits et responsabilités',
        ]);
    }
}
