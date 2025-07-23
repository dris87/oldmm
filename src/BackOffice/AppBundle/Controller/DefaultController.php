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

use All4One\AppBundle\Manager\EmployeeCvManager;
use Common\CoreBundle\Entity\Development\Documentation\DocumentationTopic;
use Common\CoreBundle\Enumeration\Employee\Cv\EmployeeCvStyleEnum;
use Knp\Snappy\GeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class DefaultController.
 * @Security("has_role('ROLE_ADMIN')")
 */
class DefaultController extends Controller
{
    /**
     * @var GeneratorInterface
     */
    private $pdf;

    /**
     * DefaultController constructor.
     *
     * @param GeneratorInterface $pdf
     */
    public function __construct(GeneratorInterface $pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * @Route("/admin", name="admin_homepage")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return $this->render('BackOffice\AppBundle:default/page.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/generate/{id}", name="generate")
     *
     * @param $id
     * @param KernelInterface    $kernel
     * @param EmployeeCvManager  $employeeCvManager
     * @param GeneratorInterface $pdf
     *
     * @return Response
     */
    public function generateAction($id, KernelInterface $kernel, EmployeeCvManager $employeeCvManager, GeneratorInterface $pdf)
    {
        $employeeCv = $this->getDoctrine()->getRepository("Common\CoreBundle\Entity\Employee\Cv\EmployeeCv")->find($id);

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

    /**
     * @Route("/generate_light/{id}", name="generate_light")
     *
     * @param $id
     * @param KernelInterface    $kernel
     * @param EmployeeCvManager  $employeeCvManager
     * @param GeneratorInterface $pdf
     *
     * @return Response
     */
    public function generateLightAction($id, KernelInterface $kernel, EmployeeCvManager $employeeCvManager, GeneratorInterface $pdf)
    {
        $employeeCv = $this->getDoctrine()->getRepository("Common\CoreBundle\Entity\Employee\Cv\EmployeeCv")->find($id);

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
            'isLight' => true,
            'color' => $style->getColor(),
            'date' => $now,
        ]);
        $name = $employeeCv->getGeneratedFileName();

        return $employeeCvManager->generatePdf($body, $pdf, $header, $footer, $style->getPdfOptions(), $name);
    }

    /**
     * @Route("/development", name="development_home")
     */
    public function development(): Response
    {
        $em = $this->getDoctrine();
        $topicsRepository = $em->getRepository('CommonCoreBundle:Development\Documentation\DocumentationTopic');

        return $this->render('development/pages/index.html.twig', [
            'topics' => $topicsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/development/page/{id}", name="development_page")
     *
     * @param DocumentationTopic $documentationTopic
     *
     * @return Response
     */
    public function page(DocumentationTopic $documentationTopic): Response
    {
        return $this->render('development/pages/page.html.twig', [
            'topic' => $documentationTopic,
        ]);
    }

}
