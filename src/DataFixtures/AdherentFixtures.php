<?php

namespace App\DataFixtures;

use App\Entity\Adherent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AdherentFixtures extends Fixture
{
    public const REF_PREFIX = 'adherent_';
    public const COUNT = 8;

    public function load(ObjectManager $manager): void
    {
        $adherents = [
            ['nom' => 'Dupont', 'prenom' => 'Alice', 'naissance' => '1997-05-12'],
            ['nom' => 'Martin', 'prenom' => 'Lucas', 'naissance' => '1989-09-03'],
            ['nom' => 'Bernard', 'prenom' => 'Emma', 'naissance' => '2001-01-22'],
            ['nom' => 'Robert', 'prenom' => 'Noah', 'naissance' => '1995-11-14'],
            ['nom' => 'Richard', 'prenom' => 'Chloe', 'naissance' => '1992-07-07'],
            ['nom' => 'Petit', 'prenom' => 'Hugo', 'naissance' => '1987-12-02'],
            ['nom' => 'Durand', 'prenom' => 'Jade', 'naissance' => '1999-03-28'],
            ['nom' => 'Moreau', 'prenom' => 'Lina', 'naissance' => '2003-10-16'],
        ];

        foreach ($adherents as $index => $data) {
            $adherent = new Adherent();
            $adherent->setNom($data['nom']);
            $adherent->setPrenom($data['prenom']);
            $adherent->setDateNaiss(new \DateTime($data['naissance']));
            $adherent->setDateAdhesion(new \DateTime(sprintf('-%d months', $index + 1)));
            $adherent->setEmail(strtolower($data['prenom'] . '.' . $data['nom']) . '@mail.local');
            $adherent->setAdressePostale(sprintf('%d Rue de la Lecture, 59000 Lille', 10 + $index));
            $adherent->setNumTel(sprintf('06000000%02d', $index));
            $adherent->setPhoto(sprintf('https://picsum.photos/seed/adherent-%d/160/160', $index + 1));
            $adherent->setEstActif($index !== 6);

            $manager->persist($adherent);
            $this->addReference(self::REF_PREFIX . $index, $adherent);
        }

        $manager->flush();
    }
}
