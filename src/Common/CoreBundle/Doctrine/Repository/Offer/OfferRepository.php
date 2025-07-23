<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Common\CoreBundle\Doctrine\Repository\Offer;

use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class OfferRepository.
 */
class OfferRepository extends EntityRepository
{
    /**
     * @param int $firmId
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function findAvailableFirmTiles(int $firmId, int $page = 1): Pagerfanta
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT o, oc
                FROM CommonCoreBundle:Offer\Offer o
                LEFT JOIN o.candidates oc
                WHERE o.firm = :firmId
                ORDER BY o.id DESC
            ')
            ->setParameter('firmId', $firmId);

        return $this->createPaginator($query, $page);
    }

    /**
     * @param int $firmId
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function findPurchasableFirmTiles(int $firmId, int $page = 1): Pagerfanta
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT o, oc
                FROM CommonCoreBundle:Offer\Offer o
                LEFT JOIN o.candidates oc
                WHERE o.firm = :firmId
                AND o.status = :active
                ORDER BY o.id DESC
            ')
            ->setParameter('active', OfferStatusEnum::ACTIVE)
            ->setParameter('firmId', $firmId);

        return $this->createPaginator($query, $page);
    }

    /**
     * @param int $offerId
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function findOfferCandidates(int $offerId, int $page = 1): Pagerfanta
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT c, ce
                FROM CommonCoreBundle:Offer\OfferCandidate c
                LEFT JOIN c.employee ce
                WHERE c.offerId = :offerId
                ORDER BY c.createdAt DESC
            ')
            ->setParameter('offerId', $offerId);

        return $this->createPaginator($query, $page);
    }

    /**
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function findLatestTile(int $page = 1): Pagerfanta
    {
        $query = $this
            ->createQueryBuilder('o')
            ->addSelect(['f', 'rel', 'd'])
            ->distinct()
            ->join('o.firm', 'f')
            ->leftJoin('o.dictionaryRelations', 'rel')
            ->innerJoin('rel.dictionary', 'd')
            ->andWhere('o.applicableFromDate <= :now')
            ->setParameter('now', new \DateTime())
            ->addOrderBy('o.applicableFromDate', 'DESC')
            ->getQuery()
        ;

        return $this->createPaginator($query, $page);
    }

    /**
     * @param int   $page
     * @param Offer $relateToOffer
     *
     * @return Pagerfanta
     */
    public function findLatestRelatedTile(int $page, Offer $relateToOffer): Pagerfanta
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT o, f, rel, l
                FROM CommonCoreBundle:Offer\Offer o
                JOIN o.firm f
                LEFT JOIN o.dictionaryRelations rel
                INNER JOIN rel.dictionary l
                WHERE o.applicableFromDate <= :now
                  AND o.firm = :firmId
                  AND o.id != :currentOfferId
                ORDER BY o.applicableFromDate DESC
            ')
            ->setParameter('now', new \DateTime())
            ->setParameter('firmId', $relateToOffer->getFirm()->getId())
            ->setParameter('currentOfferId', $relateToOffer->getId())
        ;

        return $this->createPaginator($query, $page);
    }

    /**
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function findLatestHighlightedTile(int $page = 1): Pagerfanta
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT o, f
                FROM CommonCoreBundle:Offer\Offer o
                INNER JOIN o.firm f
                LEFT JOIN o.dictionaryRelations rel
                INNER JOIN rel.dictionary l
                WHERE o.expireDate >= :now
                AND o.offerExaltationUntil >:now
                AND o.status = :active
                ORDER BY o.expireDate DESC
            ')
            ->setParameter('now', new \DateTime())
            ->setParameter('active', OfferStatusEnum::ACTIVE)
        ;

        return $this->createPaginator($query, $page, 9);
    }

    /**
     * @param Query $query
     * @param int   $page
     * @param int   $numItems
     *
     * @return Pagerfanta
     */
    public function createPaginator(Query $query, int $page, int $numItems = Offer::NUM_ITEMS): Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator->setMaxPerPage($numItems);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilderOfLatestTile(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('o')
            ->addSelect(['o', 'f'])
            ->distinct()
            ->join('o.firm', 'f')
            ->leftJoin('o.dictionaryRelations', 'rel')
            ->innerJoin('rel.dictionary', 'd')
            ->leftJoin('rel.dictionary', 'loc')
            ->leftJoin('loc.cityLocations', 'cityl')
            ->leftJoin('loc.countyLocations', 'countyl')
            ->where('o.applicableFromDate <= :now')
            ->andWhere('o.expireDate >= :now')
            ->andWhere('o.status = :activeStatus')
            ->setParameter('now', new \DateTime())
            ->setParameter('activeStatus', OfferStatusEnum::ACTIVE)
            ->addOrderBy('o.offerExaltationUntil', 'DESC')
            ->addOrderBy('o.applicableFromDate', 'DESC')
            ;
    }

    /**
   
     
public function getQueryBuilderOfLatestTile(): QueryBuilder
{
    return $this
        ->createQueryBuilder('o')
        ->select('o', 'f')
        ->join('o.firm', 'f')
        ->leftJoin('o.dictionaryRelations', 'rel')
        ->leftJoin('rel.dictionary', 'd')
        ->where('o.applicableFromDate <= :now')
        ->andWhere('o.createdAt >= :created_at')
        ->andWhere('o.status = :activeStatus')
        ->setParameter('now', new \DateTime())
        ->setParameter('activeStatus', OfferStatusEnum::ACTIVE)
        ->setParameter('created_at', "2022-05-01")
        ->addOrderBy('o.offerExaltationUntil', 'DESC')
        ->addOrderBy('o.applicableFromDate', 'DESC');
}
*/
    /**
     * Offers tömeges aktiválása
     *
     * @param array $ids Az aktiválandó hirdetések ID-i
     * @return int A módosított rekordok száma
     */
    public function batchActivate(array $ids)
    {
        return $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', ':activeStatus')
            ->where('o.id IN (:ids)')
            ->setParameter('activeStatus', OfferStatusEnum::ACTIVE)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    /**
     * Offers tömeges inaktiválása
     *
     * @param array $ids Az inaktiválandó hirdetések ID-i
     * @return int A módosított rekordok száma
     */
    public function batchDeactivate(array $ids)
    {
        return $this->createQueryBuilder('o')
            ->update()
            ->set('o.status', ':inactiveStatus')
            ->where('o.id IN (:ids)')
            ->setParameter('inactiveStatus', OfferStatusEnum::INACTIVE)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    /**
     * Aktív hirdetések számának lekérdezése
     * 
     * @return int
     */
    public function getActiveOffersCount()
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.status = :status')
            ->setParameter('status', OfferStatusEnum::ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Inaktív hirdetések számának lekérdezése
     * 
     * @return int
     */
    public function getInactiveOffersCount()
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.status = :status')
            ->setParameter('status', OfferStatusEnum::INACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Státusz szerinti hirdetések számának lekérdezése (ha esetleg több státuszt szeretnél megjeleníteni)
     * 
     * @return array
     */
    public function getOfferCountsByStatus()
    {
        $result = $this->createQueryBuilder('o')
            ->select('o.status, COUNT(o.id) as count')
            ->groupBy('o.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['status']] = (int)$row['count'];
        }

        return $counts;
    }

    /**
     * Összes hirdetés számának lekérdezése
     * 
     * @return int
     */
    public function getTotalOffersCount()
    {
        return $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Fejlett szöveges keresés címben és cég névben
     *
     * @param string $searchTerm
     * @return array
     */
    public function searchByTitleAndFirm($searchTerm)
    {
        if (empty(trim($searchTerm))) {
            return [];
        }
        
        $searchTerm = trim($searchTerm);
        $likePattern = '%' . $searchTerm . '%';
        
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.firm', 'f')
            ->where('o.title LIKE :searchTerm')
            ->orWhere('f.name LIKE :searchTerm') 
            ->orWhere('f.nameLong LIKE :searchTerm')
            ->setParameter('searchTerm', $likePattern);
            
        return $qb->getQuery()->getResult();
    }

}
