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
            SELECT id FROM (
                SELECT m.id, 
                    (6371 * acos(
                        cos(radians(:lat)) * cos(radians(a.latitude)) * 
                        cos(radians(a.longitude) - radians(:lng)) + 
                        sin(radians(:lat)) * sin(radians(a.latitude))
                    )) AS distance
                
                    FROM meal AS m
                
                INNER JOIN address AS a ON m.location_id = a.id
                LEFT JOIN booking_request AS b ON b.meal_id = m.id

                WHERE a.latitude IS NOT NULL AND a.longitude IS NOT NULL

                AND b.id IS NULL AND b.requested_at IS NULL
                AND m.created_at >= :last

            ) AS results

            WHERE distance < :dist
        ";
        
        $stmt   = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            
            'lat'   => $latitude,
            'lng'   => $longitude,

            'dist'  => $distance,
            'last'  => (new \DateTime('-5 day'))->format('Y-m-d H:i:s')

        ])->fetchFirstColumn();

        return $result;
    }

    public function findByDistanceFromWithHydration(float $latitude, float $longitude, float $distance, $page = 1, $perPage = 1) : array
    {
        $result = $this->findByDistanceFrom($latitude, $longitude, $distance, $page, $perPage);

        
        if (empty($result)) {
            return [];
        }

        return $this->createQueryBuilder('m')
            ->where('m.id IN (:ids)')

            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($perPage)
            ->setFirstResult(($page - 1) * $perPage)

            ->setParameter('ids', $result)
            ->getQuery()
            ->getResult();
    }

    public function countByDistanceFrom(float $latitude, float $longitude, float $distance): int
    {
        $result = $this->findByDistanceFrom($latitude, $longitude, $distance);
        return count($result);
    }

    public function countAll() : int
    {
        return $this->createQueryBuilder('m')
            ->select('count(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
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

    public function countAvailable($distance = null, $latitude = null, $longitude = null): int
    {
        if($distance && $latitude && $longitude) { return $this->countByDistanceFrom($latitude, $longitude, $distance); }

        $qb = $this->createQueryBuilder('m');

        return $qb->select('count(m.id)')
            ->leftJoin('m.bookingRequests', 'b')

            ->andWhere($qb->expr()->isNull('b.requestedAt'))

            ->andWhere($qb->expr()->isNull('b.id'))

            ->andWhere('m.createdAt > :last')
            ->setParameter('last', new \DateTime('-1 day'))

            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paginateAvailable($page, $perPage, $distance = null, $latitude = null, $longitude = null): array
    {
        if($distance && $latitude && $longitude) { return $this->findByDistanceFromWithHydration($latitude, $longitude, $distance, $page, $perPage); }

        $qb = $this->createQueryBuilder('m');

        return $qb
            ->leftJoin('m.bookingRequests', 'b')
            
            ->andWhere($qb->expr()->isNull('b.requestedAt'))
            ->andWhere('m.createdAt > :last')

            ->andWhere($qb->expr()->isNull('b.id'))

            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($perPage)
            
            ->setFirstResult(($page - 1) * $perPage)
            ->setParameter('last', new \DateTime('-1 day'))
            
            ->getQuery()
            ->getResult();
    }
}
