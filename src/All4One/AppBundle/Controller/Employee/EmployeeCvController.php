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
use All4One\AppBundle\Manager\EmployeeCvManager;
use All4One\AppBundle\Traits\ControllerUtilsTrait;
use All4One\AppBundle\Traits\EmployeeCvTrait;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvEducation;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvExperience;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvStatusEnum;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvStyleEnum;
use Knp\Snappy\GeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/munkavallalo/oneletrajzaim")
 * @Security("has_role('ROLE_EMPLOYEE')")
 */
class EmployeeCvController extends AbstractController
{
    use ControllerUtilsTrait, EmployeeCvTrait;

    /**
     * @Route("/", name="employee_cv_index")
     * @Route("/lista", name="employee_cv_list")
     * @Method("GET")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('pages/employee/cv_list.html.twig');
    }

    /**
     * @Route("/cv-statuszvaltas/{id}", name="employee_cv_status_toggle")
     * @Method("POST")
     * TODO: Refactor to cvStatusChange(EmployeeCv $employeeCv, int $status, TranslatorInterface $translator)
     *
     * @param EmployeeCv          $employeeCv
     * @param TranslatorInterface $translator
     * @param EmployeeCvManager   $employeeCvManager
     *
     * @return Response
     */
    public function employeeCvStatusToggle(EmployeeCv $employeeCv, TranslatorInterface $translator, EmployeeCvManager $employeeCvManager): Response
    {
        $active = EmployeeCvStatusEnum::create(EmployeeCvStatusEnum::ACTIVE);
        $inactive = EmployeeCvStatusEnum::create(EmployeeCvStatusEnum::INACTIVE);

        $employeeCv->setStatus(($employeeCv->getStatus() != $active) ? $active : $inactive);
        $employeeCvManager->save($employeeCv);

        $title = $translator->trans('notification.employee_cv.status_change.title');
        $message = $translator->trans('notification.employee_cv.status_change.message', ['%status%' => $translator->trans(EmployeeCvStatusEnum::getReadables()[$employeeCv->getStatus()->getValue()])]);

        return $this->json([
            'title' => $title,
            'message' => $message,
            'success' => 1,
        ]);
    }

    /**
     * @Route("/oneletrajz-modositasa/{id}", name="employee_cv_edit")
     * @Route("/oneletrajz-keszitese", name="employee_cv_new")
     * @Method("GET")
     *
     * @param EmployeeCv|null $employeeCv
     *
     * @return Response
     */
    public function editCv(?EmployeeCv $employeeCv)
    {
        if (null !== $employeeCv) {
            $new = false;
            $this->denyAccessUnlessGranted('ROLE_EMPLOYEE_CV_EDIT', $employeeCv);
        } else {
            $new = true;
            $employeeCv = new EmployeeCv();
            $this->denyAccessUnlessGranted('ROLE_EMPLOYEE_CV_CREATE', $employeeCv);
        }

        return $this->render(
            'pages/employee/cv_menage.html.twig',
            [
                'cv' => $employeeCv,
                'educations' => $employeeCv->getEducationsArray(),
                'experiences' => $employeeCv->getExperiencesArray(),
                'cv_work_details_form' => $this->createForm(
                    CvWorkDetailsType::class, $employeeCv,
                    [
                        'action' => (!$new) ?
                            $this->generateUrl('employee_cv_menage_action', ['id' => $employeeCv->getId()])
                            : $this->generateUrl('employee_cv_create_action'),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-work-details-form', 'novalidate' => 'novalidate'],
                    ]
                )->createView(),
                'cv_extra_form' => $this->createForm(
                    CvExtraType::class, $employeeCv,
                    [
                        'action' => $this->generateUrl('employee_cv_extra_menage', ['id' => (!$new) ? $employeeCv->getId() : '0']),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-extra-form', 'novalidate' => 'novalidate'],
                    ]
                )->createView(),
                'cv_education_form' => $this->createForm(
                    CvEducationType::class, new EmployeeCvEducation(),
                    [
                        'action' => $this->generateUrl('employee_cv_education_create', ['id' => (!$new) ? $employeeCv->getId() : '0']),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-education-form', 'novalidate' => 'novalidate'],
                    ]
                )->createView(),
                'cv_experience_form' => $this->createForm(
                    CvExperienceType::class, new EmployeeCvExperience(),
                    [
                        'action' => $this->generateUrl('employee_cv_experience_create', ['id' => (!$new) ? $employeeCv->getId() : '0']),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-experience-form', 'novalidate' => 'novalidate'],
                    ]
                )->createView(),
                'cv_other_form' => $this->createForm(
                    CvOtherType::class, $employeeCv,
                    [
                        'action' => $this->generateUrl('employee_cv_other_menage', ['id' => (!$new) ? $employeeCv->getId() : '0']),
                        'method' => 'POST',
                        'attr' => ['id' => 'employee-cv-other-form', 'novalidate' => 'novalidate'],
                    ]
                )->createView(),
            ]
        );
    }

    /**
     * @Route("/oneletrajz-modositasa-action/{id}", name="employee_cv_menage_action")
     * @Route("/oneletrajz-letrehozasa", name="employee_cv_create_action")
     * @Method("POST")
     *
     * @param Request    $request
     * @param EmployeeCv $employeeCv
     *
     * @return JsonResponse
     */
    public function menageCv(Request $request, ?EmployeeCv $employeeCv)
    {
        $employeeCv = (null !== $employeeCv) ? $employeeCv : new EmployeeCv();
        $employeeCv->setEmployee($this->getUser());

        $form = $this->createForm(CvWorkDetailsType::class, $employeeCv);

        return $this->menageEmployeeCv($request, $form, $employeeCv);
    }

    /**
     * @Route("/oneletetrajz-mentese/{id}", name="employee_cv_extra_menage")
     * @Method("POST")
     *
     * @param Request    $request
     * @param EmployeeCv $employeeCv
     *
     * @return JsonResponse
     */
    public function updateEmployeeCvExtra(Request $request, EmployeeCv $employeeCv)
    {
        $form = $this->createForm(CvExtraType::class, $employeeCv);

        return $this->menageEmployeeCv($request, $form, $employeeCv);
    }

    /**
     * @Route("/egyeb-adatok-mentese/{id}", name="employee_cv_other_menage")
     * @Method("POST")
     *
     * @param Request    $request
     * @param EmployeeCv $employeeCv
     *
     * @return JsonResponse
     */
    public function updateEmployeeCvOther(Request $request, EmployeeCv $employeeCv)
    {
        $form = $this->createForm(CvOtherType::class, $employeeCv);

        return $this->menageEmployeeCv($request, $form, $employeeCv);
    }

    /**
     * @Route("/vegzettseg-hozzadadasa/{id}", name="employee_cv_education_create")
     * @Method("POST")
     *
     * @param Request    $request
     * @param EmployeeCv $employeeCv
     *
     * @return JsonResponse
     */
    public function createEmployeeCvEducation(Request $request, EmployeeCv $employeeCv): JsonResponse
    {
        $education = new EmployeeCvEducation();
        $education->setEmployeeCv($employeeCv);
        $form = $this->createForm(CvEducationType::class, $education);

        return $this->menageCvEducation($request, $form, $education);
    }

    /**
     * @Route("/vegzettseg-modositasa/{id}", name="employee_cv_education_edit")
     * @Method("POST")
     *
     * @param Request             $request
     * @param EmployeeCvEducation $education
     *
     * @return JsonResponse
     */
    public function editEmployeeCvEducation(Request $request, EmployeeCvEducation $education): JsonResponse
    {
        $form = $this->createForm(CvEducationType::class, $education);

        return $this->menageCvEducation($request, $form, $education);
    }

    /**
     * @Route("/vegzettseg-torlese/{id}", name="employee_cv_education_delete")
     * @Method("POST")
     *
     * @param EmployeeCvEducation $education
     *
     * @return JsonResponse
     */
    public function deleteEmployeeCvEducation(EmployeeCvEducation $education): JsonResponse
    {
        $educationId = $education->getId();
        $this->employeeCvManager->deleteEducation($education);

        return $this->json([
            'success' => 1,
            'id' => $educationId,
        ]);
    }

    /**
     * @Route("/tapasztalat-hozzadadasa/{id}", name="employee_cv_experience_create")
     * @Method("POST")
     *
     * @param Request    $request
     * @param EmployeeCv $employeeCv
     *
     * @return JsonResponse
     */
    public function createEmployeeCvExperience(Request $request, EmployeeCv $employeeCv): JsonResponse
    {
        $education = new EmployeeCvExperience();
        $education->setEmployeeCv($employeeCv);
        $form = $this->createForm(CvExperienceType::class, $education);

        return $this->menageCvExperience($request, $form, $education);
    }

    /**
     * @Route("/tapasztalat-modositasa/{id}", name="employee_cv_experience_edit")
     * @Method("POST")
     *
     * @param Request              $request
     * @param EmployeeCvExperience $experience
     *
     * @return JsonResponse
     */
    public function editEmployeeCvExperience(Request $request, EmployeeCvExperience $experience): JsonResponse
    {
        $form = $this->createForm(CvExperienceType::class, $experience);

        return $this->menageCvExperience($request, $form, $experience);
    }

    /**
     * @Route("/tapasztalat-torlese/{id}", name="employee_cv_experience_delete")
     * @Method("POST")
     *
     * @param EmployeeCvExperience $experience
     *
     * @return JsonResponse
     */
    public function deleteEmployeeCvExperience(EmployeeCvExperience $experience): JsonResponse
    {
        $experienceId = $experience->getId();
        $this->employeeCvManager->deleteExperience($experience);

        return $this->json([
            'success' => 1,
            'id' => $experienceId,
        ]);
    }

    /**
     * @Route("/profilkep-feltoltese", name="employee_upload_")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request $request
     * @param string  $token
     */
    public function uploadEmployeePicture(Request $request, string $token)
    {
        exit('ok');
    }

    /**
     * @Route("/dokumentum-feltoltese", name="employee_cv_upload_document")
     * @Method("POST")
     * @Entity("trackedToken", expr="repository.loadByToken(token)")
     *
     * @param Request $request
     * @param string  $token
     */
    public function uploadEmployeeCvDocument(Request $request, string $token)
    {
        exit('ok');
    }

    /**
     * @Route("/pdf-generalas/{id}", name="employee_cv_generate_pdf")
     * @Method("GET")
     *
     * @param Request            $request
     * @param EmployeeCv         $employeeCv
     * @param GeneratorInterface $pdf
     * @param KernelInterface    $kernel
     * @param EmployeeCvManager  $employeeCvManager
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function generatePdf(
        EmployeeCv $employeeCv,
        GeneratorInterface $pdf,
        KernelInterface $kernel,
        EmployeeCvManager $employeeCvManager
    ) {
        if (!$this->getUser() instanceof Employee || $this->getUser() !== $employeeCv->getEmployee()) {
            throw new \Exception('Invalid employee');
        }
        
        $now = new \DateTime();
        $root = $kernel->getProjectDir();
        $style = EmployeeCvStyleEnum::create(EmployeeCvStyleEnum::UJALLAS_ORANGE); //$employeeCv->getStyle();

        $header = ($style->hasHeader()) ?
            $this->renderView('@BackOfficeApp/employee/cv/'.$style->getDirName().'/header.html.twig', [
                'root' => $root,
                'date' => $now,
            ]) :
            false;

        $footer = ($style->hasFooter()) ?
            $this->renderView('@BackOfficeApp/employee/cv/'.$style->getDirName().'/footer.html.twig', [
                'homesite_url' => 'www.mumi.hu',
                'date' => $now,
            ]) :
            false;
        $body = $this->renderView('@BackOfficeApp/employee/cv/'.$style->getDirName().'/cv.html.twig', [
            'root' => $root,
            'cv' => $employeeCv,
            'isLight' => false,
            'color' => $style->getColor(),
            'date' => $now,
        ]);
        $name = $employeeCv->getGeneratedFileName();

        return $employeeCvManager->generatePdf($body, $pdf, $header, $footer, $style->getPdfOptions(), $name);
    }
}
