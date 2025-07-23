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

namespace Common\CoreBundle\Entity\Util;

use Common\CoreBundle\Entity\MappedSuperclassBase;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\User\User;
use Common\CoreBundle\Enumeration\Util\TrackedTokenStatusEnum;
use Common\CoreBundle\Enumeration\Util\TrackedTokenTypeEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class TrackedToken.
 *
 * @ORM\Entity(repositoryClass="Common\CoreBundle\Doctrine\Repository\Util\TrackedTokenRepository")
 * @ORM\Table(name="tracked_token")
 */
class TrackedToken extends MappedSuperclassBase
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $token;

    /**
     * Data associated with the token.
     *  EX: - User-id for email verification.
     *      - Offer id, if you want to track a link for a specific offer,
     *        and u shared on facebook, so we can see how much visit came from that link.
     *
     * @var array
     *
     * @ORM\Column(type="json")
     */
    protected $data = [];

    /**
     * @var int
     *
     * @ORM\Column(type="tracked_token_status_enum", options={"default" = 1})
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(type="tracked_token_type_enum", options={"default" = 0})
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    private $expireDate;

    /**
     * Number of times it was used.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $usedCounter = 0;

    /**
     * Maximum of used counter.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxUseTimes;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $userId = null;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\User\User")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=true,
     *     onDelete="CASCADE",
     * )
     */
    private $user = null;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $offerId;

    /**
     * @var Offer
     *
     * @ORM\ManyToOne(targetEntity="Common\CoreBundle\Entity\Offer\Offer")
     * @ORM\JoinColumn(
     *     referencedColumnName="id",
     *     nullable=true,
     *     onDelete="CASCADE",
     * )
     */
    private $offer;

    /**
     * TrackedToken constructor.
     */
    public function __construct()
    {
        $this->status = TrackedTokenStatusEnum::create(TrackedTokenStatusEnum::ACTIVE);
        $this->setToken(self::getRandomToken());
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * Returns a random token of a given length.
     *
     * @param int $length
     *
     * @return string
     */
    public static function getRandomToken(int $length = 20): string
    {
        $token = random_bytes($length);

        return str_replace('/', '.', base64_encode($token));
    }

    /**
     * @param TrackedTokenStatusEnum $status
     *
     * @return $this
     */
    public function setStatus(TrackedTokenStatusEnum $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \Biplane\EnumBundle\Enumeration\EnumInterface|int|static
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param TrackedTokenTypeEnum $type
     *
     * @return $this
     */
    public function setType(TrackedTokenTypeEnum $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return \Biplane\EnumBundle\Enumeration\EnumInterface|int|static
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \DateTime
     */
    public function getExpireDate(): ? \DateTime
    {
        return $this->expireDate;
    }

    /**
     * @param \DateTime $expireDate
     */
    public function setExpireDate(\DateTime $expireDate): void
    {
        $this->expireDate = $expireDate;
    }

    /**
     * @return int
     */
    public function getUsedCounter(): ? int
    {
        return $this->usedCounter;
    }

    /**
     * @param int $usedCounter
     */
    public function setUsedCounter(int $usedCounter): void
    {
        $this->usedCounter = $usedCounter;
    }

    /**
     * @param int $num
     */
    public function increaseUsedCounter(int $num = 1): void
    {
        $this->usedCounter += $num;
    }

    /**
     * @return int
     */
    public function getMaxUseTimes(): ? int
    {
        return $this->maxUseTimes;
    }

    /**
     * @param int $maxUseTimes
     */
    public function setMaxUseTimes(int $maxUseTimes): void
    {
        $this->maxUseTimes = $maxUseTimes;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return array_unique($this->data);
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getDataByKey($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function appendData(string $key, string $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $this->userId = $user->getId();

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $offerId
     *
     * @return $this
     */
    public function setOfferId($offerId)
    {
        $this->offerId = $offerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOfferId()
    {
        return $this->offerId;
    }

    /**
     * @param Offer $offer
     *
     * @return $this
     */
    public function setOffer(Offer $offer)
    {
        $this->offer = $offer;
        $this->offerId = $offer->getId();

        return $this;
    }

    /**
     * @return Offer
     */
    public function getOffer()
    {
        return $this->offer;
    }
}
