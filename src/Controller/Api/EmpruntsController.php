<?php


namespace App\Controller\Api;


use App\Entity\Emprunt;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/api/emprunts')]
class EmpruntsController extends AbstractController
{
    #[Route('/me', name: 'api_emprunts_me', methods: ['GET'])]
    public function me(
        EntityManagerInterface $em
    ): JsonResponse {


        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], 401);
        }

        $emprunts = $em->getRepository(Emprunt::class)->findBy(['adherent' => $user->getAdherent()]);

        return $this->json($emprunts, 200, [], ['groups' => 'emprunt:read']);

    }
}
