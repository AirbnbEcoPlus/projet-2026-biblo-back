<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livre>
 */
class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    /**
     * @return Livre[] Returns an array of Livre objects
     */
    public function findByCriteres(array $criteres): array
    {
        $qb = $this->createQueryBuilder('livre');

        // 1. Filtre par titre (Recherche partielle)
        if (!empty($criteres['titre'])) {
            $qb->andWhere('livre.titre LIKE :titre')
            ->setParameter('titre', '%' . $criteres['titre'] . '%');
        }

        // 2. Filtre par langue (Exact)
        if (!empty($criteres['langue'])) {
            $qb->andWhere('livre.langue = :langue')
            ->setParameter('langue', $criteres['langue']);
        }

        // 3. Filtre par Catégorie (Relation ManyToMany)
        if (!empty($criteres['categorie'])) {
            $qb->innerJoin('livre.categories', 'c')
            ->andWhere('c.id = :catId')
            ->setParameter('catId', $criteres['categorie']);
        }

        // 4. Filtre par Période (Date de sortie)
        if (!empty($criteres['dateDebut'])) {
            $qb->andWhere('livre.dateSortie >= :debut')
            ->setParameter('debut', new \DateTime($criteres['dateDebut']));
        }

        if (!empty($criteres['dateFin'])) {
            $qb->andWhere('livre.dateSortie <= :fin')
            ->setParameter('fin', new \DateTime($criteres['dateFin']));
        }

        return $qb->getQuery()->getResult();
    }
}
