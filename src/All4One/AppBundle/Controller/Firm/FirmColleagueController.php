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

use All4One\AppBundle\Form\Firm\FirmColleagueAccountDeleteType;
use All4One\AppBundle\Form\FirmColleague\FirmColleagueDetailsType;
use All4One\AppBundle\Form\Security\ChangePasswordType;
use All4One\AppBundle\Manager\TrackedTokenManager;
use All4One\AppBundle\Manager\UserManager;
use All4One\AppBundle\Traits\ControllerUtilsTrait;
use Common\CoreBundle\Entity\Firm\DeletedFirmColleague;
use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Entity\Util\TrackedToken;
use Common\CoreBundle\Enumeration\User\UserStatusEnum;
use Common\CoreBundle\Enumeration\Util\TrackedTokenTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/kapcsolattarto")
 * @Security("has_role('ROLE_COLLEAGUE')")
 */
class FirmColleagueController extends AbstractController
{
    use ControllerUtilsTrait;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * FirmColleagueController constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Lists all Offer entities.
     *
     * @Route("/", name="firm_colleague_index")
     * @Route("/", name="firm_colleague_dashboard")
     * @Method("GET")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('pages/firm_colleague/dashboard.html.twig');
    }

    /**
     * @Route("/adataim", name="firm_colleague_menage_details")
     * @Method("GET")
     *
     * @return Response
     */
    public function menageDetails(): Response
    {
        $user = $this->getUser();

        return $this->render('pages/firm_colleague/manage_details.html.twig', [
            'firm_colleague_details_form' => $this->createForm(
                FirmColleagueDetailsType::class, $user,
                [
                    'action' => $this->generateUrl('firm_colleague_menage_details_action'),
                    'method' => 'POST',
                    'attr' => ['id' => 'firm-colleague-details-form'],
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
            'firm_colleague_account_delete_form' => $this->createForm(
                FirmColleagueAccountDeleteType::class, new DeletedFirmColleague(),
                [
                    'action' => $this->generateUrl('firm_colleague_delete_action'),
                    'method' => 'POST',
                    'attr' => [
                        'id' => 'firm-colleague-account-delete-form',
                        'novalidate' => 'novalidate',
                    ],
                ]
            )->createView(),
        ]);
    }

    /**
     * @Route("/adataim-modositasa", name="firm_colleague_menage_details_action")
     * @Method("POST")
     *
     * @param Request     $request
     * @param UserManager $userManager
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function menageDetailsAction(Request $request, UserManager $userManager): JsonResponse
    {
        $user = $this->getUser();

        $form = $this->createForm(FirmColleagueDetailsType::class, $user);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $userManager->save($user);

        $title = $this->translator->trans('notification.firm_colleague.details_update.title');
        $message = $this->translator->trans('notification.firm_colleague.details_update.message');

        return $this->json([
            'title' => $title,
            'message' => $message,
            'success' => 1,
        ]);
    }

    /**
     * @Route("/fiok-torlese", name="firm_colleague_delete_action")
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
    public function firmColleagueDeleteAction(Request $request, UserManager $userManager, TrackedTokenManager $trackedTokenManager)
    {
        $deletedUser = new DeletedFirmColleague();
        $form = $this->createForm(FirmColleagueAccountDeleteType::class, $deletedUser, [
            'action' => $this->generateUrl('firm_colleague_delete_action'),
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
        $trackedToken->setType(TrackedTokenTypeEnum::create(TrackedTokenTypeEnum::FIRM_ACCOUNT_DELETE));
        $trackedToken->setMaxUseTimes(1);
        $trackedToken->appendData('deletedUserId', $deletedUser->getId());
        $trackedTokenManager->create($trackedToken);

        return $this->json([
            'redirectUrl' => $this->get('router')->generate('firm_colleague_account_deletion_landing', ['token' => $trackedToken->getToken()]),
            'success' => 1,
        ]);
    }

    /**
     * @Route("/statuszvaltas", name="firm_colleague_status_toggle")
     * @Method("POST")
     *
     * @param UserManager $userManager
     *
     * @throws \Exception
     *
     * TODO: Refactor to statusChange(int $status, UserManager $userManager, TranslatorInterface $translator)
     *
     * @return Response
     */
    public function statusToggle(UserManager $userManager): Response
    {
        $active = UserStatusEnum::create(UserStatusEnum::ACTIVE);
        $inactive = UserStatusEnum::create(UserStatusEnum::INACTIVE);

        /** @var FirmColleague $user */
        $user = $this->getUser();
        $user->setStatus(($user->getStatus() != $active) ? $active : $inactive);
        $userManager->save($user);

        $title = $this->translator->trans('notification.firm_colleague.status_change.title');
        $message = $this->translator->trans('notification.firm_colleague.status_change.message', ['%status%' => $this->translator->trans(UserStatusEnum::getReadables()[$user->getStatus()->getValue()])]);

        return $this->json([
            'title' => $title,
            'message' => $message,
            'success' => 1,
        ]);
    }
}
