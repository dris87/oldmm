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

namespace All4One\AppBundle\Controller\Employee;

use All4One\AppBundle\Form\Employee\EmployeeAccountDeleteType;
use All4One\AppBundle\Form\Employee\EmployeeDetailsType;
use All4One\AppBundle\Form\Security\ChangePasswordType;
use All4One\AppBundle\Manager\EmployeeCvManager;
use All4One\AppBundle\Manager\OfferManager;
use All4One\AppBundle\Manager\TrackedTokenManager;
use All4One\AppBundle\Manager\UserManager;
use All4One\AppBundle\Traits\ControllerUtilsTrait;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\DeletedEmployee;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Entity\Util\TrackedToken;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvStatusEnum;
use Common\CoreBundle\Enumeration\User\UserStatusEnum;
use Common\CoreBundle\Enumeration\Util\TrackedTokenTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/munkavallalo")
 * @Security("has_role('ROLE_EMPLOYEE')")
 */
class EmployeeController extends AbstractController
{
    use ControllerUtilsTrait;

    /**
     * @Route("/", name="employee_index")
     * @Route("/", name="employee_dashboard")
     * @Method("GET")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('pages/employee/dashboard.html.twig');
    }

    /**
     * @Route("/adataim", name="employee_menage_details")
     * @Method("GET")
     *
     * @return Response
     */
    public function menageDetails(): Response
    {
        $user = $this->getUser();

        return $this->render('pages/employee/manage_details.html.twig', [
            'employee_details_form' => $this->createForm(
                EmployeeDetailsType::class, $user,
                [
                    'action' => $this->generateUrl('employee_menage_details_action'),
                    'method' => 'POST',
                    'attr' => ['id' => 'employee-details-form'],
                ]
            )->createView(),
            'change_password_form' => $this->createForm(
                ChangePasswordType::class, $user,
                [
                    'action' => $this->generateUrl('do_change_password'),
                    'method' => 'POST',
                    'attr' => [
                        'id' => 'change-password-form',
                    ],
                ]
            )->createView(),
            'employee_account_delete_form' => $this->createForm(
                EmployeeAccountDeleteType::class, new DeletedEmployee(),
                [
                    'action' => $this->generateUrl('employee_delete_action'),
                    'method' => 'POST',
                    'attr' => [
                        'id' => 'employee-account-delete-form',
                        'novalidate' => 'novalidate',
                    ],
                ]
            )->createView(),
        ]);
    }

    /**
     * @Route("/adataim-modositasa", name="employee_menage_details_action")
     * @Method("POST")
     *
     * @param Request             $request
     * @param UserManager         $userManager
     * @param TranslatorInterface $translator
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function menageDetailsAction(Request $request, UserManager $userManager, TranslatorInterface $translator)
    {
        $user = $this->getUser();
        $form = $this->createForm(EmployeeDetailsType::class, $user);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $userManager->save($user);

        $title = $translator->trans('notification.employee.details_update.title');
        $message = $translator->trans('notification.employee.details_update.message');

        return $this->json([
            'title' => $title,
            'message' => $message,
            'success' => 1,
        ]);
    }

    /**
     * @Route("/fiok-torlese", name="employee_delete_action")
     * @Method("POST")
     *
     * @param Request             $request
     * @param UserManager         $userManager
     * @param TrackedTokenManager $trackedTokenManager
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function employeeDeleteAction(Request $request, UserManager $userManager, TrackedTokenManager $trackedTokenManager)
    {
        $deletedUser = new DeletedEmployee();
        $form = $this->createForm(EmployeeAccountDeleteType::class, $deletedUser, [
            'action' => $this->generateUrl('employee_delete_action'),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $userManager->deleteUser($deletedUser, $this->getUser());

        $trackedToken = new TrackedToken();
        $trackedToken->setType(TrackedTokenTypeEnum::create(TrackedTokenTypeEnum::EMPLOYEE_ACCOUNT_DELETE));
        $trackedToken->setMaxUseTimes(1);
        $trackedToken->appendData('deletedUserId', $deletedUser->getId());
        $trackedTokenManager->create($trackedToken);

        return $this->json([
            'redirectUrl' => $this->get('router')->generate('employee_account_deletion_landing', ['token' => $trackedToken->getToken()]),
            'success' => 1,
        ]);
    }

    /**
     * @Route("/statuszvaltas", name="employee_status_toggle")
     * @Method("POST")
     * TODO: Refactor to statusToggle(int $status, TranslatorInterface $translator)
     *
     * @param UserManager         $userManager
     * @param TranslatorInterface $translator
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function statusToggle(UserManager $userManager, TranslatorInterface $translator): Response
    {
        $active = UserStatusEnum::create(UserStatusEnum::ACTIVE);
        $inactive = UserStatusEnum::create(UserStatusEnum::INACTIVE);

        /** @var Employee $user */
        $user = $this->getUser();
        $user->setStatus(($user->getStatus() != $active) ? $active : $inactive);
        $userManager->save($user);

        $title = $translator->trans('notification.employee.status_change.title');
        $message = $translator->trans('notification.employee.status_change.message', ['%status%' => $translator->trans(UserStatusEnum::getReadables()[$user->getStatus()->getValue()])]);

        return $this->json([
            'title' => $title,
            'message' => $message,
            'success' => 1,
        ]);
    }

    /**
     * @Route("/jelentkezeseim", defaults={"page" = "1" }, name="employee_offer_applies")
     * @Route("/jelentkezeseim/oldal/{page}", requirements={"page" = "[1-9]\d*"}, name="employee_offer_applies_paginated")
     * @Method("GET")
     *
     * @param int          $page
     * @param OfferManager $offerManager
     *
     * @return Response
     */
    public function offerApplies(int $page, OfferManager $offerManager): Response
    {
        $candidates = $offerManager->getCandidateRepository()->findEmployeeAppliedOffersPaginated($this->getUser(), $page);

        return $this->render('pages/employee/offer_applies.html.twig', ['candidates' => $candidates]);
    }

    /**
     * @Route("/hirdetesre-jelentkezes/{offerId}/{employeeCvId}", name="offer_apply")
     * @Method("POST")
     * @Entity("offer", expr="repository.find(offerId)")
     * @Entity("employeeCv", expr="repository.find(employeeCvId)")
     *
     * @param Offer             $offer
     * @param EmployeeCv        $employeeCv
     * @param EmployeeCvManager $employeeCvManager
     *
     * @return Response
     */
    public function offerApply(Offer $offer, EmployeeCv $employeeCv, EmployeeCvManager $employeeCvManager): Response
    {
        if ($employeeCv->getStatus() != EmployeeCvStatusEnum::create(EmployeeCvStatusEnum::ACTIVE)) {
            return $this->json([
                'success' => 0,
            ]);
        }

        $employeeCvManager->apply($employeeCv, $offer);

        return $this->json([
            'success' => 1,
        ]);
    }
}
