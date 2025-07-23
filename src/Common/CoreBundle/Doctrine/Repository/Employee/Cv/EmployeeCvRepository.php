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

namespace Common\CoreBundle\Doctrine\Repository\Employee\Cv;

use Common\CoreBundle\Doctrine\Repository\Dictionary\DictionaryRepository;
use Common\CoreBundle\Entity\Dictionary\DicCategory;
use Common\CoreBundle\Entity\Dictionary\DicCity;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvStatusEnum;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvWillToMoveEnum;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvWillToTravelEnum;
use Common\CoreBundle\Enumeration\User\UserStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class EmployeeCvRepository.
 */
class EmployeeCvRepository extends EntityRepository
{
    public function findEmployeeCvs(int $employeeId, int $page = 1): Pagerfanta
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT cv
                FROM CommonCoreBundle:Employee\Cv\EmployeeCv cv
                WHERE cv.employeeId = :employeeId
                ORDER BY cv.createdAt DESC
            ')
            ->setParameter('employeeId', $employeeId);

        return $this->createPaginator($query, $page);
    }

    /**
     * @param Offer $offer
     *
     * @return QueryBuilder
     */
    public function createQueryOfCandidatesBy(Offer $offer): QueryBuilder
    {
        return $this
            ->createQueryBuilder('o')
            ->select(['o', 'c', 'e'])
            ->distinct(true)
            ->innerJoin('o.candidates', 'c')
            ->innerJoin('o.employee', 'e')
            ->innerJoin('e.location', 'el')
            ->andWhere('c.offer = :offer')
                ->setParameter('offer', $offer)
            ->orderBy('o.createdAt', 'DESC')
        ;
    }

    /**
     * @param Offer $offer
     *
     * @return QueryBuilder
     */
    public function createQueryOfMatchesBy(Offer $offer): QueryBuilder
    {
        $locations = new ArrayCollection();

        /** @var DictionaryRepository $dictionaryRepo */
        $dictionaryRepo = $this->getEntityManager()->getRepository('CommonCoreBundle:Dictionary\DicLocation');

        foreach ($offer->getLocations() as $location) {
            if ($location instanceof DicCity) {
                $locations->add($dictionaryRepo->findOneBy(['city' => $location])->getCounty());
            }
        }

        $categories = $this->retrieveWithParentCategories($offer->getCategories());

        return $this
            ->createQueryBuilder('o')
            ->select(['o', 'c', 'e'])
            ->distinct(true)
            ->leftJoin(
                'o.candidates',
                'c',
                'WITH',
                'c.offer = :offer'
            )
                ->setParameter('offer', $offer)
            ->innerJoin('o.dictionaryRelations', 'cat')
            ->leftJoin('o.dictionaryRelations', 'lrel')
            ->leftJoin('lrel.dictionary', 'loc')
            ->leftJoin('loc.cityLocations', 'cityl')
            ->leftJoin('loc.countyLocations', 'countyl')
            ->innerJoin('o.employee', 'e')
            ->innerJoin('e.location', 'el')
            ->andWhere('c.id IS NULL')
            ->andWhere('o.status = :cvstatus')
                ->setParameter('cvstatus', EmployeeCvStatusEnum::ACTIVE)
            ->andWhere('e.status = :employeestatus')
                ->setParameter('employeestatus', UserStatusEnum::ACTIVE)
            ->andWhere('cat.dictionary IN (:categories)')
                ->setParameter('categories', $categories)
            ->andWhere(implode(' OR ', [
                'o.willToMove = :anywhere',
                'o.willToTravel = :bydistance AND (el.county IN (:locations) OR el.city IN (:locations))',
                'o.willToTravel = :bylocation AND ('.implode(' OR ', [
                    'cityl.city IN (:locations)',
                    'cityl.county IN (:locations)',
                    'countyl.city IN (:locations)',
                    'countyl.county IN (:locations)',
                ]).')',
            ]))
                ->setParameter('anywhere', EmployeeCvWillToMoveEnum::ANYWHERE)
                ->setParameter('bydistance', EmployeeCvWillToTravelEnum::BY_DISTANCE)
                ->setParameter('bylocation', EmployeeCvWillToTravelEnum::BY_LOCATION)
                ->setParameter('locations', $locations)
                ->orderBy('e.createdAt', 'DESC')
        ;
    }

    public function createPaginator(Query $query, int $page): Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator->setMaxPerPage(EmployeeCv::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * @return QueryBuilder
     */
    public function getCvsQuery()
    {
        return $this->createQueryBuilder('ecv')
            ->select(['ecv,e,el'])
            ->innerJoin('ecv.employee', 'e')
            ->innerJoin('e.location', 'el')
            ->where('e.status = :activeEmployee')
                ->setParameter('activeEmployee',UserStatusEnum::ACTIVE)
            ->andWhere('ecv.status = :activeCv')
                ->setParameter('activeCv',EmployeeCvStatusEnum::ACTIVE)
            ->orderBy('ecv.createdAt', 'DESC');
    }

    /**
     * @return QueryBuilder
     */
    public function getDbCvsQuery()
    {

        $pastDate=date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d'))));



        return $this->createQueryBuilder('ecv')
            ->select(['ecv,e,el'])
            ->innerJoin('ecv.employee', 'e')
            ->innerJoin('e.location', 'el')
            ->where('ecv.createdAt >= :pastDate')
            ->setParameter('pastDate', $pastDate)
            ->orderBy('ecv.createdAt', 'DESC');
    }

    /**
     * @param DicCategory[] $categories
     *
     * @return array
     */
    private function retrieveParentIdsOfCategories($categories): array
    {
        $result = [];

        foreach ($categories as $category) {
            if (null !== $category->getParentId()) {
                $result[] = $category->getParentId();
            }
        }

        return $result;
    }

    /**
     * @param DicCategory[] $categories
     * @param array         $ids
     *
     * @return array
     */
    private function retrieveCategoriesByIds(array $ids)
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select(['o'])
            ->from('CommonCoreBundle:Dictionary\DicCategory', 'o')
            ->andWhere('o.id IN (:ids)')
                ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param DicCategory[] $categories
     *
     * @return array
     */
    private function retrieveWithParentCategories($categories)
    {
        $result = $categories->toArray();

        $parentIds = $this->retrieveParentIdsOfCategories($categories);

        while (!empty($parentIds)) {
            $newCategories = $this->retrieveCategoriesByIds($parentIds);

            $parentIds = $this->retrieveParentIdsOfCategories($newCategories);
            $result = array_merge($result, $newCategories);
        }

        return $result;
    }
}
