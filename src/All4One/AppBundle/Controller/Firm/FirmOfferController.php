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

namespace All4One\AppBundle\Controller\Firm;


use All4One\AppBundle\Form\Employee\AdvancedCvSearchType;
use All4One\AppBundle\Form\Offer\ManageType;
use All4One\AppBundle\Manager\EmailManager;
use All4One\AppBundle\Manager\Filter\AdvancedCvQueryFilter;
use All4One\AppBundle\Manager\OfferManager;
use All4One\AppBundle\Traits\ControllerUtilsTrait;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Offer\OfferStatusEnum;
use Common\CoreBundle\Pagination\CompositePagerfantaAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/cegfiok/hirdetesek")
 * @Security("has_role('ROLE_COLLEAGUE')")
 */
class FirmOfferController extends AbstractController
{
    use ControllerUtilsTrait;


    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var OfferManager
     */
    private $offerManager;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * FirmOfferController constructor.
     *
     * @param TranslatorInterface $translator
     * @param OfferManager        $offerManager
     * @param EmailManager        $emailManager
     */
    public function __construct(TranslatorInterface $translator, OfferManager $offerManager, EmailManager $emailManager)
    {     
        $this->translator = $translator;
        $this->offerManager = $offerManager;
        $this->emailManager = $emailManager;  

    }

    /**
     * @Route("/", defaults={"page" = "1" }, name="firm_offer_index")
     * @Route("/", defaults={"page" = "1" }, name="firm_offer_list")
     * @Route("/oldal/{page}", requirements={"page" = "[1-9]\d*"}, name="firm_list_offers_paginated")
     * @Method("GET")
     *
     * @param int $page
     *
     * @return Response
     */
    public function index(int $page): Response
    {
        $em = $this->getDoctrine();
        $offerRepository = $em->getRepository('CommonCoreBundle:Offer\Offer');

        $offers = $offerRepository->findAvailableFirmTiles($this->getUser()->getFirm()->getId(), $page);

        return $this->render('pages/firm/offer/list.html.twig', ['offers' => $offers]);
    }

    /**
     * @Route("/jelentkezok/{id}", defaults={"page" = "1" }, name="firm_offer_candidates_list")
     * @Route("/jelentkezok/{id}/oldal/{page}", requirements={"page" = "[1-9]\d*"}, name="firm_offer_candidates_list_paginated")
     * @Method("GET")
     *
     * @param Request               $request
     * @param Offer                 $offer
     * @param int                   $page
     * @param AdvancedCvQueryFilter $filterService
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return Response
     */
    public function offerCandidates(Request $request, Offer $offer, int $page, AdvancedCvQueryFilter $filterService): Response
    {
        if (null !== $offer) {
            $this->denyAccessUnlessGranted('ROLE_OFFER_VIEW_CANDIDATES', $offer);
        }
        $orderRepo = $this->getDoctrine()->getRepository('CommonCoreBundle:Firm\Order\FirmOrder');
        $orders = $orderRepo->findWithLivePackageByFirm($this->getUser()->getFirm());
        
        $em = $this->getDoctrine();
        $employeeCvRepo = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        $candidateQuery = $employeeCvRepo->createQueryOfCandidatesBy($offer);
        $matchedQuery = $employeeCvRepo->createQueryOfMatchesBy($offer);

        $advanced_cv_search_form = $this->container->get('form.factory')->createNamed(
            'search',
            AdvancedCvSearchType::class, null,
            [
                'action' => $this->generateUrl('firm_offer_candidates_list', ['id' => $offer->getId()]),
                'method' => 'GET',
            ]
        );
        $advanced_cv_search_form->handleRequest($request);

        if ($advanced_cv_search_form->isSubmitted() && $advanced_cv_search_form->isValid()) {
            $presentation = $advanced_cv_search_form->getData();
            $filterService->filterQueryBuilderByPresentation($candidateQuery, $presentation);
            $filterService->filterQueryBuilderByPresentation($matchedQuery, $presentation);
        }

        $candidatePaginatorAdapter = new DoctrineORMAdapter($candidateQuery->getQuery());
        $matchedPaginatorAdapter = new DoctrineORMAdapter($matchedQuery->getQuery());

        $candidatePaginator = new Pagerfanta($candidatePaginatorAdapter);
        $candidatePaginator->setMaxPerPage(EmployeeCv::NUM_ITEMS);
        $candidatePaginator->setCurrentPage(1);

        $matchedPaginator = new Pagerfanta($matchedPaginatorAdapter);
        $matchedPaginator->setMaxPerPage(EmployeeCv::NUM_ITEMS);
        $matchedPaginator->setCurrentPage(1);

        $paginator = new Pagerfanta(new  CompositePagerfantaAdapter($candidatePaginatorAdapter, $matchedPaginatorAdapter));
        $paginator->setMaxPerPage(EmployeeCv::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $this->render('pages/firm/offer/candidates_list.html.twig', [
            'advanced_cv_search_form' => $advanced_cv_search_form->createView(),
            'candidatePaginator' => $candidatePaginator,
            'matchedPaginator' => $matchedPaginator,
            'paginator' => $paginator,
            'offer' => $offer,
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/statuszvaltas/{id}", name="firm_offer_status_toggle")
     * @Method("POST")
     *
     *  TODO: Refactor to statusChange(EmployeeCv $employeeCv, int $status, TranslatorInterface $translator)
     *
     * @param Offer $offer
     *
     * @return Response
     */
    public function statusToggle(Offer $offer): Response
    {
        $active = OfferStatusEnum::create(OfferStatusEnum::ACTIVE);
        $inactive = OfferStatusEnum::create(OfferStatusEnum::INACTIVE);
        $waiting = OfferStatusEnum::create(OfferStatusEnum::WAITING);

        $today = new \DateTime();

        // if the offer is active or waiting, we just make it to inactive.
        if ($offer->getStatus() == $active || $offer->getStatus() == $waiting) {
            $offer->setStatus($inactive);
        }
        // If the offer is inactive we have to decide if it is still waiting or not!
        elseif ($offer->getApplicableFromDate() > $today) {
            $offer->setStatus($waiting);
        } else {
            $offer->setExpireDate(new \DateTime('+1 month'));
            $offer->setStatus($active);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($offer);
        $em->flush();

        $title = $this->translator->trans('notification.firm.offer.status_change.title', ['%title%' => $offer->getTitle()]);
        $message = $this->translator->trans('notification.firm.offer.status_change.message', ['%status%' => $this->translator->trans(OfferStatusEnum::getReadables()[$offer->getStatus()->getValue()])]);

        return $this->json([
            'title' => $title,
            'status' => $offer->getStatus()->getReadable(),
            'message' => $message,
            'success' => 1,
        ]);
    }

    /**
     * @Route("/uj", name="firm_offer_new")
     * @Route("/szerkesztes/{id}", name="firm_offer_edit")
     * @Method("GET")
     *
     * @param Offer|null $offer
     *
     * @return Response
     */
    public function manage(Offer $offer = null)
    {
        return $this->redirectToRoute('homepage');
        if (null !== $offer) {
            $this->denyAccessUnlessGranted('ROLE_OFFER_EDIT', $offer);
        }

        return $this->render(
            'pages/firm/offer/new.html.twig', [
                'firm_offer_form' => $this->createForm(
                    ManageType::class, (!empty($offer) ? $offer : new Offer()),
                    [
                        'action' => (null !== $offer) ? $this->generateUrl('firm_offer_edit_action', ['id' => $offer->getId()]) : $this->generateUrl('firm_offer_new_action'),
                        'method' => 'POST',
                        'attr' => [
                            'id' => 'offer-manage-form',
                            'novalidate' => 'novalidate',
                        ],
                    ]
                )->createView(),
            ]
        );
    }

    /**
     * @Route("/uj/{type}", defaults={"type" = "submit"}, name="firm_offer_new_action")
     * @Route("/szerkesztes/{id}", name="firm_offer_edit_action")
     * @Method("POST")
     *
     * @param Request    $request
     * @param Offer|null $offer
     *
     * @return JsonResponse
     */
    public function manageAction(Request $request, ?Offer $offer): JsonResponse
    {

        $new_offer = false;
        if (null === $offer) {
            $new_offer = true;
            $offer = new Offer();
            $this->denyAccessUnlessGranted('ROLE_OFFER_CREATE', $offer);
        } else {
            $this->denyAccessUnlessGranted('ROLE_OFFER_EDIT', $offer);
        }

        $form = $this->createForm(ManageType::class, $offer);

        $offer->setFirm($this->getUser()->getFirm());

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $offer->setStatus(
            OfferStatusEnum::create(
                (isset($request->request->get('manage')['submit']))
                    ? OfferStatusEnum::UNDER_CONSIDERATION
                    : OfferStatusEnum::SAVED
            )
        );
        $this->offerManager->save($offer);
        
        if($new_offer) {
            $mailArray = [
                'nemes.gyula@mumi.hu', 
                'halasi.beatrix@mumi.hu', 
                'lovas.virag@mumi.hu', 
                'ablonczy.daniel@mumi.hu', 
                'domotor.nikoletta@mumi.hu', 
                'vincze.szilard@mumi.hu',
                'szekeres.anett@mumi.hu',
            ];
            foreach ($mailArray as $mail) {
                $this->emailManager->send($this->emailManager->getDefaultSender(), $mail, 'admin.newoffer', [
                    'firm' => $this->getUser()->getFirm()->getName(),
                    'offer_id' => $offer->getId(),
                    'title' => $offer->getTitle()
                ]);
            }

        }
        
        return $this->json([
            'success' => 1,
            'redirectUrl' => $this->generateUrl('firm_offer_list'),
        ]);
    }

    /**
     * @Route("/torles/{id}", name="firm_offer_delete")
     * @Method("GET")
     *
     * @param Offer $offer
     *
     * @return Response
     */
    public function offerDelete(Offer $offer): Response
    {
        $this->denyAccessUnlessGranted('ROLE_OFFER_DELETE', $offer);

        $this->offerManager->delete($offer);

        return $this->redirectToRoute('firm_offer_list');
    }
}
