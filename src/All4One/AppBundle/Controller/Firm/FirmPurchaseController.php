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

use All4One\AppBundle\Manager\PackageManager;
use Common\CoreBundle\Entity\Firm\Cart\FirmCartItem;
use Common\CoreBundle\Entity\Firm\Order\FirmOrder;
use Common\CoreBundle\Entity\Firm\Package\FirmPackage;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Firm\Package\FirmPackageServiceEnum;
use Common\CoreBundle\Manager\Firm\FirmBalanceManager;
use Common\CoreBundle\Manager\Firm\FirmCartManager;
use Common\CoreBundle\Presentation\CartItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Controller used to manage firm's offers.
 *
 * @Route("/cegfiok")
 * @Security("has_role('ROLE_COLLEAGUE')")
 */
class FirmPurchaseController extends AbstractController
{
    /**
     * @Route("/szolgaltatasok", defaults={"page" = "0" }, name="firm_services_index")
     * @Route("/szolgaltatasok/oldal/{page}", requirements={"page" = "[1-9]\d*"}, name="firm_services_list_paginated")
     * @Method("GET")
     *
     * @param int            $page
     * @param PackageManager $packageManager
     *
     * @return Response
     */
    public function services(
        int $page,
        PackageManager $packageManager
    ): Response {
        $em = $this->getDoctrine();
        $packageActive = false;
        if (0 == $page) {
            $packageActive = true;
            $page = 1;
        }
        $offerRepository = $em->getRepository('CommonCoreBundle:Offer\Offer');
        $offers = $offerRepository->findPurchasableFirmTiles($this->getUser()->getFirm()->getId(), $page);
        $packages = $packageManager->getVisiblePackages();
        $referencePackages = $packageManager->getReferencePackages();
        $cartItemsOfPackages = $packageManager->getCartInfoOfPackages($packages);

        return $this->render('pages/firm/purchase/services.html.twig', [
            'packageActive' => $packageActive,
            'packages' => $packages,
            'cartItemsOfPackages' => $cartItemsOfPackages,
            'serviceTypes' => FirmPackageServiceEnum::getAllEnums(),

            'referencePackages' => $referencePackages,
            'offers' => $offers,
        ]);
    }

    /**
     * @Route("/egyenleg", name="firm_balance")
     * @Method({"GET", "POST"})
     *
     * @param FirmBalanceManager $balanceManager
     *
     * @return Response
     */
    public function balance(FirmBalanceManager $balanceManager): Response
    {
        $firm = $balanceManager->getFirm();
        $cvCount = $balanceManager->getServiceCount('cv_count');
        $solvedCvCount = $firm->getCvs()->count();
        $latestPackage = $balanceManager->getLatestBalanceItem();

        $orderRepo = $this->getDoctrine()->getRepository('CommonCoreBundle:Firm\Order\FirmOrder');
        $orders = $orderRepo->findWithPackagesByFirm($firm);

        return $this->render('pages/firm/purchase/balance.html.twig', [
            'cvCount' => $cvCount,
            'solvedCvCount' => $solvedCvCount,
            'latestPackage' => $latestPackage,
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/kosar", name="firm_cart")
     * @Method({"GET", "POST"})
     *
     * @param FirmCartManager $cartManager
     *
     * @return Response
     */
    public function cart(FirmCartManager $cartManager): Response
    {
        $standardCartItems = $cartManager->getStandardCartItems();
        $referenceCartItems = [];
        foreach (FirmPackageServiceEnum::getAllEnums() as $service) {
            if ($service->hasReference()) {
                $referenceCartItems[$service->getReadable()] = $cartManager->getReferenceCartItems($service);
            }
        }

        return $this->render('pages/firm/purchase/cart.html.twig', [
            'firm' => $cartManager->getFirm(),
            'standardCartItems' => $standardCartItems,
            'referenceCartItems' => $referenceCartItems,
            'sumInfo' => [
                'priceNet' => $cartManager->getPriceNet(),
                'priceGross' => $cartManager->getPriceGross(),
                'vat' => FirmOrder::VAT_VALUE,
                'priceVat' => $cartManager->getPriceVat(),
            ],
        ]);
    }

    /**
     * @Route("/kosar/rendeles-feladasa", name="firm_cart_order")
     * @Method({"POST", "GET"})
     *
     * @param FirmCartManager $cartManager
     *
     * @return Response
     */
    public function createOrder(FirmCartManager $cartManager): Response
    {
        $order = $cartManager->createOrder();

        if (!$order) {
            return $this->redirectToRoute('firm_cart');
        }

        $cartManager->purge();

        return $this->redirectToRoute('firm_balance');
    }

    /**
     * @Route(
     *     "/kosar/csomag-vasarlas/hirdeteshez-kotott/{packageId}/{offerId}/{status}",
     *     name="firm_purchase_offer_service_status_toggle",
     *     requirements={
     *         "packageId" = "^\d+$",
     *         "offerId" = "^\d+$",
     *         "status" = "inactive|active|in_cart"
     *     },
     * )
     * @Method({"POST"})
     * @Entity("package", expr="repository.find(packageId)")
     * @Entity("offer", expr="repository.find(offerId)")
     *
     * @param FirmPackage         $package
     * @param Offer               $offer
     * @param TranslatorInterface $translator
     * @param FirmCartManager     $cartManager
     * @param string              $status
     * @param FirmBalanceManager  $balanceManager
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function serviceToggleInCart(
        FirmPackage $package,
        Offer $offer,
        string $status,
        FirmCartManager $cartManager,
        FirmBalanceManager $balanceManager,
        TranslatorInterface $translator
    ) {
        if (!$package->getIsReferencePackage()) {
            throw $this->createNotFoundException();
        }

        $cartItem = $cartManager->get($package, $offer);
        $inCart = !empty($cartItem);

        switch ($status) {
            case 'inactive':
                if (!$inCart) {
                    $balanceManager->disableOfferService($package, $offer);
                } else {
                    $cartManager->set($package, 0, $offer);
                }
            break;
            case 'in_cart':              
                $cartManager->set($package, 1, $offer);
            break;
            case 'active':
                return $this->json(['success' => 0]);
            break;
        }

        $title = $translator->trans('notification.firm.purchase.offer.service.cart.updated.title');
        $message = $translator->trans('notification.firm.purchase.offer.service.cart.updated.message');

        return $this->json([
            'title' => $title,
            'message' => $message,
            'success' => 1,
        ]);
    }

    /**
     * @Route(
     *     "/kosar/csomag-vasarlas/{packageId}/{type}",
     *     requirements={
     *         "type" = "^(?:increase|decrease)$"
     *     },
     *     name="firm_purchase_add_package_to_cart"
     * )
     * @Route(
     *     "/kosar/csomag-vasarlas/{packageId}",
     *     defaults={"type" = "increase"},
     *     name="firm_purchase_add_package_to_cart_prototype"
     * )
     * @Method({"POST"})
     * @Entity("firmPackage", expr="repository.find(packageId)")
     *
     * @param FirmPackage         $firmPackage
     * @param string              $type
     * @param FirmCartManager     $cartManager
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addPackageToCart(
        FirmPackage  $firmPackage,
        string $type,
        FirmCartManager $cartManager,
        TranslatorInterface $translator
    ) {
        $removeCartId = $firmPackage->getId();
        switch ($type) {
            case 'increase':
             
                 // $has_akcio = $cartManager->getHasAction();
                // // var_dump($has_akcio);
                // // exit;
                // print_r($firmPackage);
                // exit;
             
                
                // if($has_akcio) {
                //     switch ($firmPackage->getId()) {
                //         case 3:
                //         $em = $this->getDoctrine();
                //             $firm_repository = $em->getRepository('CommonCoreBundle:Firm\Package\FirmPackage');
                //             $action_package = $firm_repository->find(9);

                //             $cartManager->add($action_package, 1);
                //             break;
                //         case 4:
                //             $sum = $sum - 20000;
                //             break;
                //         case 5:
                //              $sum = $sum - 30000;
                //             break;
                //         default:
                //             break;
                //     }
                // }
                $cartManager->add($firmPackage, 1);
            break;
            case 'decrease':
                $cartManager->sub($firmPackage, 1);
            break;
        }

        $title = $translator->trans('notification.firm.purchase.offer.service.cart.updated.title');
        $message = $translator->trans('notification.firm.purchase.offer.service.cart.updated.message');

        return $this->json([
            'removeCartId' => $removeCartId,
            'success' => 1,
            'title' => $title,
            'message' => $message,
            'summary' => [
                'priceNet' => $cartManager->getPriceNet(),
                'priceGross' => $cartManager->getPriceGross(),
                'vat' => FirmOrder::VAT_VALUE,
                'priceVat' => $cartManager->getPriceVat(),
            ],
        ]);
    }

    /**
     * @Route(
     *     "/kosar/csomag-vasarlas/torles/{cartItemId}",
     *     name="firm_purchase_cart_item_remove",
     * )
     * @Method({"POST"})
     * @Entity("firmCartItem", expr="repository.find(cartItemId)")
     *
     * @param FirmCartItem        $firmCartItem
     * @param FirmCartManager     $cartManager
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeCartItem(
        FirmCartItem  $firmCartItem,
        FirmCartManager $cartManager,
        TranslatorInterface $translator
    ) {
        $cartItem = new CartItem($firmCartItem);
        $removeCartId = $firmCartItem->getId();
        $cartManager->remove($cartItem);

        $title = $translator->trans('notification.firm.purchase.cart.removed.title');
        $message = $translator->trans('notification.firm.purchase.cart.removed.message');

        return $this->json([
            'removeCartId' => $removeCartId,
            'success' => 1,
            'title' => $title,
            'message' => $message,
            'summary' => [
                'priceNet' => $cartManager->getPriceNet(),
                'priceGross' => $cartManager->getPriceGross(),
                'vat' => FirmOrder::VAT_VALUE,
                'priceVat' => $cartManager->getPriceVat(),
            ],
        ]);
    }
}
