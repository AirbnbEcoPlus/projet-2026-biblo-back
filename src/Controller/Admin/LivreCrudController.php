<?php

namespace App\Controller\Admin;

use App\Entity\Livre;
use App\Entity\Emprunt;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;


use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LanguageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class LivreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Livre::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        
        return $actions
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_ADMIN');
    }

    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Livres')
            ->setEntityLabelInSingular('Livre')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des livres')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un nouveau livre')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le livre')
            ->overrideTemplate('crud/new', 'admin/livre_crud/new.html.twig')
            ->overrideTemplate('crud/edit', 'admin/livre_crud/edit.html.twig');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addJsFile('js/google-books-loader.js');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            FormField::addPanel('Informations du livre'),
            TextField::new('titre'),

            AssociationField::new('auteurs'),
            AssociationField::new('categories', 'Catégories'),
            DateField::new('dateSortie', 'Date de sortie'),
            LanguageField::new('langue'),
            TextareaField::new('description')->hideOnIndex(),
            UrlField::new('photoCouverture', 'Photo de la couverture'),
        ];
    }
}
