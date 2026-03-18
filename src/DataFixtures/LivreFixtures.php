<?php

namespace App\DataFixtures;

use App\Entity\Livre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LivreFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $livres = [
            [
                'titre' => 'La clé de Salomon',
                'date_sortie' => '2014-04-30',
                'langue' => 'fr',
                'photo_couverture' => 'https://picsum.photos/id/1/200/300',
            ],
            [
                'titre' => 'Le Chuchoteur',
                'date_sortie' => '2009-03-04',
                'langue' => 'it',
                'photo_couverture' => 'https://picsum.photos/id/10/200/300',
            ],
            [
                'titre' => 'The Midnight Library',
                'date_sortie' => '2020-08-13',
                'langue' => 'en',
                'photo_couverture' => 'https://picsum.photos/id/11/200/300',
            ],
            [
                'titre' => 'L\'Anomalie',
                'date_sortie' => '2020-08-20',
                'langue' => 'fr',
                'photo_couverture' => 'https://picsum.photos/id/12/200/300',
            ],
            [
                'titre' => 'Foundation',
                'date_sortie' => '1951-06-01',
                'langue' => 'en',
                'photo_couverture' => 'https://picsum.photos/id/13/200/300',
            ],
            [
                'titre' => 'La tresse',
                'date_sortie' => '2017-05-10',
                'langue' => 'fr',
                'photo_couverture' => 'https://picsum.photos/id/14/200/300',
            ],
            [
                'titre' => 'El amor en los tiempos del cólera',
                'date_sortie' => '1985-01-01',
                'langue' => 'es',
                'photo_couverture' => 'https://picsum.photos/id/15/200/300',
            ],
            [
                'titre' => '1984',
                'date_sortie' => '1949-06-08',
                'langue' => 'en',
                'photo_couverture' => 'https://picsum.photos/id/16/200/300',
            ],
            [
                'titre' => 'L\'ombre du vent',
                'date_sortie' => '2001-04-01',
                'langue' => 'es',
                'photo_couverture' => 'https://picsum.photos/id/17/200/300',
            ],
            [
                'titre' => 'Kafka sur le rivage',
                'date_sortie' => '2002-09-12',
                'langue' => 'ja',
                'photo_couverture' => 'https://picsum.photos/id/18/200/300',
            ],
            [
                'titre' => 'Le problème à trois corps',
                'date_sortie' => '2008-01-01',
                'langue' => 'zh',
                'photo_couverture' => 'https://picsum.photos/id/19/200/300',
            ]
        ];

        foreach ($livres as $data) {
            $livre = new Livre();
            $livre->setTitre($data['titre']);
            $livre->setDateSortie(new \DateTime($data['date_sortie']));
            $livre->setLangue($data['langue']);
            $livre->setPhotoCouverture($data['photo_couverture']);

            $manager->persist($livre);
        }

        $manager->flush();
    }
}
