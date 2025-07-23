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

use Common\CoreBundle\Entity\Dictionary\DicCategory;
use Common\CoreBundle\Entity\Dictionary\DicDrivingLicense;
use Common\CoreBundle\Entity\Dictionary\DicJobForm;
use Common\CoreBundle\Entity\Dictionary\DicLanguage;
use Common\CoreBundle\Entity\Dictionary\DicShift;
use Common\CoreBundle\Presentation\AdvancedOfferFilter as Presentation;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AdvancedCvQueryFilter.
 */
class AdvancedCvQueryFilter extends AbstractPresentationFilter
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param Presentation $presentation
     */
    public function filterQueryBuilderByPresentation(QueryBuilder $queryBuilder, Presentation $presentation)
    {
        $this->filterQueryBuilderByAssociation(
            $queryBuilder,
            DicCategory::class,
            $presentation->getCategories()
        );
        $this->filterQueryBuilderByLocation($queryBuilder, $presentation->getLocations());
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

    /**
     * @param QueryBuilder $queryBuilder
     * @param Collection   $locations
     */
    private function filterQueryBuilderByLocation(QueryBuilder $queryBuilder, Collection $locations)
    {
        if ($locations->isEmpty()) {
            return;
        }

        $queryBuilder
            ->andWhere('el.county IN (:search_locations) OR el.city IN (:search_locations)')
            ->setParameter('search_locations', $locations)
        ;
    }
}
