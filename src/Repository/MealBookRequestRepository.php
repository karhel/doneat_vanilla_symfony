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
