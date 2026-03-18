<?php

namespace App\Controller\Admin;

use App\Entity\Livre;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

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
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Image;

class LivreCrudController extends AbstractCrudController
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

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
        return $assets->addJsFile('js/google-books-loader.js?v=20260319-2');
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
            Field::new('photoFile', 'Uploader une image')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'constraints' => [
                        new Image(
                            maxSize: '5M',
                            mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                            mimeTypesMessage: 'Formats acceptes: jpg, png, webp, gif.'
                        ),
                    ],
                ])
                ->onlyOnForms(),
            UrlField::new('photoCouverture', 'Photo de la couverture'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Livre) {
            $this->handleCoverUpload($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Livre) {
            $this->handleCoverUpload($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleCoverUpload(Livre $livre): void
    {
        $photoFile = $livre->getPhotoFile();
        if ($photoFile === null) {
            return;
        }

        $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/livres';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0775, true);
        }

        $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $extension = $photoFile->guessExtension() ?: 'bin';
        $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid('', true), $extension);

        try {
            $photoFile->move($uploadsDir, $newFilename);
        } catch (FileException $e) {
            throw new \RuntimeException('Upload de l\'image impossible.', 0, $e);
        }

        $livre->setPhotoCouverture('/uploads/livres/' . $newFilename);
        $livre->setPhotoFile(null);
    }
}
