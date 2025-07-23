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

namespace Common\CoreBundle\Doctrine\DBAL\Types\Development\Documentation;

use Common\CoreBundle\Doctrine\DBAL\Types\AbstractEnumeratorType;
use Common\CoreBundle\Enumeration\Development\Documentation\DocumentationItemCodeTypeEnum;

/**
 * Class DocumentationItemCodeTypeEnumeratorType.
 */
class DocumentationItemCodeTypeEnumeratorType extends AbstractEnumeratorType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'development_documentation_item_code_type_enum';
    }

    /**
     * @param int $value
     *
     * @return DocumentationItemCodeTypeEnum|static
     */
    protected function createEntity($value)
    {
        return DocumentationItemCodeTypeEnum::create($value);
    }
}
