<?php

namespace App\Repository;

use App\Entity\Meal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Meal>
 */
class MealRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meal::class);
    }

    public function findByDistanceFrom(float $latitude, float $longitude, float $distance) : array
    {
        $conn   = $this->getEntityManager()->getConnection();
        $sql    = "
            SELECT m.*,
                (6371 * acos(cos(radians(:lat)) * cos(radians(m.latitude)) * cos(radians(m.longitude) - radians(:lng)) + sin(radians(:lat)) * sin(radians(m.latitude)))) AS distance
            FROM meal AS m
        ";
        
        $stmt   = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'lat'   => $latitude,
            'lng'   => $longitude,
            //'dist'  => $distance
        ]);


        /*
        $haversineFormula = "(6371 * acos(cos(radians(:lat)) * cos(radians(p.latitude)) * cos(radians(p.longitude) - radians(:lng)) + sin(radians(:lat)) * sin(radians(p.latitude))))";
        $query = $this->getEntityManager()
            ->createQuery(
                "SELECT m FROM \App\Entity\Meal m WHERE {$haversineFormula} <= :dist"
            )
            ->setParameters();
            */
        
        return $result->fetchAllAssociative();
    }

    public function paginate($page, $perPage) : array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage)
            ->getQuery()
            ->getResult();
    }

    public function paginateAvailable($page, $perPage): array
    {
        $qb = $this->createQueryBuilder('m');

        return $qb
            ->leftJoin('m.bookRequests', 'b')
            
            ->andWhere($qb->expr()->isNull('m.bookedBy'))
            //->andWhere('m.createdAt > :last')

            ->andWhere($qb->expr()->isNull('b.id'))

            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage)
            //->setParameter('last', new \DateTime('-1 day'))
            ->getQuery()
            ->getResult();
    }

    public function countAll() : int
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countAvailable(): int
    {
        $qb = $this->createQueryBuilder('m');

        return $qb->select('count(m.id)')
            ->leftJoin('m.bookRequests', 'b')
            ->andWhere($qb->expr()->isNull('m.bookedBy'))
            ->andWhere($qb->expr()->isNull('b.id'))
            //->andWhere('m.createdAt > :last')
            //->setParameter('last', new \DateTime('-1 day'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Meal[] Returns an array of Meal objects
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

    //    public function findOneBySomeField($value): ?Meal
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
