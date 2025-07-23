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

use All4One\AppBundle\Form\Employee\CvEducationType;
use All4One\AppBundle\Form\Employee\CvExperienceType;
use All4One\AppBundle\Form\Employee\CvExtraType;
use All4One\AppBundle\Form\Employee\CvOtherType;
use All4One\AppBundle\Form\Employee\CvWorkDetailsType;
use All4One\AppBundle\Form\Employee\EmployeeType;
use All4One\AppBundle\Manager\EmailManager;
use All4One\AppBundle\Manager\EmployeeCvManager;
use All4One\AppBundle\Manager\TrackedTokenManager;
use All4One\AppBundle\Manager\UserManager;
use All4One\AppBundle\Traits\ControllerUtilsTrait;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvEducation;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvExperience;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Entity\User\User;
use Common\CoreBundle\Entity\Util\TrackedToken;
use Common\CoreBundle\Enumeration\User\UserStatusEnum;
use Common\CoreBundle\Enumeration\Util\TrackedTokenStatusEnum;
use Common\CoreBundle\Enumeration\Util\TrackedTokenTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage the employee security(registration, activation, email and password change).
 */
class EmployeeSecurityController extends AbstractController
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
     * @var EmployeeCvManager
     */
    private $employeeCvManager;

    /**
     * EmployeeSecurityController constructor.
     *
     * @param EmailManager        $emailManager
     * @param TrackedTokenManager $trackedTokenManager
     * @param UserManager         $userManager
     * @param EmployeeCvManager   $employeeCvManager
     */
    public function __construct(
        EmailManager $emailManager,
        TrackedTokenManager $trackedTokenManager,
        UserManager $userManager,
        EmployeeCvManager $employeeCvManager
    ) {
        $this->emailManager = $emailManager;
        $this->trackedTokenManager = $trackedTokenManager;
        $this->userManager = $userManager;
        $this->employeeCvManager = $employeeCvManager;
    }

    /**
     * @Route("/munkavallaloi-regisztracio", name="employee_registration", options={"sitemap" = {"priority" = 1 }})
     * @Route("/munkavallaloi-regisztracio/{id}", name="employee_registration_with_offer_apply")
     * @Method("GET")
     */
    public function employeeRegistration()
    {
        $token = TrackedToken::getRandomToken();

        return $this->render(
            'pages/employee/registration.html.twig',
            [
                'token' => $token,
                'employee_form' => $this->createForm(
                    EmployeeType::class, new Employee(),
                    [
                        'action' => $this->generateUrl('employee_account_registration', ['token' => $token]),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-form'],
                    ]
                )->createView(),
                'cv_work_details_form' => $this->createForm(
                    CvWorkDetailsType::class, new EmployeeCv(),
                    [
                        'action' => $this->generateUrl('employee_cv_registration_create', ['token' => $token]),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-work-details-form'],
                    ]
                )->createView(),
                'cv_extra_form' => $this->createForm(
                    CvExtraType::class, new EmployeeCv(),
                    [
                        'action' => $this->generateUrl('employee_cv_registration_extra_update', ['token' => $token]),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-extra-form'],
                    ]
                )->createView(),
                'cv_other_form' => $this->createForm(
                    CvOtherType::class, new EmployeeCv(),
                    [
                        'action' => $this->generateUrl('employee_cv_registration_other_update', ['token' => $token]),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-other-form'],
                    ]
                )->createView(),
                'cv_education_form' => $this->createForm(
                    CvEducationType::class, new EmployeeCvEducation(),
                    [
                        'action' => $this->generateUrl('employee_cv_registration_education_create', ['token' => $token]),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-education-form'],
                    ]
                )->createView(),
                'cv_experience_form' => $this->createForm(
                    CvExperienceType::class, new EmployeeCvExperience(),
                    [
                        'action' => $this->generateUrl('employee_cv_registration_experience_create', ['token' => $token]),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-experience-form'],
                    ]
                )->createView(),
            ]
        );
    }

    /**
     * @Route("/munkavallaloi-fiok-regisztralasa/{token}", name="employee_account_registration")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request $request
     * @param string  $token
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function registerEmployee(Request $request, string $token)
    {
        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        // Validate the form
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $this->userManager->create($employee);

        $trackedToken = new TrackedToken();
        $trackedToken->setUser($employee);
        $trackedToken->setType(TrackedTokenTypeEnum::create(TrackedTokenTypeEnum::EMPLOYEE_REGISTRATION));
        $trackedToken->setStatus(TrackedTokenStatusEnum::create(TrackedTokenStatusEnum::IN_USE));
        $trackedToken->setExpireDate(new \DateTime('+1 week'));
        $trackedToken->setUsedCounter(1);
        $trackedToken->setMaxUseTimes(6);
        $this->trackedTokenManager->create($trackedToken, $token);

        $activationToken = new TrackedToken();
        $activationToken->setUser($employee);
        $activationToken->setType(TrackedTokenTypeEnum::create(TrackedTokenTypeEnum::EMPLOYEE_ACTIVATION));
        $activationToken->setExpireDate(new \DateTime('+1 week'));
        $activationToken->setMaxUseTimes(1);
        $this->trackedTokenManager->create($activationToken);

        $this->emailManager->send($this->emailManager->getDefaultSender(), $employee->getEmail(), 'employee.registration', [
            'full_name' => $employee->getFullName(),
            'token' => $activationToken->getToken(),
        ]);

        return $this->json([
            'success' => 1,
        ]);
    }

    /**
     * @Route("/oneletrajz-letrehozasa/{token}", name="employee_cv_registration_create")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request      $request
     * @param TrackedToken $trackedToken
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createEmployeeCv(
        Request $request,
        TrackedToken $trackedToken
    ) {
        $em = $this->getDoctrine();
        $employeeRepository = $em->getRepository('CommonCoreBundle:Employee\Employee');
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        // On Update
        if (isset($trackedToken->getData()['employee_cv_id'])) {
            if (!isset($trackedToken->getData()['employee_cv_id'])
                || null === ($cv = $employeeCvRepository->findOneBy(['id' => $trackedToken->getData()['employee_cv_id']]))) {
                throw new \Exception('Invalid employee cv id');
            }
        } else {
            /** @var Employee $employee */
            $employee = $employeeRepository->findOneBy(['id' => $trackedToken->getUser()->getId()]);
            if (empty($trackedToken->getUser())
                || null === ($employee)) {
                throw new \Exception('Invalid employee id');
            }
            $cv = new EmployeeCv();
            $cv->setEmployee($employee);
        }

        $form = $this->createForm(CvWorkDetailsType::class, $cv);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $this->employeeCvManager->save($cv);

        $trackedToken->appendData('employee_cv_id', $cv->getId());
        $this->trackedTokenManager->save($trackedToken, (1 != $trackedToken->getUsedCounter()));

        return $this->json([
            'success' => 1,
        ]);
    }

    /**
     * @Route("/oneletetrajz-mentese/{token}", name="employee_cv_registration_extra_update")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request      $request
     * @param TrackedToken $trackedToken
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateEmployeeCvExtra(Request $request, TrackedToken $trackedToken)
    {
        $em = $this->getDoctrine();
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        /** @var EmployeeCv $cv */
        $cv = $employeeCvRepository->findOneBy(['id' => $trackedToken->getData()['employee_cv_id']]);

        // On Update
        if (!isset($trackedToken->getData()['employee_cv_id'])
            || null === ($cv)) {
            throw new \Exception('Invalid employee cv id');
        }

        $form = $this->createForm(CvExtraType::class, $cv);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        // Create the new user entity, and log that
        $this->employeeCvManager->save($cv);

        $this->trackedTokenManager->save($trackedToken);

        return $this->json([
            'success' => 1,
        ]);
    }

    /**
     * @Route("/egyeb-adatok-mentese/{token}", name="employee_cv_registration_other_update")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request      $request
     * @param TrackedToken $trackedToken
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateEmployeeCvOther(
        Request $request,
        TrackedToken $trackedToken
    ) {
        $em = $this->getDoctrine();
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        /** @var EmployeeCv $cv */
        $cv = $employeeCvRepository->findOneBy(['id' => $trackedToken->getData()['employee_cv_id']]);
        if (!isset($trackedToken->getData()['employee_cv_id'])
            || null === ($cv)) {
            throw new \Exception('Invalid employee cv id');
        }

        $form = $this->createForm(CvOtherType::class, $cv);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $this->employeeCvManager->save($cv);

        $trackedToken->setUsedCounter(6);
        $this->trackedTokenManager->save($trackedToken, true);

        return $this->json([
            'success' => 1,
        ]);
    }

    /**
     * @Route("/vegzettseg-hozzadadasa/{token}", name="employee_cv_registration_education_create")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request      $request
     * @param TrackedToken $trackedToken
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createEmployeeCvEducation(
        Request $request,
        TrackedToken $trackedToken
    ) {
        $em = $this->getDoctrine();
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        /** @var EmployeeCv $cv */
        $cv = $employeeCvRepository->findOneBy(['id' => $trackedToken->getData()['employee_cv_id']]);
        if (!isset($trackedToken->getData()['employee_cv_id'])
            || null === ($cv)) {
            throw new \Exception('Invalid employee cv id');
        }

        $education = new EmployeeCvEducation();
        $education->setEmployeeCv($cv);
        $form = $this->createForm(CvEducationType::class, $education);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }
        $this->employeeCvManager->saveEducation($education);
        if (3 != $trackedToken->getUsedCounter()) {
            $trackedToken->setUsedCounter(4);
        }
        $this->trackedTokenManager->save($trackedToken, true);

        return $this->json([
            'success' => 1,
            'id' => $education->getId(),
        ]);
    }

    /**
     * @Route("/vegzettseg-torlese/{token}/{id}", name="employee_cv_registration_education_delete")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param EmployeeCvEducation $education
     * @param TrackedToken        $trackedToken
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function deleteEmployeeCvEducation(EmployeeCvEducation $education, TrackedToken $trackedToken)
    {
        $em = $this->getDoctrine();
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        /** @var EmployeeCv $cv */
        $cv = $employeeCvRepository->findOneBy(['id' => $trackedToken->getData()['employee_cv_id']]);
        if (!isset($trackedToken->getData()['employee_cv_id'])
            || null === ($cv)) {
            throw new \Exception('Invalid employee cv id');
        }

        $educationId = $education->getId();
        $this->employeeCvManager->deleteEducation($education);

        return $this->json([
            'success' => 1,
            'id' => $educationId,
        ]);
    }

    /**
     * @Route("/vegzettseg-modositasa/{token}/{id}", name="employee_cv_registration_education_edit")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request             $request
     * @param EmployeeCvEducation $education
     * @param TrackedToken        $trackedToken
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function editEmployeeCvEducation(Request $request, EmployeeCvEducation $education, TrackedToken $trackedToken): JsonResponse
    {
        $em = $this->getDoctrine();
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        /** @var EmployeeCv $cv */
        $cv = $employeeCvRepository->findOneBy(['id' => $trackedToken->getData()['employee_cv_id']]);
        if (!isset($trackedToken->getData()['employee_cv_id'])
            || null === ($cv)) {
            throw new \Exception('Invalid employee cv id');
        }

        $form = $this->createForm(CvEducationType::class, $education);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $this->employeeCvManager->saveEducation($education);

        return $this->json([
            'success' => 1,
            'id' => $education->getId(),
        ]);
    }

    /**
     * @Route("/tapasztalat-hozzadadasa/{token}", name="employee_cv_registration_experience_create")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request      $request
     * @param TrackedToken $trackedToken
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createEmployeeCvExperience(
        Request $request,
        TrackedToken $trackedToken
    ) {
        $em = $this->getDoctrine();
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        /** @var EmployeeCv $cv */
        $cv = $employeeCvRepository->findOneBy(['id' => $trackedToken->getData()['employee_cv_id']]);
        if (!isset($trackedToken->getData()['employee_cv_id'])
            || null === ($cv)) {
            throw new \Exception('Invalid employee cv id');
        }

        $experience = new EmployeeCvExperience();
        $experience->setEmployeeCv($cv);
        $form = $this->createForm(CvExperienceType::class, $experience);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }
        $this->employeeCvManager->saveExperience($experience);

        if (4 != $trackedToken->getUsedCounter() && 3 != $trackedToken->getUsedCounter()) {
            $trackedToken->setUsedCounter(5);
        }
        $this->trackedTokenManager->save($trackedToken, true);

        return $this->json([
            'success' => 1,
            'id' => $experience->getId(),
        ]);
    }

    /**
     * @Route("/tapasztalat-torlese/{token}/{id}", name="employee_cv_registration_experience_delete")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param EmployeeCvExperience $experience
     * @param TrackedToken         $trackedToken
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function deleteEmployeeCvExperience(EmployeeCvExperience $experience, TrackedToken $trackedToken)
    {
        $em = $this->getDoctrine();
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        /** @var EmployeeCv $cv */
        $cv = $employeeCvRepository->findOneBy(['id' => $trackedToken->getData()['employee_cv_id']]);
        if (!isset($trackedToken->getData()['employee_cv_id'])
            || null === ($cv)) {
            throw new \Exception('Invalid employee cv id');
        }

        $experienceId = $experience->getId();
        $this->employeeCvManager->deleteExperience($experience);

        return $this->json([
            'success' => 1,
            'id' => $experienceId,
        ]);
    }

    /**
     * @Route("/tapasztalat-modositasa/{token}/{id}", name="employee_cv_registration_experience_edit")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request              $request
     * @param EmployeeCvExperience $experience
     * @param TrackedToken         $trackedToken
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function editEmployeeCvExperience(Request $request, EmployeeCvExperience $experience, TrackedToken $trackedToken): JsonResponse
    {
        $em = $this->getDoctrine();
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        /** @var EmployeeCv $cv */
        $cv = $employeeCvRepository->findOneBy(['id' => $trackedToken->getData()['employee_cv_id']]);
        if (!isset($trackedToken->getData()['employee_cv_id'])
            || null === ($cv)) {
            throw new \Exception('Invalid employee cv id');
        }

        $form = $this->createForm(CvExperienceType::class, $experience);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $this->employeeCvManager->saveExperience($experience);

        return $this->json([
            'success' => 1,
            'id' => $experience->getId(),
        ]);
    }

    /**
     * @Route("/munkavallaloi-fiok-aktivalasa/{token}", name="employee_account_activation")
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
    public function activateEmployee(TrackedToken $trackedToken, Request $request)
    {
        $em = $this->getDoctrine();
        $userRepository = $em->getRepository('CommonCoreBundle:User\User');
        $success = false;

        /** @var User $user */
        if (!empty($user = $userRepository->findOneBy(['id' => $trackedToken->getUser()->getId()]))) {
            $user->setStatus(UserStatusEnum::create(UserStatusEnum::ACTIVE));
            $this->userManager->save($user);
            $this->userManager->logUserIn($user, $request);
            $trackedToken->setStatus(TrackedTokenStatusEnum::create(TrackedTokenStatusEnum::USED));
            $this->trackedTokenManager->save($trackedToken);
            $success = true;
        }

        return $this->render('pages/employee/activate.html.twig', ['activated' => $success]);
    }

    /**
     * @Route("/munkavallaloi-fiok-torolve/{token}", name="employee_account_deletion_landing")
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
    public function deletedEmployeeAccount(TrackedToken $trackedToken, TrackedTokenManager $trackedTokenManager)
    {
        $trackedTokenManager->save($trackedToken);

        return $this->render('pages/employee/deleted.html.twig');
    }
}
