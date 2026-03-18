<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixtures extends Fixture
{
    public const REF_PREFIX = 'categorie_';

    public function load(ObjectManager $manager): void
    {
        $categories = [
            ['nom' => 'Roman', 'description' => 'Romans contemporains et classiques.'],
            ['nom' => 'Science-fiction', 'description' => 'Recits d\'anticipation et univers futuristes.'],
            ['nom' => 'Policier', 'description' => 'Enquetes, thrillers et mysteres.'],
            ['nom' => 'Litterature etrangere', 'description' => 'Oeuvres traduites et auteurs internationaux.'],
            ['nom' => 'Jeunesse', 'description' => 'Livres accessibles a un jeune public.'],
            ['nom' => 'Historique', 'description' => 'Romans et recits inspires de l\'histoire.'],
            ['nom' => 'Developpement personnel', 'description' => 'Livres pratiques autour du bien-etre.'],
        ];

        foreach ($categories as $index => $data) {
            $categorie = new Categorie();
            $categorie->setNom($data['nom']);
            $categorie->setDescription($data['description']);

            $manager->persist($categorie);
            $this->addReference(self::REF_PREFIX . $index, $categorie);
        }

        $manager->flush();
    }
}
