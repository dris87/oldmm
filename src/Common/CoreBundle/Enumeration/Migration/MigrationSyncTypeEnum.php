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
 * Class MigrationSyncTypeEnum.
 */
class MigrationSyncTypeEnum extends AbstractEnum
{
    /**
     * Complete sync with the feed.
     * Remove if not in list anymore,
     * Insert if new
     * Update if modified.
     */
    const COMPLETE = 0;

    /**
     * Half sync with the feed.
     * Insert if new
     * Update if modified.
     */
    const HALF = 1;

    /**
     * Half sync with the feed.
     * Insert if new.
     */
    const INSERT_ONLY = 2;

    /**
     * Half sync with the feed.
     * Update if modified.
     */
    const UPDATE_ONLY = 3;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::COMPLETE,
            static::HALF,
            static::INSERT_ONLY,
            static::UPDATE_ONLY,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::COMPLETE => 'Teljes(törlés, frissítés, hozzáadás)',
            static::HALF => 'Fél(frissítés, hozzáadás)',
            static::INSERT_ONLY => 'Csak hozzáadás',
            static::UPDATE_ONLY => 'Csak frissítés',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::COMPLETE => 'danger',
            static::HALF => 'success',
            static::INSERT_ONLY => 'warning',
            static::UPDATE_ONLY => 'info',
        ];
    }
}
