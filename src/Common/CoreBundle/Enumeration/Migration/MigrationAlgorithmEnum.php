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

use Biplane\EnumBundle\Exception\InvalidEnumArgumentException;
use Common\CoreBundle\Enumeration\AbstractEnum;

abstract class MigrationAlgorithmEnum extends AbstractEnum
{
    /**
     * To handle humancentrum and minddiak feeds.
     */
    const HUMNANCENTRUM = 0;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::HUMNANCENTRUM,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::HUMNANCENTRUM => 'HumanCentrum',
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
        $class = self::class.'\\MigrationAlgorithm'.$class.'Enum';

        return new $class($value);
    }
}
