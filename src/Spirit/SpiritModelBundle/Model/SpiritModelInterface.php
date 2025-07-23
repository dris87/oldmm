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

namespace Spirit\SpiritModelBundle\Model;

/**
 * @author sipee
 */
interface SpiritModelInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return int|null
     */
    public function getOriginalId();

    /**
     * @param int|null $original_id
     *
     * @return SpiritModelInterface
     */
    public function setOriginalId($original_id);

    /**
     * @return mixed|null
     */
    public function getOriginal();

    /**
     * @param SpiritModelInterface|null $original
     *
     * @return SpiritModelInterface
     */
    public function setOriginal($original);

    /**
     * @return int|null
     */
    public function getLastSpiritId();

    /**
     * @param int|null $last_spirit_id
     *
     * @return SpiritModelInterface
     */
    public function setLastSpiritId($last_spirit_id);

    /**
     * @return SpiritModelInterface|null
     */
    public function getLastSpirit();

    /**
     * @param SpiritModelInterface|null $last_spirit
     *
     * @return SpiritModelInterface
     */
    public function setLastSpirit($last_spirit);

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime $datetime
     *
     * @return SpiritModelInterface
     */
    public function setUpdatedAt(\DateTime $datetime);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param \DateTime $datetime
     *
     * @return SpiritModelInterface
     */
    public function setCreatedAt(\DateTime $datetime);
}
