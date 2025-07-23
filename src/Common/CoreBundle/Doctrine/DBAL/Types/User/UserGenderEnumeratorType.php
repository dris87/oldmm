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

namespace Common\CoreBundle\Doctrine\DBAL\Types\User;

use Common\CoreBundle\Doctrine\DBAL\Types\AbstractEnumeratorType;
use Common\CoreBundle\Enumeration\User\UserGenderEnum;

/**
 * Class UserGenderEnumeratorType.
 */
class UserGenderEnumeratorType extends AbstractEnumeratorType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'user_gender_enum';
    }

    /**
     * @param int $value
     *
     * @return static|UserGenderEnum
     */
    protected function createEntity($value)
    {
        return UserGenderEnum::create($value);
    }
}
