<?php

namespace Schubwerk\ContaoSchubwerkTrackingBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageRegular;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\System;
use Schubwerk\ContaoSchubwerkTrackingBundle\Services\Config;
use Schubwerk\Core\Downloader;
use Schubwerk\Core\DownloadException;
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

    public function __construct(RouterInterface $router, Config $config)
    {
        $this->router = $router;
        $this->config = $config;
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
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

        $this->ensureSclientDownloaded();

        $placeholders = [
            '{{TRACKER_URL}}' => self::SCRIPT_PATH,
            '{{PROJECT_KEY}}' => 'local',
            '{{WRITE_KEY}}' => 'local',
            '{{API_END_POINT}}' => str_replace(['https://', 'http://'],'',$this->getApiUrl()),
            '{{PROTOCOL}}' => parse_url($this->getApiUrl(), PHP_URL_SCHEME),
            '{{VERSION}}' => self::API_VERSION,
        ];
        $script = file_get_contents( __DIR__  . '/../Resources/scaffolding/tracker.js.template');
        $script = str_replace(array_keys($placeholders), array_values($placeholders), $script);
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom_tracker = $dom->createElement('script');
        $dom_tracker->setAttribute('id', 'schubwerk_tracking');
        $dom_tracker->textContent = $script;
        $dom->appendChild( $dom_tracker );

        return $dom->saveHTML();
    }

    private function getApiUrl(): string
    {
        $routeUrl = $this->router->generate('shwkcore', ['event' => 'fake'], UrlGeneratorInterface::ABSOLUTE_URL);
        $baseUrl = substr($routeUrl, 0, strpos($routeUrl, '/v1'));
        return $baseUrl;
    }

    private function getServerArray()
    {
        return array_filter(
            $_SERVER,
            function ($key) {
                return in_array($key, self::SERVER_FIELDS);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    private function ensureSclientDownloaded()
    {
        try {
            (new Downloader(
                $this->config->getTrackerBaseUrl(),
                $this->config->getApiKey(),
                $this->config->getCacheDir(),
                $this->config->getWebDir(),
            ))->download();
        } catch (DownloadException $e) {
//            add_settings_error( 'general', 'settings_updated', $e->getMessage(), 'error' );
        }
    }
}
