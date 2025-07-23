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

namespace Common\CoreBundle\Doctrine\Repository\Dictionary;

use Common\CoreBundle\Entity\Dictionary\DicCategory;
use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Doctrine\ORM\EntityRepository;

/**
 * Class DictionaryRepository.
 */
class DictionaryRepository extends EntityRepository
{
    /**
     * @param string $city
     * @param string $zip
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return mixed
     */
    public function findOneByCityAndZip(string $city, string $zip)
    {
        return $query = $this->getEntityManager()
            ->createQuery('
                SELECT l
                FROM CommonCoreBundle:Dictionary\DicCategory l
                LEFT JOIN l.city c
                LEFT JOIN l.zip z
                WHERE z.value = :zip
                AND c.value LIKE :city
            ')
            ->setParameter('zip', $zip)
            ->setParameter('city', '%'.$city.'%')
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param string $keyword
     *
     * @return array
     */
    public function findCategoriesByKeyword (string $keyword)
    {
        return $query = $this->getEntityManager()
            ->createQuery('
                SELECT l.id
                FROM CommonCoreBundle:Dictionary\Dictionary l
                WHERE l.value LIKE :keyword
            ')
            ->setParameter('keyword', $keyword)
            ->getResult()
        ;
    }

    /**
     * @param $categories
     *
     * @return array
     */
    public function retrieveWithParentCategories($categories)
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

    /**
     * @param $categories
     *
     * @return array
     */
    public function retrieveWithChildCategories($categories)
    {
        $parentIds = $this->retrieveChildIdsOfCategories($categories);

        $result = $this->retrieveCategoriesByIds($parentIds);

        return $result;
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
     * @param array $ids
     *
     * @return mixed
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
    private function retrieveChildIdsOfCategories($categories): array
    {
        $result = [];

        foreach ($categories as $category) {
            if (!empty($category)) {
                $result = array_merge($result, $this->retrieveChildIds($category->getChildren()->toArray()));
                $result[] = $category->getId();
            }
        }

        return $result;
    }

    private function retrieveChildIds($dictionaries)
    {
        $result = [];
        /** @var Dictionary $dictionary */
        foreach ($dictionaries as $dictionary) {
            if (!empty($dictionary)) {
                $result = array_merge($result, $this->retrieveChildIds($dictionary->getChildren()->toArray()));
                $result[] = $dictionary->getId();
            }
        }

        return $result;
    }
}
