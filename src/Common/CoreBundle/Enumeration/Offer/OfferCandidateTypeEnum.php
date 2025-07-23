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

namespace Common\CoreBundle\Enumeration\Offer;

use Common\CoreBundle\Enumeration\AbstractEnum;

/**
 * Class OfferCandidateTypeEnum.
 */
class OfferCandidateTypeEnum extends AbstractEnum
{
    const DIRECT = 0; //'direct apply';
    const MOVED = 1; //'on bo move';

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::DIRECT,
            static::MOVED,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::DIRECT => 'label.candidate.direct',
            static::MOVED => 'label.candidate.moved',
        ];
    }
}
