<?php

namespace App\DataFixtures;

use App\Entity\Adherent;
use App\Entity\Livre;
use App\Entity\Reservation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $reservations = [
            ['adherent' => 1, 'livre' => 7, 'date_resa' => '2026-03-04', 'date_notification' => null],
            ['adherent' => 2, 'livre' => 8, 'date_resa' => '2026-03-06', 'date_notification' => '2026-03-08 09:30:00'],
            ['adherent' => 3, 'livre' => 9, 'date_resa' => '2026-03-07', 'date_notification' => null],
            ['adherent' => 4, 'livre' => 10, 'date_resa' => '2026-03-10', 'date_notification' => null],
        ];

        foreach ($reservations as $data) {
            $reservation = new Reservation();

            /** @var Adherent $adherent */
            $adherent = $this->getReference(AdherentFixtures::REF_PREFIX . $data['adherent'], Adherent::class);
            /** @var Livre $livre */
            $livre = $this->getReference(LivreFixtures::REF_PREFIX . $data['livre'], Livre::class);

            $reservation->setAdherent($adherent);
            $reservation->setLivre($livre);
            $reservation->setDateResa(new \DateTime($data['date_resa']));
            $reservation->setDateNotification($data['date_notification'] ? new \DateTime($data['date_notification']) : null);

            $manager->persist($reservation);
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
