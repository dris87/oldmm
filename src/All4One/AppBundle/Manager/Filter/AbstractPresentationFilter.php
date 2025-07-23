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

use Common\CoreBundle\Presentation\AdvancedOfferFilter as Presentation;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

/**
 * Class AbstractPresentationFilter.
 */
abstract class AbstractPresentationFilter
{
    /**
     * @var int
     */
    protected $filterParameterCounter = 0;

    /**
     * @var int
     */
    protected $filterAliasCounter = 0;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * AbstractPresentationFilter constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Presentation $presentation
     *
     * @return mixed
     */
    abstract public function filterQueryBuilderByPresentation(QueryBuilder $queryBuilder, Presentation $presentation);

    /**
     * @param QueryBuilder $queryBuilder
     * @param string       $dictionaryClass
     * @param Collection   $collection
     */
    protected function filterQueryBuilderByAssociation(QueryBuilder $queryBuilder, string $dictionaryClass, Collection $collection)
    {
        if ($collection->isEmpty()) {
            return;
        }

        $aliasRel = $this->getFilterAlias();
        $aliasDic = $this->getFilterAlias();
        $param = $this->getFilterParameter();

        $ra = $queryBuilder->getRootAliases();
        $ra = reset($ra);

        $ra = ('fc' == $ra) ? 'ecv' : $ra;

        $queryBuilder
            ->innerJoin(
                $ra.'.dictionaryRelations',
                $aliasRel
            )
            ->innerJoin(
                $aliasRel.'.dictionary',
                $aliasDic,
                'WITH',
                '('.$aliasDic.' INSTANCE OF '.$dictionaryClass.')'
            )
            ->andWhere($aliasRel.'.dictionary IN (:'.$param.')')
            ->setParameter($param, $collection)
        ;
    }

    /**
     * @return string
     */
    protected function getFilterParameter(): string
    {
        ++$this->filterParameterCounter;

        return 'filterparameter'.$this->filterParameterCounter;
    }

    /**
     * @return string
     */
    protected function getFilterAlias(): string
    {
        ++$this->filterAliasCounter;

        return 'aaa'.$this->filterAliasCounter;
    }

    protected function resetFilterGeneration()
    {
        $this->filterParameterCounter = 0;
        $this->filterAliasCounter = 0;
    }
}
