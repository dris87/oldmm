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

use All4One\AppBundle\Form\Employee\AdvancedCvSearchType;
use All4One\AppBundle\Form\Firm\FirmDetailsType;
use All4One\AppBundle\Manager\EmployeeCvManager;
use All4One\AppBundle\Manager\Filter\AdvancedCvQueryFilter;
use All4One\AppBundle\Manager\FirmManager;
use All4One\AppBundle\Traits\ControllerUtilsTrait;
use Common\CoreBundle\Doctrine\Repository\Employee\Cv\EmployeeCvRepository;
use Common\CoreBundle\Doctrine\Repository\Firm\FirmCvRepository;
use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Employee;
use Common\CoreBundle\Entity\Firm\FirmColleague;
use Common\CoreBundle\Entity\Firm\FirmCv;
use Common\CoreBundle\Entity\Offer\Offer;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvStyleEnum;
use Common\CoreBundle\Manager\Firm\FirmBalanceManager;
use Knp\Snappy\GeneratorInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Controller used to manage firm data.
 *
 * @Route("/cegfiok")
 * @Security("has_role('ROLE_COLLEAGUE')")
 */
class FirmController extends AbstractController
{
    use ControllerUtilsTrait;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * FirmController constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="firm_index")
     * @Route("/", name="firm_dashboard")
     * @Method("GET")
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('pages/firm/dashboard.html.twig');
    }

    /**
     * @Route("/ceginfo", name="firm_details_index")
     * @Method("GET")
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function menageDetails(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof FirmColleague) {
            throw new \Exception('Invalid firm colleague');
        }

        $firm = $user->getFirm();

        return $this->render('pages/firm/details.html.twig', [
            'firm_details_form' => $this->createForm(
                FirmDetailsType::class, $firm,
                [
                    'action' => $this->generateUrl('firm_menage_details_action'),
                    'method' => 'POST',
                    'attr' => ['id' => 'firm-details-form', 'novalidate' => 'novalidate'],
                ]
            )->createView(),
        ]);
    }

    /**
     * @Route("/cegadatok-modositasa", name="firm_menage_details_action")
     * @Method("POST")
     *
     * @param Request     $request
     * @param FirmManager $firmManager
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function menageDetailsAction(Request $request, FirmManager $firmManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof FirmColleague) {
            throw new \Exception('Invalid firm colleague');
        }

        $firm = $user->getFirm();

        $form = $this->createForm(FirmDetailsType::class, $firm);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->json([
                'success' => 0,
                'error' => $this->getErrorMessages($form),
            ]);
        }

        $firmManager->save($firm);

        $title = $this->translator->trans('notification.firm.details.update.title');
        $message = $this->translator->trans('notification.firm.details.update.message');

        return $this->json([
            'title' => $title,
            'message' => $message,
            'success' => 1,
        ]);
    }

    /**
     * @Route("/oneletrajzaim", defaults={"page" = "1" }, name="firm_cvs_list")
     * @Route("/oneletrajzaim/oldal/{page}", requirements={"page" = "[1-9]\d*"}, name="firm_cvs_list_paginated")
     * @Method("GET")
     *
     * @param Request               $request
     * @param int                   $page
     * @param AdvancedCvQueryFilter $filterService
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return Response
     */
    public function cvs(Request $request, int $page, AdvancedCvQueryFilter $filterService): Response
    {
        $orderRepo = $this->getDoctrine()->getRepository('CommonCoreBundle:Firm\Order\FirmOrder');
        $orders = $orderRepo->findWithLivePackageByFirm($this->getUser()->getFirm());

        $em = $this->getDoctrine();
        $firm = $this->getUser()->getFirm();
        /** @var FirmCvRepository $firmCvRepository */
        $firmCvRepository = $em->getRepository('CommonCoreBundle:Firm\FirmCv');
        $cvQuery = $firmCvRepository->getFirmCvsQuery($firm);

        /** @var FormInterface $advanced_cv_search_form */
        $advanced_cv_search_form = $this->container->get('form.factory')->createNamed(
            'search',
            AdvancedCvSearchType::class, null,
            [
                'action' => $this->generateUrl('firm_cvs_list'),
                'method' => 'GET',
            ]
        );
        $advanced_cv_search_form->handleRequest($request);

        if ($advanced_cv_search_form->isSubmitted() && $advanced_cv_search_form->isValid()) {
            $presentation = $advanced_cv_search_form->getData();
            $filterService->filterQueryBuilderByPresentation($cvQuery, $presentation);
        }
        $paginator = new Pagerfanta(new DoctrineORMAdapter($cvQuery->getQuery()));
        $paginator->setMaxPerPage(FirmCv::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $this->render('pages/firm/cv_list.html.twig', [
            'advanced_cv_search_form' => $advanced_cv_search_form->createView(),
            'paginator' => $paginator,
            'firm' => $firm,
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/pdf-generalas/{id}", name="firm_generate_pdf")
     * @Method("GET")
     *
     * @param EmployeeCv         $employeeCv
     * @param GeneratorInterface $pdf
     * @param KernelInterface    $kernel
     * @param EmployeeCvManager  $employeeCvManager
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function generatePdf(EmployeeCv $employeeCv, GeneratorInterface $pdf, KernelInterface $kernel, EmployeeCvManager $employeeCvManager)
    {
        $em = $this->getDoctrine();
        $firmCvRepository = $em->getRepository('CommonCoreBundle:Firm\FirmCv');

        $style = EmployeeCvStyleEnum::create(EmployeeCvStyleEnum::UJALLAS_ORANGE); //$employeeCv->getStyle();

        $now = new \DateTime();
        $root = $kernel->getProjectDir();
        $is_light = null === $firmCvRepository->findOneBy(['employeeCv' => $employeeCv, 'firm' => $this->getUser()->getFirm()]);
        $name = $employeeCv->getGeneratedFileName($is_light);

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
            'color' => $style->getColor(),
            'date' => $now,
            'isLight' => $is_light,
        ]);


        // dump($employeeCv);
        // exit;
        return $employeeCvManager->generatePdf($body, $pdf, $header, $footer, $style->getPdfOptions(), $name);
    }

    /**
     * @Route("/adatbazis-hozzaferes", defaults={"page" = "1" }, name="firm_database_access")
     * @Route("/adatbazis-hozzaferes/oldal/{page}", requirements={"page" = "[1-9]\d*"}, name="firm_database_access_paginated")
     * @Method("GET")
     *
     * @param Request               $request
     * @param int                   $page
     * @param AdvancedCvQueryFilter $filterService
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return Response
     */
    public function databaseAccess(Request $request, int $page, AdvancedCvQueryFilter $filterService): Response
    {
        $em = $this->getDoctrine();
        /** @var EmployeeCvRepository $employeeCvRepository */
        $employeeCvRepository = $em->getRepository('CommonCoreBundle:Employee\Cv\EmployeeCv');
        $cvQuery = $employeeCvRepository->getDbCvsQuery();

        $orderRepo = $this->getDoctrine()->getRepository('CommonCoreBundle:Firm\Order\FirmOrder');
        $orders = $orderRepo->findWithLivePackageByFirm($this->getUser()->getFirm());

        /** @var FormInterface $advanced_cv_search_form */
        $advanced_cv_search_form = $this->container->get('form.factory')->createNamed(
            'search',
            AdvancedCvSearchType::class, null,
            [
                'action' => $this->generateUrl('firm_database_access'),
                'method' => 'GET',
            ]
        );
        $advanced_cv_search_form->handleRequest($request);

        if ($advanced_cv_search_form->isSubmitted() && $advanced_cv_search_form->isValid()) {
            $presentation = $advanced_cv_search_form->getData();
            $filterService->filterQueryBuilderByPresentation($cvQuery, $presentation);
        }
        $paginator = new Pagerfanta(new DoctrineORMAdapter($cvQuery->getQuery()));
        $paginator->setMaxPerPage(EmployeeCv::NUM_ITEMS);
        $paginator->setCurrentPage($page);

        return $this->render('pages/firm/database_access_list.html.twig', [
            'advanced_cv_search_form' => $advanced_cv_search_form->createView(),
            'paginator' => $paginator,
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/oneletrajz-feloldas/{employeeCvId}/{offerId}", name="firm_offer_cv_unlock")
     * @Method("POST")
     * @Entity("offer", expr="repository.find(offerId)")
     * @Entity("employeeCv", expr="repository.find(employeeCvId)")
     *
     * @param EmployeeCv          $employeeCv
     * @param Offer               $offer
     * @param FirmManager         $firmManager
     * @param TranslatorInterface $translator
     * @param FirmBalanceManager  $balanceManager
     *
     * @return Response
     */
    public function offerCvUnlock(EmployeeCv $employeeCv, Offer $offer, FirmManager $firmManager, TranslatorInterface $translator, FirmBalanceManager $balanceManager): Response
    {
        return $this->employeeCvUnlock($employeeCv, $firmManager, $translator, $balanceManager, $offer);
    }

    /**
     * @Route("/oneletrajz-feloldas/{id}", name="firm_cv_unlock")
     * @Method("POST")
     *
     * @param EmployeeCv          $employeeCv
     * @param FirmManager         $firmManager
     * @param TranslatorInterface $translator
     * @param FirmBalanceManager  $balanceManager
     *
     * @return Response
     */
    public function cvUnlock(EmployeeCv $employeeCv, FirmManager $firmManager, TranslatorInterface $translator, FirmBalanceManager $balanceManager): Response
    {
        return $this->employeeCvUnlock($employeeCv, $firmManager, $translator, $balanceManager);
    }

    /**
     * @param EmployeeCv          $employeeCv
     * @param FirmManager         $firmManager
     * @param TranslatorInterface $translator
     * @param FirmBalanceManager  $balanceManager
     * @param Offer|null          $offer
     *
     * @return JsonResponse
     */
    private function employeeCvUnlock(EmployeeCv $employeeCv, FirmManager $firmManager, TranslatorInterface $translator, FirmBalanceManager $balanceManager, Offer $offer = null)
    {
        if (!$this->isGranted('ROLE_CV_UNLOCK', $employeeCv)) {
            $title = $translator->trans('notification.firm.cv.unlock.no_cv_count.title');
            $message = $translator->trans('notification.firm.cv.unlock.no_cv_count.message');

            return $this->json([
                'title' => $title,
                'message' => $message,
                'success' => 0,
            ]);
        }

        $offerCandidate = null;

        if (is_object($offer) && !empty($offer)) {
            $offerCandidate = $firmManager->saveCandidate($employeeCv, $offer);
        }

        $firmManager->unlockCv($this->getUser()->getFirm(), $employeeCv, $offerCandidate);

        $title = $translator->trans('notification.firm.cv.unlock.success.title');
        $message = $translator->trans('notification.firm.cv.unlock.success.message');

        return $this->json([
            'title' => $title,
            'message' => $message,
            'success' => 1,
        ]);
    }
}
