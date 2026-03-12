<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\Auteur;
use App\Entity\Categorie;
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
        $nbCategories = $this->em->getRepository(Categorie::class)->count([]);
        $nbAuteurs = $this->em->getRepository(Auteur::class)->count([]);
        $nbUtilisateurs = $this->em->getRepository(Utilisateur::class)->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'nbCategories' => $nbCategories,
            'nbAuteurs' => $nbAuteurs,
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
        yield MenuItem::linkToCrud('Catégories', 'fas fa-tags', Categorie::class);
        yield MenuItem::linkToCrud('Auteur', 'fas fa-newspaper', Auteur::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', Utilisateur::class);
    }
}
