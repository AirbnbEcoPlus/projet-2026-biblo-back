<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class AdherentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Adherent::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom'),
            TextField::new('prenom'),
            DateField::new('dateNaiss', 'Date de naissance'),
            DateField::new('dateAdhesion', 'Date d\'adhésion'),
            TextField::new('email')->hideOnIndex(),
            TextField::new('adressePostale', 'Adresse postale')->hideOnIndex(),
            TextField::new('numTel', 'Téléphone')->hideOnIndex(),
            UrlField::new('photo')->hideOnIndex(),
        ];
    }
}
