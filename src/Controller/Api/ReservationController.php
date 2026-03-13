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

#[Route('/api')]
class ReservationController extends AbstractController
{
    #[Route('/reservations', name: 'api_reservations', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): JsonResponse
    {
        $reservations = $reservationRepository->findAll();

        return $this->json($reservations, 200, [], ['groups' => 'reservation:read']);
    }

    #[Route('/reservations/{id}', name: 'api_reservations_show', methods: ['GET'])]
    public function show(Reservation $reservation): JsonResponse
    {
        return $this->json($reservation, 200, [], ['groups' => 'reservation:read']);
    }

    #[Route('/reservations', name: 'api_reservation_create', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $em, AdherentRepository $adherentRepo, LivreRepository $livreRepo): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $adherent = $adherentRepo->find($data['adherent']);
    $livre = $livreRepo->find($data['livre']);

    $reservation = new Reservation();
    $reservation->setAdherent($adherent);
    $reservation->setLivre($livre);
    $reservation->setDateResa(new \DateTime());

    $em->persist($reservation);
    $em->flush();
    
    return $this->json($reservation, 201, [], ['groups' => 'reservation:read']);
}

}