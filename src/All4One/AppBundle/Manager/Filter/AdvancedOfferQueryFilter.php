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

namespace All4One\AppBundle\Manager\Filter;

use Common\CoreBundle\Doctrine\Repository\Dictionary\DictionaryRepository;
use Common\CoreBundle\Entity\Dictionary\DicDrivingLicense;
use Common\CoreBundle\Entity\Dictionary\DicJobForm;
use Common\CoreBundle\Entity\Dictionary\DicLanguage;
use Common\CoreBundle\Entity\Dictionary\DicShift;
use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Common\CoreBundle\Presentation\AdvancedOfferFilter as Presentation;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AdvancedOfferQueryFilter.
 */
class AdvancedOfferQueryFilter extends AbstractPresentationFilter
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param Presentation $presentation
     */
    public function filterQueryBuilderByPresentation(QueryBuilder $queryBuilder, Presentation $presentation)
    {

        $queryBuilder->andWhere('o.status = :status')
            ->setParameter('status', 5);

        $this->filterQueryBuilderByTitle($queryBuilder, $presentation->getTitle());
        $this->filterQueryBuilderByKeyword($queryBuilder, $presentation->getKeyword());
        $this->filterQueryBuilderByCategory($queryBuilder, $presentation->getCategories());

        if (!$presentation->getLocations()->isEmpty()) {
            $this->filterQueryBuilderByLocation($queryBuilder, $presentation->getLocations());
        }

        $this->filterQueryBuilderByAssociation(
            $queryBuilder,
            DicShift::class,
            $presentation->getShifts()
        );
        $this->filterQueryBuilderByAssociation(
            $queryBuilder,
            DicJobForm::class,
            $presentation->getJobForms()
        );
        $this->filterQueryBuilderByAssociation(
            $queryBuilder,
            DicLanguage::class,
            $presentation->getLanguages()
        );
        $this->filterQueryBuilderByAssociation(
            $queryBuilder,
            DicDrivingLicense::class,
            $presentation->getDrivingLicenses()
        );

        $this->resetFilterGeneration();
    }


    private function ensureLocationJoins(QueryBuilder $queryBuilder)
    {
        $joinParts = $queryBuilder->getDQLPart('join');
        $hasLocationJoins = false;
        
        if (isset($joinParts['o'])) {
            foreach ($joinParts['o'] as $join) {
                if ($join->getAlias() === 'cityl') {
                    $hasLocationJoins = true;
                    break;
                }
            }
        }
        
        if (!$hasLocationJoins) {
            $queryBuilder
                ->leftJoin('rel.dictionary', 'loc')
                ->leftJoin('loc.cityLocations', 'cityl')
                ->leftJoin('loc.countyLocations', 'countyl');
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Collection   $locations
     */
    private function filterQueryBuilderByLocation(QueryBuilder $queryBuilder, Collection $locations)
    {
        if ($locations->isEmpty()) {
            return;
        }

        // Biztosítjuk, hogy a szükséges JOIN-ok léteznek
        $this->ensureLocationJoins($queryBuilder);

        $queryBuilder
            ->andWhere('cityl.city IN (:locations) OR cityl.county IN (:locations)')
            ->setParameter('locations', $locations);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Collection   $categories
     */
    private function filterQueryBuilderByCategory(QueryBuilder $queryBuilder, Collection $categories)
    {
        if ($categories->isEmpty()) {
            return;
        }

        /** @var DictionaryRepository $dictionaryRepository */
        $dictionaryRepository = $this->objectManager->getRepository('CommonCoreBundle:Dictionary\Dictionary');

        $categories = $dictionaryRepository->retrieveWithChildCategories($categories);

        $queryBuilder
            ->andWhere('rel.dictionary IN (:categories)')
            ->andWhere('o.status = :activeStatus')
            ->setParameter('categories', $categories)
            ->setParameter('activeStatus', OfferStatusEnum::ACTIVE)
        ;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string|null  $title
     */
    private function filterQueryBuilderByTitle(QueryBuilder $queryBuilder, string $title = null)
    {
        if (empty($title)) {
            return;
        }

        $ra = $queryBuilder->getRootAliases();
        $ra = reset($ra);
        $param = $this->getFilterParameter();

        $queryBuilder
            ->andWhere($ra.'.title LIKE :'.$param)
                ->setParameter($param, '%'.$title.'%')
        ;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string|null  $keyword
     */
    private function filterQueryBuilderByKeyword(QueryBuilder $queryBuilder, string $keyword = null)
    {
        if (empty($keyword)) {
            return;
        }

        $ra = $queryBuilder->getRootAliases();
        $ra = reset($ra);
        $param = $this->getFilterParameter();

        /** @var DictionaryRepository $dictionaryRepository */
        $dictionaryRepository = $this->objectManager->getRepository('CommonCoreBundle:Dictionary\Dictionary');

        $categories = $dictionaryRepository->findCategoriesByKeyword($keyword);

        $queryBuilder

            ->andWhere('( '.$ra.'.title LIKE :'.$param.' OR '.$ra.'.lead LIKE :'.$param.' OR ( '.$ra.'.anonim = 0 AND ( f.name LIKE :'.$param.'  OR f.nameLong LIKE :'.$param.' )) )')
                ->setParameter($param, '%'.$keyword.'%')
           // ->andWhere('rel.dictionary IN (:categories)')
             // ->setParameter('categories', $categories)
        ;
    }
}
