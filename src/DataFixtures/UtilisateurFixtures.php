<?php

namespace App\DataFixtures;

use App\Entity\Adherent;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurFixtures extends Fixture implements DependentFixtureInterface
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
            ]
        ];

        foreach ($users as $data) {
            $user = new Utilisateur();
            $user->setEmail($data['email']);
            $user->setNom($data['nom']);
            $user->setPrenom($data['prenom']);
            $user->setRoles($data['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, '1234'));

            $manager->persist($user);
        }

        for ($index = 0; $index < AdherentFixtures::COUNT; $index++) {
            /** @var Adherent $adherent */
            $adherent = $this->getReference(AdherentFixtures::REF_PREFIX . $index, Adherent::class);

            if (!$adherent->getEmail()) {
                continue;
            }

            $user = new Utilisateur();
            $user->setEmail($adherent->getEmail());
            $user->setNom($adherent->getNom() ?? 'Adherent');
            $user->setPrenom($adherent->getPrenom() ?? 'Utilisateur');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, '1234'));
            $user->setAdherent($adherent);
            $adherent->setUtilisateur($user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AdherentFixtures::class,
        ];
    }
}
