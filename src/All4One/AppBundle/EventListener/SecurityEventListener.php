<?php
namespace All4One\AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

class SecurityEventListener
{
    private $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        
        if ($exception instanceof MethodNotAllowedHttpException) {
            $request = $event->getRequest();
            $allowedMethods = $exception->getHeaders()['Allow'] ?? '';
            
            // Bővített logolás
            /*
            $this->logger->error(
                'Method Not Allowed detected', 
                [
                    'ip' => $request->getClientIp(),
                    'uri' => $request->getUri(),
                    'method' => $request->getMethod(),
                    'user_agent' => $request->headers->get('User-Agent'),
                    'allowed_methods' => $allowedMethods,
                    'request_headers' => $request->headers->all(),
                    'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'referrer' => $request->headers->get('Referer')
                ]
            );
            */
            // Helyes 405-ös válasz visszaadása az Allow header-rel
            $response = new Response('Method Not Allowed', 405);
            $response->headers->set('Allow', $allowedMethods);
            $event->setResponse($response);
        }
    }
}