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

namespace All4One\AppBundle\Manager;

use Common\CoreBundle\Doctrine\Repository\Dictionary\DictionaryRepository;
use Common\CoreBundle\Entity\Dictionary\Dictionary;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Firm\Firm;
use Common\CoreBundle\Entity\Firm\FirmCv;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\Offer\OfferCandidate;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Common\CoreBundle\Enumeration\Offer\OfferCandidateStatusEnum;
use Common\CoreBundle\Manager\Firm\FirmBalanceManager;
use Common\CoreBundle\Manager\Util\BisNodeManager;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class FirmManager.
 */
class FirmManager
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var
     */
    private $bisNodeManager;

    /**
     * @var OfferManager
     */
    private $offerManager;

    /**
     * @var FirmBalanceManager
     */
    private $balanceManager;

    /**
     * FirmManager constructor.
     *
     * @param ObjectManager      $objectManager
     * @param BisNodeManager     $bisNodeManager
     * @param OfferManager       $offerManager
     * @param FirmBalanceManager $balanceManager
     */
    public function __construct(
        ObjectManager $objectManager,
        BisNodeManager $bisNodeManager,
        OfferManager $offerManager,
        FirmBalanceManager $balanceManager
    ) {
        $this->objectManager = $objectManager;
        $this->bisNodeManager = $bisNodeManager;
        $this->offerManager = $offerManager;
        $this->balanceManager = $balanceManager;
    }

    /**
     * @param Firm $firm
     *
     * @throws \Exception
     *
     * @return Firm
     */
    public function save(Firm $firm)
    {
        $this->objectManager->persist($firm);
        $this->objectManager->flush();

        return $firm;
    }

    /**
     * @param string $taxNumber
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getInfoFromTaxNumber(string $taxNumber)
    {
        $bisNodeData = $this->bisNodeManager->getFirmDataFromTaxNumber($taxNumber);
        if (!is_object($bisNodeData)) {
            throw new \Exception('Invalid Tax Number!');
        }

        /** @var DictionaryRepository $dictionaryRepository */
        $dictionaryRepository = $this->objectManager->getRepository(Dictionary::class);
        $location = $dictionaryRepository->findOneByCityAndZip($bisNodeData->RegAddress->City, $bisNodeData->RegAddress->Zip);

        $firmData = [
            'name' => $bisNodeData->ShortName,
            'street' => $bisNodeData->RegAddress->StreetNo,
            'nameLong' => $bisNodeData->RegName,
            'location' => $location,
        ];

        return $firmData;
    }

    /**
     * @param Firm                $firm
     * @param EmployeeCv          $employeeCv
     * @param OfferCandidate|null $offerCandidate
     *
     * @return FirmCv|object|null
     */
    public function unlockCv(Firm $firm, EmployeeCv $employeeCv, ?OfferCandidate $offerCandidate)
    {
        $firmCvRepository = $this->objectManager->getRepository('CommonCoreBundle:Firm\FirmCv');

        if (null !== ($firmCv = $firmCvRepository->findOneBy(['firm' => $firm, 'employeeCv' => $employeeCv]))) {
            return $firmCv;
        }

        $firmCv = new FirmCv();
        $firmCv->setFirm($firm);
        $firmCv->setEmployeeCv($employeeCv);
        if (null !== $offerCandidate) {
            $firmCv->setOfferCandidate($offerCandidate);
        }

        $this->objectManager->persist($firmCv);
        $this->objectManager->flush();

        $this->balanceManager->resolveServiceCount(FirmPackageServiceEnum::create(FirmPackageServiceEnum::CV_COUNT));

        return $firmCv;
    }

    /**
     * @param EmployeeCv $employeeCv
     * @param Offer      $offer
     *
     * @return object|OfferCandidate|null
     */
    public function saveCandidate(EmployeeCv $employeeCv, Offer $offer)
    {
        $offerCandidateRepository = $this->objectManager->getRepository('CommonCoreBundle:Offer\OfferCandidate');

        if (null == ($offerCandidate = $offerCandidateRepository->findOneBy(['offer' => $offer, 'employeeCv' => $employeeCv]))) {
            $offerCandidate = new OfferCandidate();
            $offerCandidate->setEmployeeCv($employeeCv);
            $offerCandidate->setOffer($offer);
        }

        $offerCandidate->setPaired(true);
        $offerCandidate->setStatus(OfferCandidateStatusEnum::create(OfferCandidateStatusEnum::NEW));

        $this->offerManager->saveCandidate($offerCandidate);

        return $offerCandidate;
    }
}
