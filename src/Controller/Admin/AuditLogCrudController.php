<?php

namespace App\Controller\Admin;

use App\Entity\AuditLog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AuditLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AuditLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Log')
            ->setEntityLabelInPlural('Logs')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPageTitle(Crud::PAGE_INDEX, 'Historique des actions')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du log');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield DateTimeField::new('createdAt', 'Date');
        yield TextField::new('action', 'Action');
        yield TextField::new('entityClass', 'Entité');
        yield TextField::new('entityId', 'ID entité');
        yield TextField::new('userEmail', 'Utilisateur')->setSortable(false);
        yield TextField::new('dataSummary', 'Résumé')->onlyOnIndex()->setSortable(false);
        yield TextareaField::new('data', 'Données')
            ->onlyOnDetail()
            ->formatValue(static function ($value): string {
                if (!is_array($value)) {
                    return '';
                }

                return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '';
            });
    }
}
