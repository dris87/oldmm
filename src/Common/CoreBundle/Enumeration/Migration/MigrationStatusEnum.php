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
 * Class MigrationStatusEnum.
 */
class MigrationStatusEnum extends AbstractEnum
{
    /**
     * The migration is waiting for the next execution.
     */
    const WAITING = 0;

    /**
     * The migration is still executing.
     */
    const RUNNING = 1;

    /**
     * The migration will not execute on schedule.
     */
    const DISABLED = 2;

    /**
     * The last execution failed for some reason.
     */
    const FAILED = 3;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::WAITING,
            static::RUNNING,
            static::DISABLED,
            static::FAILED,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::WAITING => 'Várakozik',
            static::RUNNING => 'Fut',
            static::DISABLED => 'Kikapcsolva',
            static::FAILED => 'Meghiusult',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::WAITING => 'success',
            static::RUNNING => 'info',
            static::DISABLED => 'warning',
            static::FAILED => 'danger',
        ];
    }
}
