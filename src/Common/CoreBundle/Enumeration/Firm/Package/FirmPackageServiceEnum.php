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

namespace Common\CoreBundle\Enumeration\Firm\Package;

use Biplane\EnumBundle\Exception\InvalidEnumArgumentException;
use Common\CoreBundle\Enumeration\AbstractEnum;

/**
 * Class FirmPackageServiceEnum.
 */
abstract class FirmPackageServiceEnum extends AbstractEnum
{
    const CV_COUNT = 0;
    const MATCH = 1;
    const DATABASE = 2;
    const OFFER_EXALTATION = 3;
    const ADVANCE_FILTER = 4;
//    const HR_MANAGEMENT = 5;
//    const MARKETING = 6;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::CV_COUNT,
            static::MATCH,
            static::DATABASE,
            static::OFFER_EXALTATION,
            static::ADVANCE_FILTER,
//            static::HR_MANAGEMENT,
//            static::MARKETING,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::CV_COUNT => 'cv_count',
            static::MATCH => 'match',
            static::DATABASE => 'database',
            static::OFFER_EXALTATION => 'offer_exaltation',
            static::ADVANCE_FILTER => 'advance_filter',
//            static::HR_MANAGEMENT => 'hr_management',
//            static::MARKETING => 'marketing',
        ];
    }

    abstract public function hasReference();

    /**
     * @param $value
     *
     * @return \Biplane\EnumBundle\Enumeration\EnumInterface|mixed|static
     */
    public static function create($value)
    {
        if (!static::isAcceptableValue($value)) {
            throw new InvalidEnumArgumentException($value);
        }

        $class = self::getReadables();
        $class = implode(array_map('ucfirst', preg_split('/[-_]/', $class[$value])));
        $class = self::class.'\\FirmPackageService'.$class.'Enum';

        return new $class($value);
    }
}
