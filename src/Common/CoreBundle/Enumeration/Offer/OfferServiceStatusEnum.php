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
 * Class OfferServiceStatusEnum.
 */
class OfferServiceStatusEnum extends AbstractEnum
{
    const INACTIVE = 0;
    const IN_CART = 1;
    const ACTIVE = 2;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::INACTIVE,
            static::IN_CART,
            static::ACTIVE,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::INACTIVE => 'inactive',
            static::IN_CART => 'in_cart',
            static::ACTIVE => 'active',
        ];
    }
}
