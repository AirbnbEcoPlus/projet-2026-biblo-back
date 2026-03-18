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
        $isbn = preg_replace('/[^0-9Xx]/', '', $isbn) ?? '';
        $isbn = strtoupper($isbn);

        if (empty($isbn)) {
            return new JsonResponse(['error' => 'ISBN requis'], 400);
        }

        try {
            $url = sprintf('https://www.googleapis.com/books/v1/volumes?q=isbn:%s', urlencode($isbn));
            $apiKey = (string) ($_ENV['GOOGLE_BOOKS_API_KEY'] ?? $_SERVER['GOOGLE_BOOKS_API_KEY'] ?? '');
            if ($apiKey !== '') {
                $url .= '&key=' . urlencode($apiKey);
            }
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'ignore_errors' => true,
                    'header' => "User-Agent: SAEIUT/1.0\r\n",
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            $statusCode = 0;
            if (isset($http_response_header[0]) && preg_match('#HTTP/\S+\s+(\d{3})#', $http_response_header[0], $matches) === 1) {
                $statusCode = (int) $matches[1];
            }
            
            if ($response === false || $statusCode >= 400) {
                if ($statusCode === 429) {
                    return new JsonResponse(['error' => 'Quota Google Books temporairement depasse. Reessayez dans quelques minutes.'], 429);
                }

                if ($statusCode >= 500) {
                    return new JsonResponse(['error' => 'Service Google Books indisponible pour le moment.'], 503);
                }

                if ($statusCode >= 400) {
                    return new JsonResponse(['error' => sprintf('Erreur Google Books (%d).', $statusCode)], 502);
                }

                return new JsonResponse(['error' => 'Impossible de contacter Google Books'], 503);
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
