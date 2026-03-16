<?php


namespace App\Controller\Api;


use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/api/adherents')]
class AdherentsController extends AbstractController
{
    #[Route('', name: 'api_adherents_me_get', methods: ['GET'])]
    public function view(): JsonResponse {


        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], 401);
        }

        $adherent = $user->getAdherent();

        if (!$adherent) {
            return $this->json(['message' => 'No adherent linked to this user'], 404);
        }

        return $this->json($adherent, 200, [], ['groups' => 'adherent:read']);

    }

    #[Route('', name: 'api_adherents_me_update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $em): JsonResponse {


        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], 401);
        }

        $adherent = $user->getAdherent();

        if (!$adherent) {
            return $this->json(['message' => 'No adherent linked to this user'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid JSON body'], 400);
        }

        $adherent->setNom(isset($data['nom']) ? trim((string) $data['nom']) : $adherent->getNom());
        $adherent->setPrenom(isset($data['prenom']) ? trim((string) $data['prenom']) : $adherent->getPrenom());
        $adherent->setEmail(isset($data['email']) ? trim((string) $data['email']) : $adherent->getEmail());
        $adherent->setAdressePostale(isset($data['adressePostale']) ? trim((string) $data['adressePostale']) : $adherent->getAdressePostale());
        $adherent->setNumTel(isset($data['numTel']) ? trim((string) $data['numTel']) : $adherent->getNumTel());
        $adherent->setPhoto(isset($data['photo']) ? trim((string) $data['photo']) : $adherent->getPhoto());

        if (isset($data['dateNaiss'])) {
            $dateNaiss = \DateTime::createFromFormat('Y-m-d', (string) $data['dateNaiss']);
            if (!$dateNaiss) {
                return $this->json(['message' => 'dateNaiss format: YYYY-MM-DD'], 400);
            }
            $adherent->setDateNaiss($dateNaiss);
        }

        $em->flush();

        return $this->json($adherent, 200, [], ['groups' => 'adherent:read']);

    }
}
