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

namespace Common\CoreBundle\Manager\Migration\Interfaces;

/**
 * Interface MigrationAlgorithmInterface.
 */
interface MigrationAlgorithmInterface
{
    /**
     * MigrationAlgorithmInterface constructor.
     *
     * @param array $array
     */
    public function __construct(array $array);

    /**
     * @param $array
     *
     * @return bool
     */
    public function execute(): bool;
}
