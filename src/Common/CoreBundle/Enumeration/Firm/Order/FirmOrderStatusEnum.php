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

namespace Common\CoreBundle\Enumeration\Firm\Order;

use Common\CoreBundle\Enumeration\AbstractEnum;

/**
 * Class FirmOrderStatusEnum.
 */
class FirmOrderStatusEnum extends AbstractEnum
{
    const INIT = 0;
    const PAID = 1;
    const CANCELLED = 2;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::INIT,
            static::PAID,
            static::CANCELLED,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::INIT => 'inactive',
            static::PAID => 'active',
            static::CANCELLED => 'cancelled',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::INIT => 'info',
            static::PAID => 'success',
            static::CANCELLED => 'danger',
        ];
    }
}
