<?php

namespace Schubwerk\ContaoSchubwerkTrackingBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageRegular;
use Contao\LayoutModel;
use Contao\PageModel;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Hook("generatePage")
 */
class GeneratePageListener
{
    const API_URL = 'http://tracker.schubwerk.de/api/tracker';
    const API_VERSION = 'v1';

    const SCRIPT_URL = 'https://tracker.schubwerk.de/js/tracking.js';

    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if (empty($GLOBALS['TL_CONFIG']['schubwerk_tracking_project_id'])) {
            return;
        }

        $url = $this->buildEventUrl('pageviews', $GLOBALS['TL_CONFIG']['schubwerk_tracking_project_id']);
        $this->sendRequestAndForget('POST', $url, $_SERVER);

        if ($script = $this->buildHeadScript()) {
            $GLOBALS['TL_HEAD'][] = $script;
        }
    }

    private function buildEventUrl(string $event_name, string $api_key): string
    {
        return sprintf('%s/%s/projects/%s/events/server/%s',
            self::API_URL,
            self::API_VERSION,
            $api_key,
            $event_name
        );
    }

    /**
     * Send a HTTP request, but do not wait for the response
     *
     * @source https://stackoverflow.com/a/58504757/288568
     * @param string $method The HTTP method
     * @param string $url The url (including query string)
     * @param array $params Added to the URL or request body depending on method
     */
    private function sendRequestAndForget(string $method, string $url, array $params = []): void
    {
        $parts = parse_url($url);
        if ($parts === false)
            throw new Exception('Unable to parse URL');
        $host = $parts['host'] ?? null;
        $port = $parts['port'] ?? 80;
        $path = $parts['path'] ?? '/';
        $query = $parts['query'] ?? '';
        parse_str($query, $queryParts);

        if ($host === null)
            throw new Exception('Unknown host');
        $connection = fsockopen($host, $port, $errno, $errstr, 30);
        if ($connection === false)
            throw new Exception('Unable to connect to ' . $host);
        $method = strtoupper($method);

        if (!in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $queryParts = $params + $queryParts;
            $params = [];
        }

        // Build request
        $request  = $method . ' ' . $path;
        if ($queryParts) {
            $request .= '?' . http_build_query($queryParts);
        }
        $request .= ' HTTP/1.1' . "\r\n";
        $request .= 'Host: ' . $host . "\r\n";

        $body = json_encode($params);
        if ($body) {
            $request .= 'Content-Type: application/json' . "\r\n";
            $request .= 'Content-Length: ' . strlen($body) . "\r\n";
        }
        $request .= 'Connection: Close' . "\r\n\r\n";
        $request .= $body;

        // Send request to server
        fwrite($connection, $request);
        fclose($connection);
    }

    private function buildHeadScript()
    {
        if (empty($GLOBALS['TL_CONFIG']['schubwerk_tracking_track_events']) || empty($GLOBALS['TL_CONFIG']['schubwerk_tracking_project_id'])) {
            return false;
        }

        $projectId = $GLOBALS['TL_CONFIG']['schubwerk_tracking_project_id'];

        $placeholders = [
            '{{TRACKER_URL}}' => self::SCRIPT_URL,
            '{{PROJECT_KEY}}' => $projectId,
            '{{WRITE_KEY}}' => $projectId,
            '{{API_END_POINT}}' => str_replace(['https://', 'http://'],'',self::API_URL),
            '{{PROTOCOL}}' => parse_url(self::API_URL, PHP_URL_SCHEME),
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
}
