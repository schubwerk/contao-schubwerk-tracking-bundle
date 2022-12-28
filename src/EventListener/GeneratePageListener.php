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

    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __invoke(PageModel $pageModel, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if (empty($GLOBALS['TL_CONFIG']['schubwerk_tracking_project_id'])) {
            return;
        }
        $url = $this->build_event_url('pageviews', $GLOBALS['TL_CONFIG']['schubwerk_tracking_project_id']);
        $this->sendRequestAndForget('POST', $url, $_SERVER);
    }

    private function build_event_url(string $event_name, string $api_key): string
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
}
