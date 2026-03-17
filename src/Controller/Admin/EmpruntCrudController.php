<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Routing\Attribute\Route;

class EmpruntCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Emprunt::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Emprunts')
            ->setEntityLabelInSingular('Emprunt')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des emprunts')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvel emprunt')
            ->setSearchFields(['adherent.nom', 'adherent.prenom', 'livre.titre']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, static function (Action $action): Action {
                return $action
                    ->setLabel('Nouvel emprunt')
                    ->linkToRoute('admin_emprunt_manual_new')
                    ->setCssClass('btn btn-primary');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateField::new('dateEmprunt', 'Date d\'emprunt')->hideOnForm(),
            DateField::new('dateRetour', 'Date de retour'),
            AssociationField::new('adherent', 'Adhérent')
                ->formatValue(static function ($value, ?Emprunt $emprunt): string {
                    if (!$emprunt || !$emprunt->getAdherent()) {
                        return '';
                    }

                    $adherent = $emprunt->getAdherent();

                    return sprintf('%s %s', $adherent->getPrenom(), $adherent->getNom());
                }),
            AssociationField::new('livre', 'Livre')
                ->formatValue(static function ($value, ?Emprunt $emprunt): string {
                    if (!$emprunt || !$emprunt->getLivre()) {
                        return '';
                    }

                    return (string) $emprunt->getLivre()->getTitre();
                }),
        ];
    }

    #[Route('/admin/emprunts/manual-new', name: 'admin_emprunt_manual_new', methods: ['GET', 'POST'])]
    public function manualNew(
        Request $request,
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator
    ): Response {
        $today = new \DateTimeImmutable('today');
        $activeLoansByAdherent = $this->getActiveLoanCountsByAdherent($entityManager, $today);

        $form = $this->createFormBuilder()
            ->add('adherent', EntityType::class, [
                'class' => Adherent::class,
                'label' => 'Adhérent',
                'placeholder' => 'Choisir un adhérent',
                'choice_label' => static function (Adherent $adherent): string {
                    return sprintf('%s %s', $adherent->getPrenom(), $adherent->getNom());
                },
                'choice_attr' => static function (Adherent $adherent) use ($activeLoansByAdherent): array {
                    $adherentId = $adherent->getId() ?? 0;
                    $activeLoans = (int) ($activeLoansByAdherent[$adherentId] ?? 0);

                    return [
                        'data-active-loans' => (string) $activeLoans,
                        'data-remaining-slots' => (string) max(0, 5 - $activeLoans),
                        'data-email' => (string) ($adherent->getEmail() ?? 'Non renseigné'),
                        'data-phone' => (string) ($adherent->getNumTel() ?? 'Non renseigné'),
                    ];
                },
            ])
            ->add('livres', EntityType::class, [
                'class' => Livre::class,
                'label' => 'Livres à emprunter',
                'multiple' => true,
                'expanded' => false,
                'choice_label' => 'titre',
                'query_builder' => static function (LivreRepository $repository) use ($today) {
                    return $repository->createQueryBuilder('l')
                        ->leftJoin('l.emprunts', 'e', 'WITH', 'e.dateRetour >= :today')
                        ->andWhere('e.id IS NULL')
                        ->setParameter('today', $today)
                        ->orderBy('l.titre', 'ASC');
                },
                'choice_attr' => static function (Livre $livre): array {
                    $authors = [];
                    foreach ($livre->getAuteurs() as $auteur) {
                        $authors[] = trim(($auteur->getPrenom() ?? '').' '.($auteur->getNom() ?? ''));
                    }

                    return [
                        'data-langue' => (string) ($livre->getLangue() ?? 'Non renseignée'),
                        'data-auteurs' => $authors ? implode(', ', $authors) : 'Non renseignés',
                        'data-date-sortie' => $livre->getDateSortie()?->format('d/m/Y') ?? 'Non renseignée',
                    ];
                },
                'help' => 'Seuls les livres disponibles sont affichés.',
            ])
            ->add('dateRetour', DateType::class, [
                'label' => 'Date de retour prévue',
                'widget' => 'single_text',
                'html5' => true,
                'data' => (new \DateTimeImmutable('today'))->modify('+14 days'),
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Adherent $adherent */
            $adherent = $form->get('adherent')->getData();
            /** @var iterable<Livre> $livres */
            $livres = $form->get('livres')->getData();
            /** @var \DateTimeInterface $dateRetour */
            $dateRetour = $form->get('dateRetour')->getData();

            $livresArray = is_array($livres) ? $livres : iterator_to_array($livres);

            if (count($livresArray) === 0) {
                $this->addFlash('danger', 'Sélectionne au moins un livre.');

                return $this->render('admin/emprunt_manual_new.html.twig', [
                    'form' => $form,
                ]);
            }

            $activeLoans = (int) $entityManager->createQueryBuilder()
                ->select('COUNT(e.id)')
                ->from(Emprunt::class, 'e')
                ->where('e.adherent = :adherent')
                ->andWhere('e.dateRetour >= :today')
                ->setParameter('adherent', $adherent)
                ->setParameter('today', $today)
                ->getQuery()
                ->getSingleScalarResult();

            if ($activeLoans + count($livresArray) > 5) {
                $this->addFlash('danger', 'Limite atteinte: un adhérent ne peut pas dépasser 5 emprunts en cours.');

                return $this->render('admin/emprunt_manual_new.html.twig', [
                    'form' => $form,
                ]);
            }

            $unavailableBooks = $entityManager->createQueryBuilder()
                ->select('DISTINCT l')
                ->from(Livre::class, 'l')
                ->join('l.emprunts', 'e')
                ->where('l IN (:selectedBooks)')
                ->andWhere('e.dateRetour >= :today')
                ->setParameter('selectedBooks', $livresArray)
                ->setParameter('today', $today)
                ->getQuery()
                ->getResult();

            if (count($unavailableBooks) > 0) {
                $titles = array_map(
                    static fn (Livre $livre): string => (string) $livre->getTitre(),
                    $unavailableBooks
                );

                $this->addFlash('danger', 'Indisponible: '.implode(', ', $titles));

                return $this->render('admin/emprunt_manual_new.html.twig', [
                    'form' => $form,
                ]);
            }

            foreach ($livresArray as $livre) {
                $emprunt = new Emprunt();
                $emprunt->setAdherent($adherent);
                $emprunt->setLivre($livre);
                $emprunt->setDateEmprunt(new \DateTime());
                $emprunt->setDateRetour(\DateTime::createFromInterface($dateRetour));
                $entityManager->persist($emprunt);
            }

            $entityManager->flush();

            $this->addFlash('success', sprintf('%d emprunt(s) créé(s) avec succès.', count($livresArray)));

            return $this->redirect(
                $adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            );
        }

        return $this->render('admin/emprunt_manual_new.html.twig', [
            'form' => $form,
        ]);
    }

    private function getActiveLoanCountsByAdherent(EntityManagerInterface $entityManager, \DateTimeImmutable $today): array
    {
        $rows = $entityManager->createQueryBuilder()
            ->select('IDENTITY(e.adherent) AS adherentId, COUNT(e.id) AS activeCount')
            ->from(Emprunt::class, 'e')
            ->where('e.dateRetour >= :today')
            ->setParameter('today', $today)
            ->groupBy('e.adherent')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['adherentId']] = (int) $row['activeCount'];
        }

        return $counts;
    }
}
