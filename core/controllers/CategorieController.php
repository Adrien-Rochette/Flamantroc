<?php
declare(strict_types=1);

final class CategorieController extends Controller
{
    public function liste(): void
    {
        $categorieModel = new Categorie();

        $this->view('categories/liste', [
            'pageTitle' => 'Categories',
            'categories' => $categorieModel->all(),
        ]);
    }
}
