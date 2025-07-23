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

namespace Common\CoreBundle\Enumeration\Migration\MigrationAlgorithmEnum;

use Common\CoreBundle\Enumeration\Migration\MigrationAlgorithmEnum;

/**
 * Class MigrationAlgorithmHumanCentrumEnum.
 */
class MigrationAlgorithmHumanCentrumEnum extends MigrationAlgorithmEnum
{
    /**
     * @return bool
     */
    public function hasReference()
    {
        return true;
    }
}
