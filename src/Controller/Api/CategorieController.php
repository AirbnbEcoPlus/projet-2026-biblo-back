<?php

namespace App\Controller\Api;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class CategorieController extends AbstractController
{
    #[Route('/categories', name: 'api_categories', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): JsonResponse
    {
        $categories = $categorieRepository->findAll();

        return $this->json($categories, 200, [], ['groups' => 'categorie:read']);
    }

    #[Route('/categories/{id}', name: 'api_categories_show', methods: ['GET'])]
    public function show(Categorie $categorie): JsonResponse
    {
        return $this->json($categorie, 200, [], ['groups' => 'categorie:read']);
    }
}
