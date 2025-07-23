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

use All4One\AppBundle\Form\Offer\SimpleSearchType;
use All4One\AppBundle\Form\Util\ContactType;
use All4One\AppBundle\Traits\ControllerUtilsTrait;
use All4One\NewsBundle\Manager\NewsManager;
use Common\CoreBundle\Doctrine\Repository\Offer\OfferRepository;
use Common\CoreBundle\Entity\Dictionary\DicCategory;
use Common\CoreBundle\Entity\Dictionary\DicCounty;
use Common\CoreBundle\Entity\Util\Contact;
use Common\CoreBundle\Entity\Util\LandingWork;
use Common\CoreBundle\Entity\Util\LandingWorking;
use Common\CoreBundle\Enumeration\Dictionary\DictionaryStatusEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use All4One\AppBundle\Manager\EmailManager;

/**
 * Class IndexController.
 *
 * @Route("")
 */
class IndexController extends AbstractController
{
    use ControllerUtilsTrait;


    /**
     * @Route("/", name="homepage", options={"sitemap" = true})
     *
     * @param NewsManager $newsManager
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return Response
     */
    public function index(NewsManager $newsManager, Request $request): Response
    {
        $em = $this->getDoctrine();

        /** @var OfferRepository $offerRepository */
        $offerRepository = $em->getRepository('CommonCoreBundle:Offer\Offer');
        $latestFeaturedOffers = $offerRepository->findLatestHighlightedTile(1);

        $simpleSearchForm = $this->container->get('form.factory')->createNamed(
            'search',
            SimpleSearchType::class, null,
            [
                'action' => $this->generateUrl('list_offers'),
                'method' => 'GET',
                'attr' => ['novalidate' => 'novalidate'],
            ]
        )->createView();

        $categories = $this->getDoctrine()->getRepository(DicCategory::class)->findBy(
            [
                'parentId' => null,
                'status' => DictionaryStatusEnum::create(DictionaryStatusEnum::ACTIVE),
            ], ['value' => 'ASC'], 26);
        $counties = $this->getDoctrine()->getRepository(DicCounty::class)->findBy([], ['value' => 'ASC'], 20);
        $firms = $em->getRepository("CommonCoreBundle:Firm\Firm")
                    ->createQueryBuilder('f')
                    ->where('f.status=:status')
                    ->andWhere('f.logoName IS NOT NULL')
                    ->andWhere('f.id NOT IN (:ids)')
                    ->setParameter('status', 1)
                    ->setParameter('ids', [393, 395, 397, 399, 401, 403, 405, 407, 493, 495, 497, 499, 503, 505, 507], \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                    ->getQuery()->getArrayResult();
        
        $cookies = $request->cookies;

        if ($cookies->has('dev'))
        {
            
            // foreach ($firms as $row) {
            //     var_dump($row);
            // exit;
            // }
            
        }
        return $this->render('pages/general/homepage.html.twig', [
            'featuredOffers' => $latestFeaturedOffers,
            'simpleSearchForm' => $simpleSearchForm,
            'categories' => $categories,
            'counties' => $counties,
            'posts' => $newsManager->getPostRepository()->findLatestPosts(),
            'cegek' => $firms,
         ]);
    }

    /**
     * @Route("/about_us", name="about_us", options={"sitemap" = {"priority" = 0.7 }})
     * @Route("/rolunk", name="about_us", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function aboutUs(Request $request): Response
    {
        $cookies = $request->cookies;

         if ($cookies->has('SYMFONY2_TEST'))
            {
                var_dump($cookies->get('SYMFONY2_TEST'));
                die();
            }
        return $this->render('pages/general/about_us.html.twig', []);
    }

    /**
     * @Route("/general-terms-and-conditions", name="terms", options={"sitemap" = {"priority" = 0.7 }})
     * @Route("/aszf", name="terms", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function terms(Request $request): Response
    {
        return $this->render('pages/general/terms.html.twig', []);
    }

    /**
     * @Route("/satisfaction-inquiry", name="inquiry", options={"sitemap" = {"priority" = 0.7 }})
     * @Route("/elegedettsegi-kerdoiv", name="inquiry", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function inquiry(): Response
    {
        return $this->redirect('https://forms.gle/M3QY6ijLWRmG1LZR9');
    }

    /**
     * @Route("/faq", name="faq", options={"sitemap" = {"priority" = 0.7 }})
     * @Route("/gyik", name="faq", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function faq(): Response
    {
        return $this->redirectToRoute('homepage');
        return $this->render('pages/general/faq.html.twig', []);
    }

    /**
     * @Route("/cookie-law", name="cookie_law_introduction", options={"sitemap" = {"priority" = 0.7 }})
     * @Route("/sutik-hasznalata", name="cookie_law_introduction", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function cookieLawIntroduction(): Response
    {
        return $this->render('pages/general/cookie_law_introduction.html.twig', []);
    }

    /**
     * @Route("/contact", name="contact", options={"sitemap" = {"priority" = 0.7 }})
     * @Route("/ugyfelszolgalat", name="contact", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function contact(): Response
    {
        // Az Enterprise reCAPTCHA site kulcs
        define('SITE_KEY', '6LeF2P0qAAAAAAgR-JnzqscuAfdVH8PFF_6hP5_V');

        return $this->render(
            'pages/general/contact.html.twig', [
                'site_key' => SITE_KEY,
                'contact_form' => $this->createForm(
                    ContactType::class, new Contact(),
                    [
                        'action' => $this->generateUrl('send_contact_message'),
                        'method' => 'POST',
                        'attr' => [
                            'id' => 'send-contact-message',
                        ],
                    ]
                )->createView(),
            ]
        );
    }

    /**
 * @Route("/ugyfelszolgalat-uzenet-kuldese", name="send_contact_message")
 * @Method("POST")
 */
public function sendContactMessage(Request $request, TranslatorInterface $translator, EmailManager $emailManager)
{
    // Enterprise verzió konfigurációja
    define('PROJECT_ID', 'static-reach-454622-a0');
    define('API_KEY', 'AIzaSyAao6gJuENASXee47Y_pK9fHR32iug1pu8');
    define('SITE_KEY', '6LeF2P0qAAAAAAgR-JnzqscuAfdVH8PFF_6hP5_V');
    
    // reCAPTCHA ellenőrzés
    $recaptchaToken = $request->request->get('g-recaptcha-response');

    if (empty($recaptchaToken)) {
        $this->addFlash('error', 'A reCAPTCHA ellenőrzés nem sikerült. Kérjük, próbálja újra.');
        return $this->redirectToRoute('contact');
    }
    
    // reCAPTCHA Enterprise API hívás
    $url = "https://recaptchaenterprise.googleapis.com/v1/projects/" . PROJECT_ID . "/assessments?key=" . API_KEY;

    $data = json_encode([
        "event" => [
            "token" => $recaptchaToken,
            "siteKey" => SITE_KEY,
            "expectedAction" => "submit"
        ]
    ]);

    // cURL használata a reCAPTCHA ellenőrzéséhez
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Ellenőrzés és hibajelzés
    if ($httpCode != 200) {
        $this->addFlash('error', 'A biztonsági ellenőrzés sikertelen volt. Kérjük, próbálja újra.');
        return $this->redirectToRoute('contact');
    }

    // Válasz feldolgozása
    $result = json_decode($response);

    // Token validálás ellenőrzése
    if (!isset($result->tokenProperties) || !$result->tokenProperties->valid) {
        $this->addFlash('error', 'A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra.');
        return $this->redirectToRoute('contact');
    }

    // Pontszám ellenőrzése
    if (isset($result->riskAnalysis) && isset($result->riskAnalysis->score) && $result->riskAnalysis->score < 0.3) {
        $this->addFlash('error', 'A biztonsági ellenőrzés nem sikerült. Kérjük, próbálja újra.');
        return $this->redirectToRoute('contact');
    }
    
    // Az űrlap feldolgozása
    $em = $this->getDoctrine()->getManager();
    $contact = new Contact();
    $form = $this->createForm(ContactType::class, $contact);
    $form->handleRequest($request);
    
    // Validate the contact form
    if (!$form->isSubmitted() || !$form->isValid()) {
        return $this->json([
            'success' => 0,
            'error' => $this->getErrorMessages($form),
        ]);
    }
    
    $emailManager->send(
        $emailManager->getDefaultSender(), 
        "ugyfelszolgalat@mumi.hu", 
        'contact', 
        ['contact' => $contact],
        $contact->getEmail()
    );
    
    return $this->json([
        'success' => 1,
    ]);
}

    /**
     * @Route("/cegtajekoztato", name="sales_landingpage", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function salesLanding(Request $request): Response
    {
        
        return $this->render('pages/landing_pages/sales_landing.html.twig');
    }

    /**
     * @Route("/allast-keresek", name="work_landingpage", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function workForm(Request $request): Response
    {
         define('SITE_KEY', '6Ldt8LIUAAAAAE72Wh-7Ny1NxKMc9qpqRbHuZCO4');
         define('SECRET_KEY', '6Ldt8LIUAAAAAHAzyAqDItXSZbExN41aOnmgMOas');
        return $this->render('pages/landing_pages/work_landing.html.twig', ['site_key' => SITE_KEY, 'secret_key' => SECRET_KEY]);
    }

    /**
     * @Route("/allast-keresek-kuldes", name="send_work_landingpage")
     * @Method("POST")
     *
     * @param Request             $request
     * @param \Swift_Mailer       $mailer
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */

    public function sendWorkForm(Request $request): Response
    {   
        
        define('SECRET_KEY', '6Ldt8LIUAAAAAHAzyAqDItXSZbExN41aOnmgMOas');
        $name = $request->request->get('name');
        $telephone = $request->request->get('telephone');
        $captcha = $request->request->get('g-recaptcha-response');
        $telepules = $request->request->get('telepules');
        $position = $request->request->get('position');
        
        $response = [];
        $errors = [];
        
        if(empty($name) || strlen($name) < 3 || strlen($name) > 200) {
            array_push($errors, 'A név megadása kötelező!');
        }

        if(empty($telepules) || strlen($telepules) < 3 || strlen($telepules) > 200) {
            array_push($errors, 'A település megadása kötelező!');
        }
        if(empty($position) || strlen($position) < 3 || strlen($position) > 200) {
            array_push($errors, 'A betöltendő pozíció megadása kötelező!');
        }
        $repository = $this->getDoctrine()
        ->getRepository(LandingWork::class);

        $query = $repository->createQueryBuilder('p')
            ->where('p.telephone = :telephone')
            ->setParameter('telephone', $telephone)
            ->getQuery();

        $find_telephone = $query->getResult();
    
        
        if(empty($telephone)) {
            array_push($errors, 'A telefonszám megadása kötelező!');
        } else {
            if(!empty($find_telephone)) {
               array_push($errors, 'Ezzel a telefonszámmal jelentkeztek már.');
            }

            if($this->validate_phone_number($telephone) == false) {
               array_push($errors, 'A telefonszámmal formátuma nem megfelelő.');
            }

        }

        if(empty($captcha) || $this->getCapcha(SECRET_KEY, $captcha)['success'] == false ) {
            array_push($errors, 'Hiba a jelentkezés elküldése során. Kérjük próbálja meg később.');
        }
        
        if(count($errors) > 0){
            return $this->json(['errors' => $errors]);
        }

        $em = $this->getDoctrine()->getManager();
        $contact = new LandingWork();
        $contact->setName($name);
        $contact->setTelephone($telephone);
        $contact->setTelepules($telepules);
        $contact->setPosition($position);

        $em->persist($contact);
        $em->flush();
        $response['success'] = true;
        return $this->json($response);
    }

        /**
     * @Route("/allasra-jelentkezem", name="working_landingpage", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function workingForm(Request $request): Response
    {
         define('SITE_KEY', '6Ldt8LIUAAAAAE72Wh-7Ny1NxKMc9qpqRbHuZCO4');
         define('SECRET_KEY', '6Ldt8LIUAAAAAHAzyAqDItXSZbExN41aOnmgMOas');
        return $this->render('pages/landing_pages/working_landing.html.twig', ['site_key' => SITE_KEY, 'secret_key' => SECRET_KEY]);
    }

    /**
     * @Route("/allasra-jelentkezem-kuldes", name="send_working_landingpage")
     * @Method("POST")
     *
     * @param Request             $request
     * @param \Swift_Mailer       $mailer
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */

    public function sendWorkingForm(Request $request): Response
    {   
        
        define('SECRET_KEY', '6Ldt8LIUAAAAAHAzyAqDItXSZbExN41aOnmgMOas');
        $name = $request->request->get('name');
        $telephone = $request->request->get('telephone');
        $captcha = $request->request->get('g-recaptcha-response');
        $telepules = $request->request->get('telepules');
        $position = $request->request->get('position');
        
        $response = [];
        $errors = [];
        
        if(empty($name) || strlen($name) < 3 || strlen($name) > 200) {
            array_push($errors, 'A név megadása kötelező!');
        }

        if(empty($telepules) || strlen($telepules) < 3 || strlen($telepules) > 200) {
            array_push($errors, 'A település megadása kötelező!');
        }
        if(empty($position) || strlen($position) < 3 || strlen($position) > 200) {
            array_push($errors, 'A betöltendő pozíció megadása kötelező!');
        }
        $repository = $this->getDoctrine()
        ->getRepository(LandingWorking::class);

        $query = $repository->createQueryBuilder('p')
            ->where('p.telephone = :telephone')
            ->setParameter('telephone', $telephone)
            ->getQuery();

        $find_telephone = $query->getResult();
    
        
        if(empty($telephone)) {
            array_push($errors, 'A telefonszám megadása kötelező!');
        } else {
            if(!empty($find_telephone)) {
               array_push($errors, 'Ezzel a telefonszámmal jelentkeztek már.');
            }

            if($this->validate_phone_number($telephone) == false) {
               array_push($errors, 'A telefonszámmal formátuma nem megfelelő.');
            }

        }

        if(empty($captcha) || $this->getCapcha(SECRET_KEY, $captcha)['success'] == false ) {
            array_push($errors, 'Hiba a jelentkezés elküldése során. Kérjük próbálja meg később.');
        }
        
        if(count($errors) > 0){
            return $this->json(['errors' => $errors]);
        }

        $em = $this->getDoctrine()->getManager();
        $contact = new LandingWorking();
        $contact->setName($name);
        $contact->setTelephone($telephone);
        $contact->setTelepules($telepules);
        $contact->setPosition($position);

        $em->persist($contact);
        $em->flush();
        $response['success'] = true;
        return $this->json($response);
    }

    /**
     * @Route("/temajavaslatok", name="redirect_google", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function redirectGoogle(Request $request): Response
    {
       return $this->redirect("https://forms.gle/RKB9FbtYBuipj57e7");
    }

    public function getCapcha($secret_key, $captcha)
    {
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$captcha);
        $return = json_decode($response, true);
        return $return;
    }

    public function validate_phone_number($phone)
    {
         // Allow +, - and . in phone number
         $filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
         // Remove "-" from number
         $phone_to_check = str_replace("-", "", $filtered_phone_number);
         // Check the lenght of number
         // This can be customized if you want phone number from a specific country
         if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 14) {
            return false;
         } else {
           return true;
         }
    }

    /**
     * @Route("/brand-referenseket-keresunk", name="brand_reference_landingpage", options={"sitemap" = {"priority" = 0.7 }})
     * @Method("GET")
     */
    public function brandReference (Request $request): Response
    {
        define('SITE_KEY', '6Ldt8LIUAAAAAE72Wh-7Ny1NxKMc9qpqRbHuZCO4');
         define('SECRET_KEY', '6Ldt8LIUAAAAAHAzyAqDItXSZbExN41aOnmgMOas');
         
        return $this->render('pages/landing_pages/brand_reference_landing.html.twig', ['site_key' => SITE_KEY, 'secret_key' => SECRET_KEY]);
    }


    
    
}
