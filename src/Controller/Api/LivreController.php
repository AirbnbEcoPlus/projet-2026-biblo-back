<?php

namespace App\Controller\Api;

use App\Entity\Livre;
use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/livres')]
class LivreController extends AbstractController
{
    #[Route('/', name: 'api_livres', methods: ['GET'])]
    public function index(LivreRepository $livreRepository, Request $request): JsonResponse
    {
        $criteres = [
            'titre'   => $request->query->get('titre'),
            'langue' => $request->query->get('langue'),
            'categorieId' => $request->query->get('categorie'),
            'auteurId' => $request->query->get('auteur'),
            'dateDebut' => $request->query->get('debut'),
            'dateFin' => $request->query->get('fin'),
        ];

        $livres = $livreRepository->findByCriteres($criteres);

        return $this->json($livres, 200, [], ['groups' => 'livre:read']);
    }

    #[Route('/{id}', name: 'api_livres_show', methods: ['GET'])]
    public function show(Livre $livre): JsonResponse
    {
        return $this->json($livre, 200, [], ['groups' => 'livre:read']);
    }
}