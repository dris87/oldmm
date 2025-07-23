<?php
namespace All4One\AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Psr\Log\LoggerInterface;

class BotBlockerEventListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $userAgent = $request->headers->get('User-Agent');
        $clientIp = $request->getClientIp();

        // Bing bot IP tartományok
        $blockedIps = [
            '40.77.167.',
            '157.55.39.',
            '207.46.13.',
            '52.167.144.'
        ];

        // User-Agent vagy IP alapján ellenőrzés
        if (stripos($userAgent, 'bingbot') !== false || 
            $this->isIpInBlockedRanges($clientIp, $blockedIps)) {
            
            // Logoljuk a blokkolt kérést
            $this->logger->info('Blocked bot request', [
                'ip' => $clientIp,
                'user_agent' => $userAgent,
                'uri' => $request->getUri()
            ]);

            $response = new Response('Access Denied', Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
        }
    }

    private function isIpInBlockedRanges($ip, array $blockedRanges)
    {
        foreach ($blockedRanges as $range) {
            if (strpos($ip, $range) === 0) {
                return true;
            }
        }
        return false;
    }
}