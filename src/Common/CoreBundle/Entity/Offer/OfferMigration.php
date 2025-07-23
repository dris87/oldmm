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

namespace Common\CoreBundle\Entity\Offer;

use Common\CoreBundle\Entity\Firm\Firm;
use Common\CoreBundle\Entity\Migration\Migration;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OfferMigration.
 *
 * @ORM\Entity
 */
class OfferMigration extends Migration
{
    /**
     * @var Firm
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Firm\Firm")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE",
     * )
     */
    private $firm;

    /**
     * OfferMigration constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return Firm
     */
    public function getFirm()
    {
        return $this->firm;
    }

    /**
     * @param Firm $firm
     */
    public function setFirm(Firm $firm): void
    {
        $this->firm = $firm;
    }
}
