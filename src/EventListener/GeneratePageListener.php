<?php

namespace Schubwerk\ContaoSchubwerkTrackingBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageRegular;
use Contao\LayoutModel;
use Contao\PageModel;
use Schubwerk\ContaoSchubwerkTrackingBundle\Services\Config;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Hook("generatePage")
 */
class GeneratePageListener
{
    const API_PATH = '/api/tracker';
    const API_VERSION = 'v1';

    const SCRIPT_PATH = '/shwk-assets/sclient.js';
    const SERVER_FIELDS = [
        'APP_NAME',
        'HTTPS',
        'HTTP_ACCEPT_LANGUAGE',
        'HTTP_HOST',
        'HTTP_REFERER',
        'HTTP_USER_AGENT',
        'HTTP_USER_AGENT',
        'REMOTE_ADDR',
        'REMOTE_ADDR',
        'REQUEST_SCHEME',
        'REQUEST_TIME_FLOAT',
        'REQUEST_URI',
    ];

    private RouterInterface $router;
    private Config $config;
    private RequestStack $requestStack;

    public function __construct(RouterInterface $router, Config $config, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->config = $config;
        $this->requestStack = $requestStack;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if ($this->requestStack->getCurrentRequest()->attributes->get('_preview', false)) {
            return;
        }

        if (empty($GLOBALS['TL_CONFIG']['schubwerk_tracking_project_id'])) {
            return;
        }

        if ($script = $this->buildHeadScript()) {
            $GLOBALS['TL_HEAD'][] = $script;
        }
    }

    private function buildHeadScript()
    {
        if (empty($GLOBALS['TL_CONFIG']['schubwerk_tracking_project_id'])) {
            return false;
        }

        $this->config->ensureSclientDownloaded();

        $placeholders = [
            '{{TRACKER_URL}}' => self::SCRIPT_PATH,
            '{{PROJECT_KEY}}' => 'local',
            '{{WRITE_KEY}}' => 'local',
            '{{API_END_POINT}}' => str_replace(['https://', 'http://'], '', $this->getApiUrl()),
            '{{PROTOCOL}}' => parse_url($this->getApiUrl(), PHP_URL_SCHEME),
            '{{VERSION}}' => self::API_VERSION,
            '{{ORIGIN}}' => ltrim($this->config->getTrackerBaseUrl(), '/') . '/'
        ];
        $script = file_get_contents(__DIR__ . '/../Resources/scaffolding/tracker.js.template');
        $script = str_replace(array_keys($placeholders), array_values($placeholders), $script);
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom_tracker = $dom->createElement('script');
        $dom_tracker->setAttribute('id', 'schubwerk_tracking');
        $dom_tracker->textContent = $script;
        $dom->appendChild($dom_tracker);

        return $dom->saveHTML();
    }

    private function getApiUrl(): string
    {
        $routeUrl = $this->router->generate('shwkcore', ['event' => 'fake'], UrlGeneratorInterface::ABSOLUTE_URL);
        $baseUrl = substr($routeUrl, 0, strpos($routeUrl, '/v1'));
        return $baseUrl;
    }
}

