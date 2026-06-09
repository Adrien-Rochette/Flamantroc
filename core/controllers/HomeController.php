<?php
declare(strict_types=1);

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/dashboard', [
            'pageTitle' => 'Tableau de bord',
        ]);
    }
}
