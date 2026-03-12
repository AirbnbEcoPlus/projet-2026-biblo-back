<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'email' => 'responsable.bib@biblio.com',
                'nom' => 'Responsable',
                'prenom' => 'Bib',
                'roles' => ['ROLE_ADMIN'],
            ],
            [
                'email' => 'biblio@biblio.com',
                'nom' => 'Bibliothecaire',
                'prenom' => 'Biblio',
                'roles' => ['ROLE_BIBLIO'],
            ],
            [
                'email' => 'adherent@biblio.com',
                'nom' => 'Adherent',
                'prenom' => 'User',
                'roles' => ['ROLE_USER'],
            ]
        ];

        foreach ($users as $data) {
            $user = new Utilisateur();
            $user->setEmail($data['email']);
            $user->setNom($data['nom']);
            $user->setPrenom($data['prenom']);
            $user->setRoles($data['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

            $manager->persist($user);
        }

        $manager->flush();
    }
}
