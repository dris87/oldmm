<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) 
 *
 * Developed by: 
 * Contributed: 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace All4One\AppBundle\Controller\Firm;

use All4One\AppBundle\Form\FirmColleagueType;
use All4One\AppBundle\Form\FirmType;
use All4One\AppBundle\Manager\EmailManager;
use All4One\AppBundle\Manager\FirmManager;
use All4One\AppBundle\Manager\TrackedTokenManager;
use All4One\AppBundle\Manager\UserManager;
use All4One\AppBundle\Traits\ControllerUtilsTrait;
use Common\CoreBundle\Entity\Firm\Firm;
use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Entity\Util\TrackedToken;
use Common\CoreBundle\Enumeration\User\UserStatusEnum;
use Common\CoreBundle\Enumeration\Util\TrackedTokenStatusEnum;
use Common\CoreBundle\Enumeration\Util\TrackedTokenTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage the firm colleague related security parts(registration, activation, email and password change).
 */
class FirmSecurityController extends AbstractController
{
    use ControllerUtilsTrait;

    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * @var TrackedTokenManager
     */
    private $trackedTokenManager;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var FirmManager
     */
    private $firmManager;

    /**
     * FirmSecurityController constructor.
     *
     * @param EmailManager        $emailManager
     * @param TrackedTokenManager $trackedTokenManager
     * @param UserManager         $userManager
     * @param FirmManager         $firmManager
     */
    public function __construct(
        EmailManager $emailManager,
        TrackedTokenManager $trackedTokenManager,
        UserManager $userManager,
        FirmManager $firmManager
    ) {
        $this->emailManager = $emailManager;
        $this->trackedTokenManager = $trackedTokenManager;
        $this->userManager = $userManager;
        $this->firmManager = $firmManager;
    }

    /**
     * @Route("/munkaadoi-regisztracio", name="firm_registration", options={"sitemap" = {"priority" = 1 }})
     * @Method("GET")
     *
     * @return Response
     */
    public function firmRegistration(): Response
    {
        return $this->render(
            'pages/firm_colleague/registration.html.twig',
            [
                'firm_registration_form' => $this->getRegistrationForm()->createView(),
            ]
        );
    }

    /**
     * @Route("/munkaadoi-regisztracio", name="firm_registration_action")
     * @Method("POST")
     *
     * @param Request $request
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function registerFirm(Request $request)
    {
        $form = $this->getRegistrationForm();
        $form->handleRequest($request);

        // Check if the form is valid or not.
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'composite' => true,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        /** @var Firm $firm */
        $firm = $form->get('firm')->getData();
        /** @var FirmColleague $firmColleague */
        $firmColleague = $form->get('firmColleague')->getData();

        $this->firmManager->save($firm);
        $firmColleague->setFirm($firm);
        $this->userManager->create($firmColleague);

        $activationToken = new TrackedToken();
        $activationToken->setUser($firmColleague);
        $activationToken->setExpireDate(new \DateTime('+1 month'));
        $activationToken->setType(TrackedTokenTypeEnum::create(TrackedTokenTypeEnum::FIRM_ACTIVATION));
        $activationToken->setMaxUseTimes(1);
        $this->trackedTokenManager->create($activationToken);

        $this->emailManager->send($this->emailManager->getDefaultSender(), $firmColleague->getEmail(), 'firm_colleague.registration', [
            'full_name' => $firmColleague->getFullName(),
            'token' => $activationToken->getToken(),
        ]);

        $mailArray = [
                'nemes.gyula@mumi.hu', 
                'halasi.beatrix@mumi.hu', 
                'lovas.virag@mumi.hu', 
                'ablonczy.daniel@mumi.hu', 
                'vincze.szilard@mumi.hu',
                'szekeres.anett@mumi.hu',
                'domotor.nikoletta@mumi.hu',
                'kirjak.kitti@mumi.hu',
                'foldi.magdolna@mumi.hu',
                'papp.karolina@mumi.hu',
                'lukacs.veronika@mumi.hu',
                'sule.greta@mumi.hu',
            ];
      
        foreach ($mailArray as $mail) {

            $this->emailManager->send($this->emailManager->getDefaultSender(), $mail , 'admin.registration', [
                'firm_name' => $firmColleague->getFirm(),
                'firm_id' => $firmColleague->getFirm()->getId(),
                'f_c' => $firmColleague->getFullName(),
                'f_c_e' => $firmColleague->getEmail(),
            ]);

        }

        return $this->json([
            'redirectUrl' => $this->get('router')->generate('firm_registration_success'),
            'success' => 1,
        ]);
    }

    /**
     * @Route("/sikeres-munkaadoi-regisztracio", name="firm_registration_success")
     * @Method("GET")
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function registerFirmSuccess()
    {
        return $this->render('pages/firm_colleague/registration_success.html.twig');
    }

    /**
     * @Route("/kapcsolattartoi-fiok-aktivalasa/{token}", name="activate_firm_colleague_account")
     * @Method("GET")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param TrackedToken $trackedToken
     * @param Request      $request
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function activateFirmColleague(TrackedToken $trackedToken, Request $request): Response
    {
        $em = $this->getDoctrine();
        $userRepository = $em->getRepository('CommonCoreBundle:User\User');
        $success = false;

        /** @var FirmColleague $user */
        $user = $userRepository->findOneBy(['id' => $trackedToken->getUser()->getId()]);
        if (!empty($user)) {
            $user->setStatus(UserStatusEnum::create(UserStatusEnum::ACTIVE));
            $this->userManager->save($user);
            $this->userManager->logUserIn($user, $request);

            $trackedToken->setStatus(TrackedTokenStatusEnum::create(TrackedTokenStatusEnum::USED));
            $this->trackedTokenManager->save($trackedToken);

            $success = true;
        }

        return $this->render('pages/firm_colleague/activate.html.twig', ['activated' => $success]);
    }

    /**
     * @Route("/adoszam-adatok-lekerdezese/{taxNumber}", name="get_tax_number_info")
     * @Method("POST")
     * TODO: Tokennel védjük le, hogy ne legyen elérhető bárki számára
     *
     * @param string      $taxNumber
     * @param FirmManager $firmManager
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getTaxNumberInfo(string $taxNumber, FirmManager $firmManager)
    {
        $firmData = $firmManager->getInfoFromTaxNumber($taxNumber);

        if (!is_object($firmData['location'])) {
            return $this->json([
                'success' => 0,
            ]);
        }
        $firmData['location'] = [
          'id' => $firmData['location']->getId(),
          'value' => $firmData['location']->getCity()->getValue(),
        ];

        return $this->json([
           'success' => 1,
           'firmData' => $firmData,
        ]);
    }

    /**
     * @Route("/munkaadoi-fiok-torolve/{token}", name="firm_colleague_account_deletion_landing")
     * @Method("GET")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param TrackedToken        $trackedToken
     * @param TrackedTokenManager $trackedTokenManager
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function deletedFirmColleagueAccount(TrackedToken $trackedToken, TrackedTokenManager $trackedTokenManager)
    {
        $trackedTokenManager->save($trackedToken);

        return $this->render('pages/firm_colleague/deleted.html.twig');
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getRegistrationForm()
    {
        return $this->createFormBuilder(null,
            [
                'action' => $this->generateUrl('firm_registration_action'),
                'method' => 'POST',
                'attr' => ['id' => 'firm-registration-form', 'novalidate' => 'novalidate'],
                'validation_groups' => ['Default', 'registration'],
            ])
            ->add('firm', FirmType::class, [
                'data_class' => Firm::class,
            ])
            ->add('firmColleague', FirmColleagueType::class, [
                'data_class' => FirmColleague::class,
            ])
            ->getForm();
    }
}
