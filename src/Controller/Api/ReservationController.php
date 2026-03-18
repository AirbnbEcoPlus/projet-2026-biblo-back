<?php

namespace App\Controller\Api;

use App\Entity\Reservation;
use App\Entity\Utilisateur;
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

        $nbReservationsActives = $reservationRepo->count(['adherent' => $adherent]);
        if ($nbReservationsActives >= 3) {
            return $this->json(['error' => 'Maximum 3 réservations simultanées par adhérent'], 409);
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

    #[Route('/{id}', name: 'api_reservation_delete', methods: ['DELETE'])]
    public function delete(Reservation $reservation, EntityManagerInterface $em): JsonResponse
    {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $canDelete = $this->isGranted('ROLE_ADMIN')
            || ($user->getAdherent() !== null && $reservation->getAdherent() === $user->getAdherent());

        if (!$canDelete) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $em->remove($reservation);
        $em->flush();

        return new JsonResponse(null, 204);
    }

}