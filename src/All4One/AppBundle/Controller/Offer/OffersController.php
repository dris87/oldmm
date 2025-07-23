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

use All4One\AppBundle\Form\Offer\AdvancedSearchType;
use All4One\AppBundle\Manager\Filter\AdvancedOfferQueryFilter;
use Common\CoreBundle\Doctrine\Repository\Offer\OfferRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage firm data.
 *
 * @Route("/allasajanlatok")
 */
class OffersController extends AbstractController
{
    /**
     * @Route("/", defaults={"page" = "1", "_format" = "html"}, name="list_offers", options={"sitemap" = {"priority" = 1 }})
     * @Route("/oldal/{page}", defaults={"_format" = "html"}, requirements={"page" = "[1-9]\d*"}, name="list_offers_paginated")
     * @Method("GET")
     * @Cache(smaxage="10")
     *
     * @param int                      $page
     * @param string                   $_format
     * @param Request                  $request
     * @param AdvancedOfferQueryFilter $filterService
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return Response
     */
    public function index(int $page, string $_format, Request $request, AdvancedOfferQueryFilter $filterService): Response
    {
        $em = $this->getDoctrine();

        /** @var OfferRepository $offerRepo */
        $offerRepo = $em->getRepository('CommonCoreBundle:Offer\Offer');
        $queryBuilder = $offerRepo->getQueryBuilderOfLatestTile();

        /** @var Form $advanced_search_form */
        $advanced_search_form = $this->container->get('form.factory')->createNamed(
            'search',
            AdvancedSearchType::class, null,
            [
                'action' => $this->generateUrl('list_offers'),
                'method' => 'GET',
            ]
        );

        $advanced_search_form->handleRequest($request);
        
        if ($advanced_search_form->isSubmitted() && $advanced_search_form->isValid()) {
            $presentation = $advanced_search_form->getData();
            $filterService->filterQueryBuilderByPresentation($queryBuilder, $presentation);
        }

        $latestOffers = $offerRepo->createPaginator($queryBuilder->getQuery(), $page);

        return $this->render('pages/offer/list.'.$_format.'.twig', [
            'advanced_offer_search_form' => $advanced_search_form->createView(),
            'offers' => $latestOffers,
        ]);
    }
}
