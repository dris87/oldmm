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

namespace Common\CoreBundle\Enumeration\Offer;

use Common\CoreBundle\Enumeration\AbstractEnum;

/**
 * Offer status indicators.
 *
 * When a firm colleague creates an offer for a firm
 * he/she has 3 buttons:
 *          - Preview : No special status. We save the offer
 *                      with status "SAVED" and then show a preview
 *          - Save as draft: The offer is saved, ( but not validated )
 *                           with status "SAVED"
 *          - Upload: The offer is validated and saved
 *                           with status "UNDER_CONSIDERATION"
 *
 * Class OfferStatusEnum
 */
class OfferStatusEnum extends AbstractEnum
{
    /**
     * If saved, it is still no listed
     * The owner can modify it.
     * Note: On SAVED status the offer is not validated yet.
     */
    const SAVED = 0;

    /**
     * The offer was validated and waits for BO approval.
     *
     * BO can accept(ACTIVE) it or deny it(DENIED)
     */
    const UNDER_CONSIDERATION = 1;

    /**
     * The offer was not accepted by the BO,
     * so it is still not listed. But the user can edit it
     * and again "preview", "save" or upload it.
     */
    const DENIED = 2;

    /**
     * The offer is accepted by the BO
     * but not available yet, cuz
     * the available date.
     */
    const WAITING = 3;

    /**
     * If the offer is inactive, it is not listed
     * No option to modify for the owner
     * since it is approved by the BO.
     */
    const INACTIVE = 4;

    /**
     * The offer is accepted by the BO and listed.
     */
    const ACTIVE = 5;

    /**
     * In case an offer is expired!
     */
    const EXPIRED = 6;

    /**
     * In case the offer was migrated from other sources.
     */
    const MIGRATED = 7;

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        return [
            static::SAVED,
            static::UNDER_CONSIDERATION,
            static::DENIED,
            static::WAITING,
            static::INACTIVE,
            static::ACTIVE,
            static::EXPIRED,
            static::MIGRATED,
        ];
    }

    /**
     * @return array
     */
    public static function getReadables()
    {
        return [
            static::SAVED => 'label.saved',
            static::UNDER_CONSIDERATION => 'label.under_consideration',
            static::DENIED => 'label.denied',
            static::WAITING => 'label.offer.waiting',
            static::INACTIVE => 'label.inactive',
            static::ACTIVE => 'label.active',
            static::EXPIRED => 'label.expired',
            static::MIGRATED => 'label.migrated',
        ];
    }

    /**
     * @return array
     */
    public static function getLabels()
    {
        return [
            static::SAVED => 'warning',
            static::UNDER_CONSIDERATION => 'info',
            static::DENIED => 'danger',
            static::WAITING => 'info',
            static::INACTIVE => 'default',
            static::ACTIVE => 'success',
            static::EXPIRED => 'warning',
            static::MIGRATED => 'warning',
        ];
    }
}
