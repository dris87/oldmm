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

namespace All4One\AppBundle\Autocomplete;

use All4One\AutocompleteBundle\Autocomplete\BaseAutocompleteDescriptor;
use Doctrine\ORM\QueryBuilder;

/**
 * Autocomplete descriptor for fetching a city or country.
 *
 * Class DicCityCountyAutocompleteDescriptor
 */
class DicCityCountyAutocompleteDescriptor extends BaseAutocompleteDescriptor
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function buildQuery(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->where('o INSTANCE OF Common\CoreBundle\Entity\Dictionary\DicCity OR o INSTANCE OF Common\CoreBundle\Entity\Dictionary\DicCounty')
            ->orderBy('o.value', 'ASC');
    }
}
