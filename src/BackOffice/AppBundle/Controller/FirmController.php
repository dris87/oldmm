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

namespace BackOffice\AppBundle\Controller;

use BackOffice\AppBundle\Form\FirmBalanceItemCreditType;
use Common\CoreBundle\Entity\Firm\Balance\FirmBalanceItem;
use Common\CoreBundle\Entity\Firm\Firm;
use Common\CoreBundle\Entity\Firm\Order\FirmOrder;
use Common\CoreBundle\Enumeration\Firm\Order\FirmOrderStatusEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FirmController.
 *
 * @Security("has_role('ROLE_ADMIN')")
 */
class FirmController extends Controller
{
    /**
     * @Route("/firm/{firm}/balance", name="admin_firm_balance")
     *
     * @param Firm $firm
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listBalanceAction(Firm $firm)
    {
        return $this->render('BackOfficeAppBundle::firm/balance_list.html.twig', [
            'firm' => $firm,
        ]);
    }

    /**
     * @Route("/firm/{firm}/purchases", name="firm_purchases")
     *
     * @param Firm $firm
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listPurchasesAction(Firm $firm)
    {
        return $this->render('BackOfficeAppBundle::firm/purchase_list.html.twig', [
            'firm' => $firm,
        ]);
    }

    /**
     * @Route("/firm/purchases/accept/{order}", name="firm_purchases_accept")
     *
     * @param FirmOrder $order
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function acceptPurchase(FirmOrder $order)
    {
        $order->setStatus(FirmOrderStatusEnum::create(FirmOrderStatusEnum::PAID));
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();
        $this->addFlash('sonata_flash_success', 'Sikeresen elfogadva!');

        return $this->redirectToRoute('firm_purchases', ['firm' => $order->getFirm()->getId()]);
    }

    /**
     * @Route("/firm/purchases/cancel/{order}", name="firm_purchases_cancel")
     *
     * @param FirmOrder $order
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cancelPurchase(FirmOrder $order)
    {
        $order->setStatus(FirmOrderStatusEnum::create(FirmOrderStatusEnum::CANCELLED));
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();
        $this->addFlash('sonata_flash_success', 'Sikeresen elutasítva!');

        return $this->redirectToRoute('firm_purchases', ['firm' => $order->getFirm()->getId()]);
    }

    /**
     * @Route("/firm/balance-item/{balanceItem}/edit", name="admin_firm_balance_item_edit")
     *
     * @param Request         $request
     * @param FirmBalanceItem $balanceItem
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editBalanceItemCredit(Request $request, FirmBalanceItem $balanceItem)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(FirmBalanceItemCreditType::class, $balanceItem);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($balanceItem);
            $em->flush();

            $this->addFlash('sonata_flash_success', 'Kredit sikeresen módosítva!');

            return $this->redirectToRoute('admin_firm_balance', [
                'firm' => $balanceItem->getBalance()->getFirm()->getId(),
            ]);
        }

        return $this->render('BackOfficeAppBundle::firm/balance_item_edit.html.twig', [
            'form' => $form->createView(),
            'firm' => $balanceItem->getBalance()->getFirm()->getId(),
        ]);
    }

    /**
     * @Route("/back-to-firm-list", name="back_to_firm_list")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToList(Request $request)
    {
        $admin = $this->get('admin.firm');
        $admin->setRequest($request);
        $parameters = [];

        if ($filter = $admin->getFilterParameters()) {
            $parameters['filter'] = $filter;
        }

        return $this->redirect($admin->generateUrl('list', $parameters));
    }
}
