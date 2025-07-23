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

namespace Common\CoreBundle\Enumeration\Migration;

use Common\CoreBundle\Enumeration\AbstractEnum;

/**
 * Class MigrationFrequencyEnum.
 */
class MigrationFrequencyEnum extends AbstractEnum
{
    /**
     * The migration will daily.
     */
    const DAILY = 0;

    /**
     * The migration will run weekly.
     */
    const WEEKLY = 1;

    /**
     * The migration will run monthly.
     */
    const MONTHLY = 2;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::DAILY,
            static::WEEKLY,
            static::MONTHLY,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::DAILY => 'Naponta 1x',
            static::WEEKLY => 'Hetente 1x (hétfő 00:01)',
            static::MONTHLY => 'Havonta 1x (01 00:01)',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::DAILY => 'success',
            static::WEEKLY => 'info',
            static::MONTHLY => 'warning',
        ];
    }
}
