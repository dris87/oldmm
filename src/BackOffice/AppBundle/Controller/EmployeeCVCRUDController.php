<?php


namespace BackOffice\AppBundle\Controller;

use Common\CoreBundle\Entity\Employee\Cv\EmployeeCv;
use Common\CoreBundle\Entity\Employee\Employee;
use Knp\Snappy\GeneratorInterface;
use Knp\Snappy\Pdf;
use Sonata\AdminBundle\Controller\CRUDController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EmployeeCVCRUDController extends BaseController
{
    /**
     * @var GeneratorInterface
     */
    private $pdf;

    /**
     * EmployeeCRUDController constructor.
     *
     * @param GeneratorInterface $pdf
     */
    public function __construct(GeneratorInterface $pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function generateFullAction($id)
    {
        return $this->generatePDF($this->admin->getObject($id), false);
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function generateLightAction($id)
    {
        return $this->generatePDF($this->admin->getObject($id), true);
    }

    /**
     * @param EmployeeCv $employeeCv
     * @param $isLight
     *
     * @return RedirectResponse|Response
     */
    public function generatePDF(EmployeeCv $employeeCv, $isLight)
    {
        if ($employeeCv) {
            var_dump($employeeCv);
            exit;
            $root = $this->getParameter('kernel.project_dir');
            $header = $this->renderView('@BackOfficeApp/employee/cv/header.html.twig', [
                'root' => $root,
                'date' => new \DateTime(),
            ]);
            $footer = $this->renderView('@BackOfficeApp/employee/cv/footer.html.twig', [
                'homesite_url' => 'www.mumi.hu',
                'date' => new \DateTime(),
            ]);

            $body = $this->renderView('@BackOfficeApp/employee/cv/cv.html.twig', [
                'root' => $root,
                'cv' => $employeeCv,
                'isLight' => $isLight,
            ]);

            return new Response(
                $this->pdf->getOutputFromHtml($body, [
                    'header-html' => $header,
                    'footer-html' => $footer,
                    'margin-left' => 0,
                    'margin-right' => 0,
                    'margin-bottom' => 20,
                    'margin-top' => 21,
                    'disable-smart-shrinking' => false,
                    'dpi' => 100,
                    'page-size' => 'A4',
                    'images' => true,
                    'load-media-error-handling' => 'abort',
                    'enable-local-file-access' => true,
                    'print-media-type' => true,
                    'image-dpi' => 1000,
                    'image-quality' => 300,
                ]),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.'cv'.'.pdf"',
                ]
            );
        }

        $this->addFlash('error', 'Nem kapcsolódik CV a/az '.$employeeCv->getEmployee()->getFullName().' nevű munkavállalóhoz!');

        return new RedirectResponse($this->admin->generateUrl('list', ['filter' => $this->admin->getFilterParameters()]));
    }
}
