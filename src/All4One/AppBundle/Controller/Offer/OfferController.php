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

namespace All4One\AppBundle\Controller\Offer;

use Common\CoreBundle\Doctrine\Repository\Offer\OfferCandidateRepository;
use Common\CoreBundle\Doctrine\Repository\Offer\OfferRepository;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\Offer\OfferCandidate;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvStatusEnum;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage firm data.
 *
 * @Route("/allas")
 */
class OfferController extends AbstractController
{
    /**
     * @Route("/{slug}", name="show_offer")
     * @Method("GET")
     *
     * @param Offer $offer
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Response
     */
    public function show(Offer $offer): Response
    {
        $em = $this->getDoctrine();
        /** @var OfferRepository $offerRepository */
        $offerRepository = $em->getRepository('CommonCoreBundle:Offer\Offer');

        $data = [];
        $data['owner'] = false;
        if (
            is_object($this->getUser()) &&
            $this->getUser()->isFirmColleague() &&
            $offer->getFirm() === $this->getUser()->getFirm()
        ) {
            $data['owner'] = true;
        } elseif (
            $offer->getStatus() != OfferStatusEnum::create(OfferStatusEnum::INACTIVE) &&
            $offer->getStatus() != OfferStatusEnum::create(OfferStatusEnum::ACTIVE)
        ) {
            // our offer is not visible publicly
            $offer = null;
        } else {
            /** @var OfferCandidateRepository $candidateRepository */
            $candidateRepository = $this->getDoctrine()->getRepository(OfferCandidate::class);
            $data['applied'] = (!empty($this->getUser()) && $this->getUser()->isEmployee())
                ? !empty($candidateRepository->isDirectOfferCandidate($offer, $this->getUser()))
                : false;

            $now = new \DateTime();

            $data['expired'] = $offer->getExpireDate() < $now;

            $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
            if (
                is_object($this->getUser()) &&
                $this->getUser()->isEmployee()
            ) {
                $data['cvs'] = $employeeCvRepository->findBy(['status' => EmployeeCvStatusEnum::create(EmployeeCvStatusEnum::ACTIVE), 'employeeId' => $this->getUser()->getId()]);
            }
        }

       // És helyette használj üres tömböt:
        $data['related_offers'] = [];

        if ($offer) {
            try {
                //$relatedOffersPaginator = $offerRepository->findLatestRelatedTile(1, $offer);
                //$data['related_offers'] = iterator_to_array($relatedOffersPaginator->getCurrentPageResults());
                $data['related_offers'] = [];
            } catch (\Exception $e) {
                // Ha bármilyen hiba van, üres tömböt adunk
                $data['related_offers'] = [];
            }
        } else {
            // Ha nincs offer, akkor nincs kapcsolódó hirdetés sem
            $data['related_offers'] = [];
        }
                
        $data['offer'] = $offer;

        return $this->render('pages/offer/show.html.twig', $data);
    }
}
