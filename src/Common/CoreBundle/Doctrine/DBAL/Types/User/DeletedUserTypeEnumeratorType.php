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
use Common\CoreBundle\Enumeration\User\DeletedUserTypeEnum;

/**
 * Class DeletedUserTypeEnumeratorType.
 */
class DeletedUserTypeEnumeratorType extends AbstractEnumeratorType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'deleted_user_type_enum';
    }

    /**
     * @param int $value
     *
     * @return DeletedUserTypeEnum|static
     */
    protected function createEntity($value)
    {
        return DeletedUserTypeEnum::create($value);
    }
}
