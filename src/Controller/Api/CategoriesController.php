<?php

namespace App\Controller\Api;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories')]
class Categories extends AbstractController
{
	#[Route('', name: 'api_categories_index', methods: ['GET'])]
	public function index(CategorieRepository $categorieRepository): JsonResponse
	{
		$categories = $categorieRepository->findAll();

		return $this->json($categories, 200, [], ['groups' => ['categorie:read']]);
	}

	#[Route('/{id}', name: 'api_categories_show', methods: ['GET'])]
	public function show(int $id, CategorieRepository $categorieRepository): JsonResponse
	{
		$categorie = $categorieRepository->find($id);

		if (!$categorie) {
			return $this->json(['message' => 'Categorie introuvable'], 404);
		}

		return $this->json($categorie, 200, [], ['groups' => ['categorie:read']]);
	}

	#[Route('', name: 'api_categories_create', methods: ['POST'])]
	public function create(Request $request, EntityManagerInterface $em): JsonResponse
	{
		$data = json_decode($request->getContent(), true);

		if (!is_array($data)) {
			return $this->json(['message' => 'JSON invalide'], 400);
		}

		if (!isset($data['idCat'], $data['nom'], $data['description']) || $data['nom'] === '' || $data['description'] === '') {
			return $this->json(['message' => 'Champs obligatoires manquants (idCat, nom, description)'], 400);
		}

		$categorie = new Categorie();
		$categorie->setIdCat($data['idCat']);
		$categorie->setNom($data['nom']);
		$categorie->setDescription($data['description']);

		$em->persist($categorie);
		$em->flush();

		return $this->json([
			'message' => 'Categorie créée avec succès',
			'categorie' => $categorie,
		], 201, [], ['groups' => ['categorie:read']]);
	}

	#[Route('/{id}', name: 'api_categories_update', methods: ['PUT'])]
	public function update(int $id, Request $request, CategorieRepository $categorieRepository, EntityManagerInterface $em): JsonResponse
	{
		$categorie = $categorieRepository->find($id);

		if (!$categorie) {
			return $this->json(['message' => 'Categorie introuvable'], 404);
		}

		$data = json_decode($request->getContent(), true);

		if (!is_array($data)) {
			return $this->json(['message' => 'JSON invalide'], 400);
		}

		if (!isset($data['idCat'], $data['nom'], $data['description']) || $data['nom'] === '' || $data['description'] === '') {
			return $this->json(['message' => 'Champs obligatoires manquants (idCat, nom, description)'], 400);
		}

		$categorie->setIdCat((int) $data['idCat']);
		$categorie->setNom((string) $data['nom']);
		$categorie->setDescription((string) $data['description']);

		$em->flush();

		return $this->json([
			'message' => 'Categorie mise a jour avec succes',
			'categorie' => $categorie,
		], 200, [], ['groups' => ['categorie:read']]);
	}

	#[Route('/{id}', name: 'api_categories_delete', methods: ['DELETE'])]
	public function delete(int $id, CategorieRepository $categorieRepository, EntityManagerInterface $em): JsonResponse
	{
		$categorie = $categorieRepository->find($id);

		if (!$categorie) {
			return $this->json(['message' => 'Categorie introuvable'], 404);
		}

		$em->remove($categorie);
		$em->flush();

		return $this->json(['message' => 'Categorie supprimee avec succes']);
	}
}
