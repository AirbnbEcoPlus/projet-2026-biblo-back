<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class GoogleBooksController extends AbstractController
{
    #[Route('/google-books/search', name: 'admin_google_books_search', methods: ['POST'])]
    public function searchGoogleBooks(Request $request): JsonResponse
    {
        $isbn = trim($request->request->get('isbn', ''));

        if (empty($isbn)) {
            return new JsonResponse(['error' => 'ISBN requis'], 400);
        }

        try {
            $url = sprintf('https://www.googleapis.com/books/v1/volumes?q=isbn:%s', urlencode($isbn));
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                return new JsonResponse(['error' => 'Impossible de contacter Google Books'], 500);
            }

            $data = json_decode($response, true);

            if (!isset($data['items']) || empty($data['items'])) {
                return new JsonResponse(['error' => 'Aucun livre trouvé avec cet ISBN'], 404);
            }

            $book = $data['items'][0]['volumeInfo'];
            $publishedDate = $book['publishedDate'] ?? '';
            $normalizedDate = '';

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishedDate) === 1) {
                $normalizedDate = $publishedDate;
            } elseif (preg_match('/^\d{4}-\d{2}$/', $publishedDate) === 1) {
                $normalizedDate = $publishedDate . '-01';
            } elseif (preg_match('/^\d{4}$/', $publishedDate) === 1) {
                $normalizedDate = $publishedDate . '-01-01';
            }
            
            $result = [
                'titre' => $book['title'] ?? '',
                'description' => $book['description'] ?? '',
                'dateSortie' => $normalizedDate,
            ];

            return new JsonResponse($result);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la recherche: ' . $e->getMessage()], 500);
        }
    }
}
