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

namespace Common\CoreBundle\Enumeration\Firm;

use Common\CoreBundle\Enumeration\AbstractEnum;

/**
 * Class FirmStatusEnum.
 */
class FirmStatusEnum extends AbstractEnum
{
    const INACTIVE = 0;
    const ACTIVE = 1;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::INACTIVE,
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
            static::ACTIVE => 'active',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::INACTIVE => 'warning',
            static::ACTIVE => 'success',
        ];
    }
}
