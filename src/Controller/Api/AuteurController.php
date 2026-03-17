<?php

namespace App\Controller\Api;

use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/auteurs')]
class AuteurController extends AbstractController
{
    #[Route('', name: 'api_auteurs', methods: ['GET'])]
    public function index(AuteurRepository $auteurRepository): JsonResponse
    {
        $auteurs = $auteurRepository->findAll();

        return $this->json($auteurs, 200, [], ['groups' => 'auteur:read']);
    }

    #[Route('/{id}', name: 'api_auteurs_show', methods: ['GET'])]
    public function show(Auteur $auteur): JsonResponse
    {
        return $this->json($auteur, 200, [], ['groups' => 'auteur:read']);
    }

    #[Route('', name: 'api_auteurs_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, AuteurRepository $auteurRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nom'], $data['prenom'], $data['date_naissance'])) {
            return $this->json(['message' => 'Champs incomplet'], 400);
        }
        if ($auteurRepository->findOneBy(['nom' => $data['nom'], 'prenom' => $data['prenom']])) {
            return $this->json(['message' => 'L\'Auteur  exists déjà'], 409);
        }

        $auteur = new Auteur();
        $auteur->setNom($data['nom']);
        $auteur->setPrenom($data['prenom']);
        $auteur->setDateNaissance(new \DateTime($data['date_naissance']));


        $em->persist($auteur);
        $em->flush();

        return $this->json($auteur, 201, [], ['groups' => 'auteur:read']);
    }
}