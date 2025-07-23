<?php

namespace BackOffice\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DictionaryController extends Controller
{
    /**
     * @Route("/admin/validate-locations", name="admin_validate_locations", options={"expose"=true})
     */
    public function validateLocationsAction(Request $request)
    {
        $locations = json_decode($request->getContent(), true)['locations'] ?? [];
        
        $validLocations = $this->getDoctrine()
            ->getRepository('CommonCoreBundle:Dictionary\Dictionary')
            ->createQueryBuilder('d')
            ->where('d.value IN (:locations)')
            ->setParameter('locations', $locations)
            ->getQuery()
            ->getResult();
            
        $result = [];
        foreach ($validLocations as $location) {
            $result[] = [
                'id' => $location->getId(),
                'text' => $location->getValue()
            ];
        }
        
        return new JsonResponse($result);
    }
}