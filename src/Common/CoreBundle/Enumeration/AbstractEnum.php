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

namespace Common\CoreBundle\Enumeration;

use Biplane\EnumBundle\Enumeration\Enum;
use Biplane\EnumBundle\Exception\InvalidEnumArgumentException;

/**
 * Class AbstractEnum.
 */
abstract class AbstractEnum extends Enum
{
    /**
     * @return array
     */
    public static function getAllEnums()
    {
        $result = [];

        foreach (static::getPossibleValues() as $value) {
            $result[] = static::create($value);
        }

        return $result;
    }

    /**
     * @param $readable
     *
     * @return \Biplane\EnumBundle\Enumeration\EnumInterface|static
     */
    public static function createByReadable($readable)
    {
        $value = array_search($readable, static::getReadables());

        if (false === $value) {
            throw new InvalidEnumArgumentException($readable);
        }

        return static::create($value);
    }

    /**
     * @return string
     */
    public function getLabelType()
    {
        return static::getLabelTypeFor($this->getValue());
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public static function getLabelTypeFor($value)
    {
        if (!static::isAcceptableValue($value)) {
            throw new InvalidEnumArgumentException($value);
        }

        $labelRepresentations = static::getLabels();

        return $labelRepresentations[$value];
    }
}
