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
 * Autocomplete descriptor for fetching a location by zip or city.
 *
 * Class DicLocationAutocompleteDescriptor
 */
class DicLocationAutocompleteDescriptor extends BaseAutocompleteDescriptor
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function buildQuery(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->join('o.zip', 'z')
            ->join('o.city', 'c')
            ->groupBy('c');
    }
}
