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

namespace All4One\AppBundle\Controller;

use All4One\AppBundle\Form\Security\ChangePasswordType;
use All4One\AppBundle\Form\Security\ForgotPasswordType;
use All4One\AppBundle\Form\Security\ResetPasswordType;
use All4One\AppBundle\Manager\EmailManager;
use All4One\AppBundle\Manager\NotificationManager;
use All4One\AppBundle\Manager\TrackedTokenManager;
use All4One\AppBundle\Manager\UserManager;
use All4One\AppBundle\Traits\ControllerUtilsTrait;
use Common\CoreBundle\Entity\User\User;
use Common\CoreBundle\Entity\Util\TrackedToken;
use Common\CoreBundle\Enumeration\Util\TrackedTokenStatusEnum;
use Common\CoreBundle\Enumeration\Util\TrackedTokenTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SecurityController.
 */
class SecurityController extends AbstractController
{
    use ControllerUtilsTrait;

    /**
     * Type of logins, which are associated with a separate login page.
     *
     * @var array
     */
    private $loginTypeTemplate = [
        'munkavallalo' => 'employee',
        'munkaado' => 'firm_colleague',
    ];

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TrackedTokenManager
     */
    private $trackedTokenManager;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var NotificationManager
     */
    private $notificationManager;

    /**
     * SecurityController constructor.
     *
     * @param TranslatorInterface $translator
     * @param TrackedTokenManager $trackedTokenManager
     * @param EmailManager        $emailManager
     * @param UserManager         $userManager
     * @param NotificationManager $notificationManager
     */
    public function __construct(
        TranslatorInterface $translator,
        TrackedTokenManager $trackedTokenManager,
        EmailManager $emailManager,
        UserManager $userManager,
        NotificationManager $notificationManager
    ) {
        $this->translator = $translator;
        $this->trackedTokenManager = $trackedTokenManager;
        $this->emailManager = $emailManager;
        $this->userManager = $userManager;
        $this->notificationManager = $notificationManager;
    }

    /**
     * @Route("/bejelentkezes-json", name="security_json_login")
     *
     * @return JsonResponse
     */
    public function loginAction(): JsonResponse
    {
        return $this->json([
            'success' => 1,
        ]);
    }

    /**
     * @Route(
     *     "/bejelentkezes/{type}",
     *     defaults={"type" = "landing"},
     *     requirements={"type" = "munkavallalo|munkaado|landing"},
     *     name="security_login",
     *     options={"sitemap" = {"priority" = 1 }}
     * )
     *
     * @param AuthenticationUtils $helper
     * @param $type
     *
     * @return Response
     */
    public function login(AuthenticationUtils $helper, $type)
    {

        
        if ('landing' == $type) {
            $twigName = 'pages/general/login_landing.html.twig';
        } else {
            $twigName = 'pages/'.$this->loginTypeTemplate[$type].'/login.html.twig';
        }

        return $this->render($twigName, [
            // last username entered by the user (if any)
            'last_username' => $helper->getLastUsername(),
            // last authentication error (if any)
            'error' => $helper->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/elfelejtett-jelszo", name="forgot_password", options={"sitemap" = {"priority" = 1 }})
     *
     * @return Response
     */
    public function forgotPasswordView(): Response
    {
        return $this->render(
            'pages/user/forgot_password.html.twig', [
                'forgot_password_form' => $this->createForm(
                    ForgotPasswordType::class, new User(),
                    [
                        'action' => $this->generateUrl('send_forgot_password'),
                        'method' => 'POST',
                        'attr' => [
                            'id' => 'send-forgot-password-message',
                        ],
                    ]
                )->createView(),
            ]
        );
    }

    /**
     * @Route("/elfelejtett-jelszo-kuldese", name="send_forgot_password")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return JsonResponse
     */
    public function sendForgotPassword(Request $request): JsonResponse
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        // Validate the form
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $formData = $form->getData();
        /** @var $user User */
        if (null === ($user = $this->userManager->getUserByEmail($formData['email']))) {
            return $this->json([
                'success' => 0,
                'error' => ['email' => $this->translator->trans('validation.not_registered_email')],
            ]);
        }

        // Generate and save a tracked token
        $trackedToken = new TrackedToken();
        $trackedToken->setUser($user);
        $trackedToken->setType(TrackedTokenTypeEnum::create(TrackedTokenTypeEnum::RESET_PASSWORD));
        $trackedToken->setExpireDate(new \DateTime('+1 week'));
        $trackedToken->setMaxUseTimes(1);
        $this->trackedTokenManager->create($trackedToken);

        $this->emailManager->send($this->emailManager->getDefaultSender(), $user->getEmail(), 'security.forgot_password', [
            'full_name' => $user->getFullName(),
            'token' => $trackedToken->getToken(),
        ]);

        return $this->json([
            'success' => 1,
        ]);
    }

    /**
     * @Route("/uj-jelszo-megadasa/{token}", name="reset_password")
     * @Method("GET")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param TrackedToken $trackedToken
     * @param UserManager  $userManager
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function resetPasswordView(TrackedToken $trackedToken, UserManager $userManager): Response
    {
        $user = $userManager->getUserById($trackedToken->getUserId());

        return $this->render(
            'pages/user/reset_password.html.twig', [
                'reset_password_form' => $this->createForm(
                    ResetPasswordType::class, new User(),
                    [
                        'action' => $this->generateUrl('do_reset_password', ['token' => $trackedToken->getToken()]),
                        'method' => 'POST',
                        'attr' => [
                            'id' => 'reset-password-message',
                        ],
                    ]
                )->createView(),
                'login_type' => ($user->isFirmColleague() ? 'munkaado' : 'munkavallalo'),
            ]
        );
    }

    /**
     * @Route("/uj-jelszo-beallitasa/{token}", name="do_reset_password")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param TrackedToken        $trackedToken
     * @param Request             $request
     * @param UserManager         $userManager
     * @param TrackedTokenManager $trackedTokenManager
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function resetPassword(TrackedToken $trackedToken, Request $request, UserManager $userManager, TrackedTokenManager $trackedTokenManager)
    {
        /** @var User $user */
        $user = $userManager->getUserById($trackedToken->getUserId());
        if (empty($user)) {
            return $this->json([
                'success' => 0,
                'error' => 'Token owner unknown',
            ]);
        }

        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);

        // Validate the form as usual
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $userManager->changePassword($user, $user->getPlainPassword());

        $trackedToken->setStatus(TrackedTokenStatusEnum::create(TrackedTokenStatusEnum::USED));
        $trackedTokenManager->save($trackedToken);

        $userManager->logUserIn($user, $request);

        return $this->json([
            'success' => 1,
        ]);
    }

    /**
     * @Route("/jelszo-modositas", name="do_change_password")
     * @Method("POST")
     *
     * @param Request     $request
     * @param UserManager $userManager
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changePassword(Request $request, UserManager $userManager)
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        // Validate the form as usual
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'notification' => $this->notificationManager->createArray('change_password.error'),
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $userManager->changePassword($user, $user->getPlainPassword());

        return $this->json([
            'notification' => $this->notificationManager->createArray('change_password.success'),
            'success' => 1,
        ]);
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in app/config/security.yml
     *
     * @Route("/logout", name="security_logout")
     *
     * @throws \Exception
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
