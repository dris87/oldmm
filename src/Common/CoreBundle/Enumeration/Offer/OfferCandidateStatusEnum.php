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
 * Class OfferCandidateStatusEnum.
 */
class OfferCandidateStatusEnum extends AbstractEnum
{
    const NEW = 0;
    const VIEWED = 1;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::NEW,
            static::VIEWED,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::NEW => 'label.candidate.new',
            static::VIEWED => 'label.candidate.viewed',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::VIEWED => 'warning',
            static::NEW => 'success',
        ];
    }
}
