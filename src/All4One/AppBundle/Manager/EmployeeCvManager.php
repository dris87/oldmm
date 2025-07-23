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

use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvEducation;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvExperience;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\Offer\OfferCandidate;
use Common\CoreBundle\Enumeration\Offer\OfferCandidateStatusEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Snappy\GeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class EmployeeCvManager.
 */
class EmployeeCvManager extends AbstractManager
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var OfferManager
     */
    private $offerManager;

    /**
     * EmployeeCvManager constructor.
     *
     * @param ObjectManager   $objectManager
     * @param OfferManager    $offerManager
     * @param KernelInterface $kernel
     */
    public function __construct(ObjectManager $objectManager, OfferManager $offerManager, KernelInterface $kernel)
    {
        $this->objectManager = $objectManager;
        $this->offerManager = $offerManager;
        $this->kernel = $kernel;
    }

    /**
     * @param EmployeeCv $cv
     *
     * @return EmployeeCv
     */
    public function save(EmployeeCv $cv)
    {
        $this->objectManager->persist($cv);
        $this->objectManager->flush();

        return $cv;
    }

    /**
     * @param EmployeeCvEducation $education
     *
     * @return EmployeeCvEducation
     */
    public function saveEducation(EmployeeCvEducation $education)
    {
        $this->objectManager->persist($education);
        $this->objectManager->flush();

        return $education;
    }

    /**
     * @param EmployeeCvEducation $education
     */
    public function deleteEducation(EmployeeCvEducation $education)
    {
        $this->objectManager->remove($education);
        $this->objectManager->flush();
    }

    /**
     * @param EmployeeCvExperience $experience
     *
     * @return EmployeeCvExperience
     */
    public function saveExperience(EmployeeCvExperience $experience)
    {
        $this->objectManager->persist($experience);
        $this->objectManager->flush();

        return $experience;
    }

    /**
     * @param EmployeeCvExperience $experience
     */
    public function deleteExperience(EmployeeCvExperience $experience)
    {
        $this->objectManager->remove($experience);
        $this->objectManager->flush();
    }

    /**
     * @param $body
     * @param GeneratorInterface $pdf
     * @param bool               $header
     * @param bool               $footer
     * @param $pdfOptions
     * @param string $fileName
     *
     * @return Response
     */
    public function generatePdf($body, GeneratorInterface $pdf, $header, $footer, $pdfOptions, string $fileName)
    {
        return new Response(
            $pdf->getOutputFromHtml($body, array_merge($pdfOptions, [
                'header-html' => $header,
                'footer-html' => $footer,
                'javascript-delay' => 1000,
                'stop-slow-scripts' => false,
                'disable-smart-shrinking' => false,
                'dpi' => 100,
                'page-size' => 'A4',
                'images' => true,
                'load-media-error-handling' => 'abort',
                'enable-local-file-access' => true,
                'print-media-type' => true,
                'image-dpi' => 1000,
                'image-quality' => 300,
            ])),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$fileName.'.pdf"',
            ]
        );
    }

    /**
     * @param EmployeeCv $employeeCv
     * @param Offer      $offer
     *
     * @return object|OfferCandidate|null
     */
    public function apply(EmployeeCv $employeeCv, Offer $offer)
    {
        $offerCandidateRepository = $this->objectManager->getRepository('CommonCoreBundle:Offer\OfferCandidate');

        if (null == ($offerCandidate = $offerCandidateRepository->findOneBy(['offer' => $offer, 'employeeCv' => $employeeCv]))) {
            $offerCandidate = new OfferCandidate();
            $offerCandidate->setEmployeeCv($employeeCv);
            $offerCandidate->setOffer($offer);
        }

        $offerCandidate->setDirect(true);
        $offerCandidate->setStatus(OfferCandidateStatusEnum::create(OfferCandidateStatusEnum::NEW));

        $this->offerManager->saveCandidate($offerCandidate);

        return $offerCandidate;
    }
}
