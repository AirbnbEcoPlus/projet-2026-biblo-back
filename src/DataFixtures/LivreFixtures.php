<?php

namespace App\DataFixtures;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Livre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LivreFixtures extends Fixture implements DependentFixtureInterface
{
    public const REF_PREFIX = 'livre_';

    public function load(ObjectManager $manager): void
    {
        $livres = [
            [
                'titre' => 'La clé de Salomon',
                'date_sortie' => '2014-04-30',
                'langue' => 'fr',
                'photo_couverture' => 'https://picsum.photos/id/1/200/300',
                'description' => 'Un thriller sur les traces de secrets historiques.',
                'auteurs' => [4],
                'categories' => [2, 0],
            ],
            [
                'titre' => 'Le Chuchoteur',
                'date_sortie' => '2009-03-04',
                'langue' => 'it',
                'photo_couverture' => 'https://picsum.photos/id/10/200/300',
                'description' => 'Un thriller psychologique a haute tension.',
                'auteurs' => [4],
                'categories' => [2],
            ],
            [
                'titre' => 'The Midnight Library',
                'date_sortie' => '2020-08-13',
                'langue' => 'en',
                'photo_couverture' => 'https://picsum.photos/id/11/200/300',
                'description' => 'Une bibliotheque magique qui ouvre sur des vies alternatives.',
                'auteurs' => [6],
                'categories' => [0, 3],
            ],
            [
                'titre' => 'L\'Anomalie',
                'date_sortie' => '2020-08-20',
                'langue' => 'fr',
                'photo_couverture' => 'https://picsum.photos/id/12/200/300',
                'description' => 'Roman choral autour d\'un evenement impossible.',
                'auteurs' => [5],
                'categories' => [0, 1],
            ],
            [
                'titre' => 'Foundation',
                'date_sortie' => '1951-06-01',
                'langue' => 'en',
                'photo_couverture' => 'https://picsum.photos/id/13/200/300',
                'description' => 'Classique de la science-fiction et de la psychohistoire.',
                'auteurs' => [1],
                'categories' => [1],
            ],
            [
                'titre' => 'La tresse',
                'date_sortie' => '2017-05-10',
                'langue' => 'fr',
                'photo_couverture' => 'https://picsum.photos/id/14/200/300',
                'description' => 'Trois destins feminins relies entre eux.',
                'auteurs' => [6],
                'categories' => [0],
            ],
            [
                'titre' => 'El amor en los tiempos del cólera',
                'date_sortie' => '1985-01-01',
                'langue' => 'es',
                'photo_couverture' => 'https://picsum.photos/id/15/200/300',
                'description' => 'Une histoire d\'amour qui traverse les decennies.',
                'auteurs' => [7],
                'categories' => [0, 3],
            ],
            [
                'titre' => '1984',
                'date_sortie' => '1949-06-08',
                'langue' => 'en',
                'photo_couverture' => 'https://picsum.photos/id/16/200/300',
                'description' => 'Dystopie politique devenue incontournable.',
                'auteurs' => [0],
                'categories' => [1],
            ],
            [
                'titre' => 'L\'ombre du vent',
                'date_sortie' => '2001-04-01',
                'langue' => 'es',
                'photo_couverture' => 'https://picsum.photos/id/17/200/300',
                'description' => 'Mystere litteraire dans la Barcelone du XXe siecle.',
                'auteurs' => [7],
                'categories' => [0, 2, 3],
            ],
            [
                'titre' => 'Kafka sur le rivage',
                'date_sortie' => '2002-09-12',
                'langue' => 'ja',
                'photo_couverture' => 'https://picsum.photos/id/18/200/300',
                'description' => 'Roman onirique entre realite et imaginaire.',
                'auteurs' => [2],
                'categories' => [0, 3],
            ],
            [
                'titre' => 'Le problème à trois corps',
                'date_sortie' => '2008-01-01',
                'langue' => 'zh',
                'photo_couverture' => 'https://picsum.photos/id/19/200/300',
                'description' => 'Premier tome d\'une saga de science-fiction chinoise.',
                'auteurs' => [3],
                'categories' => [1, 3],
            ]
        ];

        foreach ($livres as $index => $data) {
            $livre = new Livre();
            $livre->setTitre($data['titre']);
            $livre->setDateSortie(new \DateTime($data['date_sortie']));
            $livre->setLangue($data['langue']);
            $livre->setPhotoCouverture($data['photo_couverture']);
            $livre->setDescription($data['description']);

            foreach ($data['auteurs'] as $auteurIndex) {
                /** @var Auteur $auteur */
                $auteur = $this->getReference(AuteurFixtures::REF_PREFIX . $auteurIndex, Auteur::class);
                $livre->addAuteur($auteur);
            }

            foreach ($data['categories'] as $categorieIndex) {
                /** @var Categorie $categorie */
                $categorie = $this->getReference(CategorieFixtures::REF_PREFIX . $categorieIndex, Categorie::class);
                $livre->addCategorie($categorie);
            }

            $manager->persist($livre);
            $this->addReference(self::REF_PREFIX . $index, $livre);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AuteurFixtures::class,
            CategorieFixtures::class,
        ];
    }
}
