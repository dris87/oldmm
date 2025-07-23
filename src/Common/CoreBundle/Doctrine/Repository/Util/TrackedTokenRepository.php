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

namespace Common\CoreBundle\Doctrine\Repository\Util;

use Common\CoreBundle\Enumeration\Util\TrackedTokenStatusEnum;
use Doctrine\ORM\EntityRepository;

/**
 * Class TrackedTokenRepository.
 */
class TrackedTokenRepository extends EntityRepository
{
    /**
     * @param string $token
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return mixed
     */
    public function loadByToken(string $token)
    {
        return $this->createQueryBuilder('tt')
            ->where('tt.token = :token')
            ->andWhere('tt.maxUseTimes > tt.usedCounter')
            ->andWhere('tt.expireDate > :now')
            ->andWhere('tt.status <> :status')
            ->setParameter('token', $token)
            ->setParameter('now', '\'CURRENT_TIMESTAMP()\'')
            ->setParameter('status', TrackedTokenStatusEnum::create(TrackedTokenStatusEnum::USED)->getValue())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
