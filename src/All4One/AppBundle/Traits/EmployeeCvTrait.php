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

namespace All4One\AppBundle\Traits;

use All4One\AppBundle\Manager\EmployeeCvManager;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvEducation;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCvExperience;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait EmployeeCvTrait.
 */
trait EmployeeCvTrait
{
    /**
     * @var EmployeeCvManager
     */
    private $employeeCvManager;

    /**
     * EmployeeCvController constructor.
     *
     * @param EmployeeCvManager $employeeCvManager
     */
    public function __construct(EmployeeCvManager $employeeCvManager)
    {
        $this->employeeCvManager = $employeeCvManager;
    }

    /**
     * @param Request       $request
     * @param FormInterface $form
     * @param EmployeeCv    $employeeCv
     *
     * @return JsonResponse
     */
    private function menageEmployeeCv(Request $request, FormInterface $form, EmployeeCv $employeeCv)
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }
        $this->employeeCvManager->save($employeeCv);

        return $this->json([
            'id' => $employeeCv->getId(),
            'success' => 1,
        ]);
    }

    /**
     * @param Request             $request
     * @param FormInterface       $form
     * @param EmployeeCvEducation $education
     *
     * @return JsonResponse
     */
    private function menageCvEducation(Request $request, FormInterface $form, EmployeeCvEducation $education): JsonResponse
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $employee = $education->getEmployeeCv();
        $employee->addEducation($education);

        $this->employeeCvManager->save($employee);

        return $this->json([
            'success' => 1,
            'id' => $education->getId(),
        ]);
    }

    /**
     * @param Request              $request
     * @param FormInterface        $form
     * @param EmployeeCvExperience $experience
     *
     * @return JsonResponse
     */
    private function menageCvExperience(Request $request, FormInterface $form, EmployeeCvExperience $experience): JsonResponse
    {
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
}
