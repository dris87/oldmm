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

namespace Common\CoreBundle\Manager\Migration\Managers;

use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;

/**
 * This class will be responsible for migrating offers
 * from different sources.
 *
 * Class OfferMigrationManager
 */
class OfferMigrationManager extends MigrationManager
{
    /**
     * @return bool
     */
    public function execute(): bool
    {
        $this->fillEntity();

        return true;
    }

    /**
     * @throws \Exception
     *
     * @return bool
     */
    public function analyzeData(): bool
    {
        //throw new \Exception('Not recognized data structure.');

        return true;
    }

    /**
     * Fill the appropriate.
     */
    protected function fillEntity()
    {
        $offer = new Offer();
        $offer->setStatus(OfferStatusEnum::create(OfferStatusEnum::MIGRATED));
        $offer->setTitle($this->data['title']);

        // Fill other fields as well!

        // save it
    }
}
