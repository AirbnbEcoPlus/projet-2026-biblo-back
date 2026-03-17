<?php

namespace App\Controller\Api;

use App\Entity\Reservation;
use App\Repository\AdherentRepository;
use App\Repository\ReservationRepository;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/reservations')]
class ReservationController extends AbstractController
{
    #[Route('', name: 'api_reservations', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): JsonResponse
    {
    
         /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        if (!$user->getAdherent()) {
            return $this->json(['error' => 'Aucun adhérent lié'], 400);
        }
        
        if ($this->isGranted('ROLE_ADMIN')) {
            $reservations = $reservationRepository->findAll();
        } else {
            $reservations = $reservationRepository->findBy([
                'adherent' => $user->getAdherent()
            ]);
        }

        return $this->json($reservations, 200, [], ['groups' => 'reservation:read']);
    }

    #[Route('/{id}', name: 'api_reservations_show', methods: ['GET'])]
    public function show(Reservation $reservation): JsonResponse
{
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user || $reservation->getAdherent() !== $user->getAdherent()) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        return $this->json($reservation, 200, [], ['groups' => 'reservation:read']);
}

    #[Route('', name: 'api_reservation_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, AdherentRepository $adherentRepo, LivreRepository $livreRepo, ReservationRepository $reservationRepo): JsonResponse {

    

        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $adherent = $user->getAdherent();

        if (!$adherent) {
            return $this->json(['error' => 'Aucun adhérent lié'], 400);
        }
        $data = json_decode($request->getContent(), true);
        $livre = $livreRepo->find($data['livre']);

        $reservationExistante = $reservationRepo->findOneBy(['livre' => $livre]);

        if ($reservationExistante) {
            return $this->json(['error' => 'Ce livre est déjà réservé'], 409);
        }

        $reservation = new Reservation();
        $reservation->setAdherent($adherent);
        $reservation->setLivre($livre);
        $reservation->setDateResa(new \DateTime());

        $em->persist($reservation);
        $em->flush();

        return $this->json($reservation, 201, [], ['groups' => 'reservation:read']);
}

}