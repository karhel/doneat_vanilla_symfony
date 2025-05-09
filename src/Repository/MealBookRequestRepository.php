<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\MealBookRequest;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<MealBookRequest>
 */
class MealBookRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MealBookRequest::class);
    }

    public function findByCreatedBy(User $user): array
    {
        $query = $this->createQueryBuilder('b')
            ->innerJoin('b.meal', 'm')
            ->andWhere('m.createdBy = :user')
            ->setParameter('user', $user);

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne la liste des demandes suivant le statut indiqué (en attente, validée, refusée) 
     * et ayant été émise par l'utilisateur indiqué
     * 
     * @param User $user Utilisateur ayant créé la demande de réservation (envoyé la demande)
     * @param int $status Statut de la demande (à partir de l'enum de la classe MealBookRequest)
     * 
     * @return array Liste des demandes suivant le statut et le demandeur
     */
    public function findByStatusAndRequestedBy(User $user, int $status, bool $withClosed = false): array
    {
        $query = $this->createQueryBuilder('b')
            ->andWhere('b.requestedBy = :user')
            ->andWhere('b.status = :status');

        if(!$withClosed) {
            $query->andWhere('b.isClosed = false');
        }
        
        $query
            ->setParameter('user', $user)
            ->setParameter('status', $status);

        return $query->getQuery()->getResult();
    }

    /**
     * Retourne la liste des demandes suivant le statut indiqué (en attente, validée, refusée)
     * et étant en lien avec un repas posté par l'utilisateur indiqué
     * 
     * @param User $user Utilisateur ayant posté le repas dont la demande fait l'objet
     * @param int $status Statut de la demande (à partir de l'num de la classe MealBookRequest)
     * 
     * @return array Liste des demandes suivant le statut et l'utilisateur ayant posté le repas
     */
    public function findByStatusAndMealCreatedBy(User $user, int $status, bool $withClosed = false): array
    {
        $query = $this->createQueryBuilder('b')
            ->innerJoin('b.meal', 'm')
            ->andWhere('m.createdBy = :user')
            ->andWhere('b.status = :status');
        
        if(!$withClosed) {
            $query->andWhere('b.isClosed = false');
        }

        $query
            ->setParameter('user', $user)
            ->setParameter('status', $status);

        return $query->getQuery()->getResult();
    }

    //    /**
    //     * @return MealBookRequest[] Returns an array of MealBookRequest objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MealBookRequest
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
