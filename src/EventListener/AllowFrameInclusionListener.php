<?php

namespace Schubwerk\ContaoSchubwerkTrackingBundle\EventListener;

use Schubwerk\ContaoSchubwerkTrackingBundle\Services\Config;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @final
 */
class AllowFrameInclusionListener implements EventSubscriberInterface
{
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::RESPONSE => 'onKernelResponse');
    }

    /**
     * @param FilterResponseEvent|ResponseEvent $e
     */
    public function onKernelResponse($e)
    {
        if (!$e instanceof FilterResponseEvent && !$e instanceof ResponseEvent) {
            throw new \InvalidArgumentException(\sprintf('Expected instance of type %s, %s given', \class_exists(ResponseEvent::class) ? ResponseEvent::class : FilterResponseEvent::class, \is_object($e) ? \get_class($e) : \gettype($e)));
        }

        if (HttpKernelInterface::MASTER_REQUEST !== $e->getRequestType()) {
            return;
        }

        $response = $e->getResponse();

        if ($response->isRedirection()) {
            return;
        }

        if (empty($this->config->getTrackerBaseUrl())) {
            return;
        }

        if(!str_starts_with(
            $e->getRequest()->headers->get('referer'),
            $this->config->getTrackerBaseUrl()
        )) {
            return;
        }

        // allow inclusion from tracker for event configuration
        $response->headers->remove('X-Frame-Options');
    }
}
