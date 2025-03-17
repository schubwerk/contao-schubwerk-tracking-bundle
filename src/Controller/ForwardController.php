<?php

namespace Schubwerk\ContaoSchubwerkTrackingBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Schubwerk\ContaoSchubwerkTrackingBundle\Services\Config;
use Schubwerk\Core\Forwarder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForwardController extends AbstractController
{
    const PLUGIN_VERSION = '2.0.0';

    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @Route("/shwkcore/v1/projects/local/events/{event}", name="shwkcore", requirements={"path"=".+"}, defaults={"path"=null})
     */
    public function forwardAction(Request $request, ?string $event): Response
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $response = (new Forwarder(
            $this->config->getTrackerBaseUrl(),
            $this->config->getApiKey(),
            $this->config->getCacheDir(),
            $this->config->getWebDir(),
            'Contao/' . $this->config->getContaoVersion() . '; SchubwerkTracking/' . self::PLUGIN_VERSION) // Server Agent
        )->forwardRequest($request->getClientIp(), $event, $data);

        return new Response($response, Response::HTTP_OK);
    }

    private function getUserAgent(): string
    {
        return 'Contao SchubwerkTracking/' . self::PLUGIN_VERSION;
    }
}
