<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdherentCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

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

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Adherent) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }

        parent::persistEntity($entityManager, $entityInstance);

        if ($entityInstance->getUtilisateur() !== null || !$entityInstance->getEmail()) {
            return;
        }

        $existing = $entityManager->getRepository(Utilisateur::class)->findOneBy([
            'email' => $entityInstance->getEmail(),
        ]);

        if ($existing) {
            $existing->setAdherent($entityInstance);
            $entityManager->flush();
            return;
        }

        $user = new Utilisateur();
        $user->setEmail($entityInstance->getEmail());
        $user->setNom($entityInstance->getNom());
        $user->setPrenom($entityInstance->getPrenom());
        $user->setRoles(['ROLE_USER']);

        $user->setPassword($this->passwordHasher->hashPassword($user, '1234'));
        $user->setAdherent($entityInstance);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}