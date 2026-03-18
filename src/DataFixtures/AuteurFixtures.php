<?php

namespace App\DataFixtures;

use App\Entity\Auteur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AuteurFixtures extends Fixture
{
    public const REF_PREFIX = 'auteur_';

    public function load(ObjectManager $manager): void
    {
        $auteurs = [
            ['nom' => 'Orwell', 'prenom' => 'George', 'nationalite' => 'Britannique', 'dateNaissance' => '1903-06-25'],
            ['nom' => 'Asimov', 'prenom' => 'Isaac', 'nationalite' => 'Americain', 'dateNaissance' => '1920-01-02'],
            ['nom' => 'Murakami', 'prenom' => 'Haruki', 'nationalite' => 'Japonais', 'dateNaissance' => '1949-01-12'],
            ['nom' => 'Liu', 'prenom' => 'Cixin', 'nationalite' => 'Chinois', 'dateNaissance' => '1963-06-23'],
            ['nom' => 'Carrisi', 'prenom' => 'Donato', 'nationalite' => 'Italien', 'dateNaissance' => '1973-03-25'],
            ['nom' => 'Le Tellier', 'prenom' => 'Herve', 'nationalite' => 'Francais', 'dateNaissance' => '1957-04-21'],
            ['nom' => 'Foenkinos', 'prenom' => 'David', 'nationalite' => 'Francais', 'dateNaissance' => '1974-10-28'],
            ['nom' => 'Saramago', 'prenom' => 'Jose', 'nationalite' => 'Portugais', 'dateNaissance' => '1922-11-16'],
        ];

        foreach ($auteurs as $index => $data) {
            $auteur = new Auteur();
            $auteur->setNom($data['nom']);
            $auteur->setPrenom($data['prenom']);
            $auteur->setNationalite($data['nationalite']);
            $auteur->setDateNaissance(new \DateTime($data['dateNaissance']));
            $auteur->setDescription('Auteur de reference dans son genre.');
            $auteur->setPhoto(sprintf('https://picsum.photos/seed/auteur-%d/200/300', $index + 1));

            $manager->persist($auteur);
            $this->addReference(self::REF_PREFIX . $index, $auteur);
        }

        $manager->flush();
    }
}
