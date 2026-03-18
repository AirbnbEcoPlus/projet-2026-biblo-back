<?php

namespace App\DataFixtures;

use App\Entity\Adherent;
use App\Entity\Emprunt;
use App\Entity\Livre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EmpruntFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $emprunts = [
            ['adherent' => 0, 'livre' => 0, 'emprunt' => '2026-02-01', 'retour_prevue' => '2026-02-22', 'retour_effectue' => '2026-02-20'],
            ['adherent' => 1, 'livre' => 1, 'emprunt' => '2026-02-10', 'retour_prevue' => '2026-03-03', 'retour_effectue' => null],
            ['adherent' => 2, 'livre' => 2, 'emprunt' => '2026-01-15', 'retour_prevue' => '2026-02-05', 'retour_effectue' => '2026-02-04'],
            ['adherent' => 3, 'livre' => 3, 'emprunt' => '2026-03-01', 'retour_prevue' => '2026-03-22', 'retour_effectue' => null],
            ['adherent' => 4, 'livre' => 4, 'emprunt' => '2026-02-18', 'retour_prevue' => '2026-03-11', 'retour_effectue' => null],
            ['adherent' => 5, 'livre' => 5, 'emprunt' => '2026-01-22', 'retour_prevue' => '2026-02-12', 'retour_effectue' => '2026-02-10'],
            ['adherent' => 0, 'livre' => 6, 'emprunt' => '2026-03-05', 'retour_prevue' => '2026-03-26', 'retour_effectue' => null],
        ];

        foreach ($emprunts as $data) {
            $emprunt = new Emprunt();

            /** @var Adherent $adherent */
            $adherent = $this->getReference(AdherentFixtures::REF_PREFIX . $data['adherent'], Adherent::class);
            /** @var Livre $livre */
            $livre = $this->getReference(LivreFixtures::REF_PREFIX . $data['livre'], Livre::class);

            $emprunt->setAdherent($adherent);
            $emprunt->setLivre($livre);
            $emprunt->setDateEmprunt(new \DateTime($data['emprunt']));
            $emprunt->setDateRetourPrevue(new \DateTime($data['retour_prevue']));
            $emprunt->setDateRetourEffectue($data['retour_effectue'] ? new \DateTime($data['retour_effectue']) : null);

            $manager->persist($emprunt);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AdherentFixtures::class,
            LivreFixtures::class,
        ];
    }
}
