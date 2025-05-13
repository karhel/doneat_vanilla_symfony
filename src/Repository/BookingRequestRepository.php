<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\BookingRequest;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<BookingRequest>
 */
class BookingRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookingRequest::class);
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
            $query->andWhere($query->expr()->isNull('b.closedAt'));
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
            $query->andWhere($query->expr()->isNull('b.closedAt'));
        }

        $query
            ->setParameter('user', $user)
            ->setParameter('status', $status);

        return $query->getQuery()->getResult();
    }

    public function findByRequestedByAndToClose(User $user): array
    {
        $query = $this->createQueryBuilder('b');

        $query
            ->andWhere('b.requestedBy = :user')
            ->andWhere($query->expr()->isNull('b.closedAt'))
            ->andWhere($query->expr()->isNull('b.closedByEaterAt'))
            ->setParameter('user', $user);
            
        return $query->getQuery()->getResult();
    }

    public function findByCreatedByAndToClose(User $user): array
    {
        $query = $this->createQueryBuilder('b');

        $query     
            ->innerJoin('b.meal', 'm')
            ->andWhere('m.createdBy = :user')
            ->andWhere($query->expr()->isNull('b.closedAt'))
            ->andWhere($query->expr()->isNull('b.closedByGiverAt'))
            ->setParameter('user', $user);
            
        return $query->getQuery()->getResult();
    }
}
