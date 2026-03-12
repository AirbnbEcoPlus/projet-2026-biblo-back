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
use App\Controller\Admin\AdherentCrudController;
use App\Controller\Admin\AuteurCrudController;
use App\Controller\Admin\CategorieCrudController;
use App\Controller\Admin\EmpruntCrudController;
use App\Controller\Admin\ReservationCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use PhpParser\Node\Expr\Cast;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Config\Framework\Validation\AutoMappingConfig;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function index(): Response
    {
        $nbCategories = $this->em->getRepository(Categorie::class)->count([]);
        $nbLivres = $this->em->getRepository(Livre::class)->count([]);
        $nbUtilisateurs = $this->em->getRepository(Utilisateur::class)->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'nbCategories' => $nbCategories,
            'nbLivres' => $nbLivres,
            'nbUtilisateurs' => $nbUtilisateurs,
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
        yield MenuItem::linkTo(AdherentCrudController::class, 'Adhérents', 'fas fa-id-card', Adherent::class);
        yield MenuItem::linkTo(AuteurCrudController::class, 'Auteurs', 'fas fa-pen-fancy', Auteur::class);
        yield MenuItem::linkTo(CategorieCrudController::class, 'Catégories', 'fas fa-layer-group', Categorie::class);
        yield MenuItem::linkTo(EmpruntCrudController::class, 'Emprunts', 'fas fa-arrow-right-arrow-left', Emprunt::class);
        yield MenuItem::linkTo(ReservationCrudController::class, 'Réservations', 'fas fa-bookmark', Reservation::class);
        yield MenuItem::linkTo(LivreCrudController::class, 'Livres', 'fas fa-book', Livre::class);
        yield MenuItem::linkTo(UtilisateurCrudController::class, 'Utilisateurs', 'fas fa-user-shield', Utilisateur::class);
    }
}
