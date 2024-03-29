<?php

namespace App\Repository;

use App\Entity\DemandRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandRelation>
 *
 * @method DemandRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandRelation[]    findAll()
 * @method DemandRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandRelation::class);
    }

    public function save(DemandRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DemandRelation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   public function findRelations($user,array $demand): array
   {
    return $this->createQueryBuilder('dr')
         ->innerJoin('dr.user', 'u')
         ->innerJoin('dr.demand', 'd')
         ->andWhere('dr.user = :user OR dr.demand IN (:demand) ')
         ->setParameter('user', $user)
         ->setParameter('demand',$demand)
         ->getQuery()
         ->getResult()

        // SQL :
        /*
        SELECT * FROM demand_relation dr 
        INNER JOIN user u ON dr.user_id = u.id 
        INNER JOIN demand d ON dr.demand_id = d.id 
        WHERE dr.user_id = $user 
        OR dr.demand_id = $demand
        */
    ;
    }









//    /**
//     * @return DemandRelation[] Returns an array of DemandRelation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DemandRelation
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
