<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategorieRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'api_articles', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): JsonResponse
    {
        $articles = $articleRepository->findAll();

        return $this->json($articles, 200, [], ['groups' => 'article:read']);
    }

    #[Route('/articles/{id}', name: 'api_articles_show', methods: ['GET'])]
    public function show(Article $article): JsonResponse
    {
        return $this->json($article, 200, [], ['groups' => 'article:read']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/articles', name: 'api_articles_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, CategorieRepository $categorieRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new Article();
        $article->setTitre($data['titre']);
        $article->setContenu($data['contenu'] ?? null);
        $article->setPublie($data['publie'] ?? false);
        $article->setDateCreation(new \DateTime());
        $article->setNumeroOrdre($data['numero_ordre'] ?? 0);

        if (isset($data['categorie_id'])) {
            $categorie = $categorieRepository->find($data['categorie_id']);
            if ($categorie) {
                $article->setCategorie($categorie);
            }
        }

        $em->persist($article);
        $em->flush();

        return $this->json($article, 201, [], ['groups' => 'article:read']);
    }
}
