<?php


namespace App\Controller\Api;


use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

    #[Route('', name: 'api_adherents_me_update', methods: ['PATCH', 'POST'])]
    public function update(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        UtilisateurRepository $utilisateurRepository
    ): JsonResponse {


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

        $allowedFields = ['email', 'numTel', 'photo', 'password'];
        foreach (array_keys($data) as $field) {
            if (!in_array($field, $allowedFields, true)) {
                return $this->json([
                    'message' => sprintf('Field "%s" is not allowed. Allowed fields: email, numTel, photo, password', $field),
                ], 400);
            }
        }

        if (array_key_exists('email', $data)) {
            $email = trim((string) $data['email']);
            if ($email === '') {
                return $this->json(['message' => 'Email is required'], 400);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json(['message' => 'Invalid email format'], 400);
            }

            $existing = $utilisateurRepository->findOneBy(['email' => $email]);
            if ($existing !== null && $existing->getId() !== $user->getId()) {
                return $this->json(['message' => 'Email already used'], 409);
            }

            $adherent->setEmail($email);
            $user->setEmail($email);
        }

        if (array_key_exists('numTel', $data)) {
            $adherent->setNumTel(trim((string) $data['numTel']));
        }

        if (array_key_exists('photo', $data)) {
            $adherent->setPhoto(trim((string) $data['photo']));
        }

        if (array_key_exists('password', $data)) {
            $password = (string) $data['password'];
            if (strlen($password) < 4) {
                return $this->json(['message' => 'Password must contain at least 4 characters'], 400);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $password));
        }

        $em->flush();

        return $this->json($adherent, 200, [], ['groups' => 'adherent:read']);

    }
}
