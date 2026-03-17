<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Reservation;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function index(): Response
    {
        $today = new \DateTimeImmutable('today');

        $nbCategories = $this->em->getRepository(Categorie::class)->count([]);
        $nbLivres = $this->em->getRepository(Livre::class)->count([]);
        $nbReservations = $this->em->getRepository(Reservation::class)->count([]);
        $nbUtilisateurs = $this->em->getRepository(Utilisateur::class)->count([]);

        $nbEmpruntsEnCours = (int) $this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(Emprunt::class, 'e')
            ->where('e.dateRetour >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        $nbEmpruntsRetard = (int) $this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from(Emprunt::class, 'e')
            ->where('e.dateRetour < :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        $nbAdherentsActifs = (int) $this->em->createQueryBuilder()
            ->select('COUNT(DISTINCT IDENTITY(e.adherent))')
            ->from(Emprunt::class, 'e')
            ->where('e.dateRetour >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        $nbLivresIndisponibles = (int) $this->em->createQueryBuilder()
            ->select('COUNT(DISTINCT IDENTITY(e.livre))')
            ->from(Emprunt::class, 'e')
            ->where('e.dateRetour >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();

        $recentEmprunts = $this->em->createQueryBuilder()
            ->select('e, a, l')
            ->from(Emprunt::class, 'e')
            ->leftJoin('e.adherent', 'a')
            ->leftJoin('e.livre', 'l')
            ->orderBy('e.dateEmprunt', 'DESC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();

        $empruntsRetard = $this->em->createQueryBuilder()
            ->select('e, a, l')
            ->from(Emprunt::class, 'e')
            ->leftJoin('e.adherent', 'a')
            ->leftJoin('e.livre', 'l')
            ->where('e.dateRetour < :today')
            ->setParameter('today', $today)
            ->orderBy('e.dateRetour', 'ASC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();

        $nbAdherents = $this->em->getRepository(Adherent::class)->count([]);
        $ratioAdherentsActifs = $nbAdherents > 0
            ? round(($nbAdherentsActifs / $nbAdherents) * 100)
            : 0;

        $nbLivresDisponibles = max(0, $nbLivres - $nbLivresIndisponibles);

        return $this->render('admin/dashboard.html.twig', [
            'nbCategories' => $nbCategories,
            'nbLivres' => $nbLivres,
            'nbReservations' => $nbReservations,
            'nbUtilisateurs' => $nbUtilisateurs,
            'nbEmpruntsEnCours' => $nbEmpruntsEnCours,
            'nbEmpruntsRetard' => $nbEmpruntsRetard,
            'nbAdherentsActifs' => $nbAdherentsActifs,
            'nbLivresIndisponibles' => $nbLivresIndisponibles,
            'nbLivresDisponibles' => $nbLivresDisponibles,
            'ratioAdherentsActifs' => $ratioAdherentsActifs,
            'recentEmprunts' => $recentEmprunts,
            'empruntsRetard' => $empruntsRetard,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Bibliothèque - Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Adhérents', 'fas fa-id-card', Adherent::class);
        yield MenuItem::linkToCrud('Auteurs', 'fas fa-pen-fancy', Auteur::class);
        yield MenuItem::linkToCrud('Catégories', 'fas fa-layer-group', Categorie::class);
        yield MenuItem::linkToCrud('Emprunts', 'fas fa-arrow-right-arrow-left', Emprunt::class);
        yield MenuItem::linkToCrud('Réservations', 'fas fa-bookmark', Reservation::class);
        yield MenuItem::linkToCrud('Livres', 'fas fa-book', Livre::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user-shield', Utilisateur::class);
        
    }
}
