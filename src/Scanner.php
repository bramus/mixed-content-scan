<?php

namespace Bramus\MCS;

use Exception;
use Psr\Log\LoggerInterface;

/**
 * MCS\Scanner - A scanner class to crawl+scan HTTPS-enabled websites for Mixed Content.
 * @author Bramus! <bramus@bram.us>
 */
class Scanner
{
    /**
     * Do we need to crawl pages or not?
     * @var boolean
     */
    private $crawl = true;

    /**
     * Do we check the certificates for being valid or not (false = allow self signed, or missing certificates)
     * @var boolean
     */
    private $checkCertificate = true;

    /**
     * How long we will wait (in milliseconds) for each request to execute.
     * @var int
     */
    private $timeout = 10000;

    /**
     * Logger
     * @var Logger
     */
    private $logger;

    /**
     * The root URL to start scanning at
     * @var string
     */
    private $rootUrl;

    /**
     * The Base path of the root URL
     * @var string
     */
    private $rootUrlBasePath;

    /**
     * The URL parts of the root URL, as parsed by parse_url()
     * @var string[]
     */
    private $rootUrlParts;

    /**
     * Array of all pages scanned / about to be scanned
     * @var string[]
     */
    private $pages = [];

    /**
     * Array of patterns in URLs to ignore to fetch content from
     * @var string[]
     */
    private $ignorePatterns = [];

    /**
     * Create a new Scanner instance.
     *
     * @param  string          $rootUrl The (root)URL to start scanning
     * @param  LoggerInterface $logger
     * @param  string[]        $ignorePatterns
     * @throws Exception If the cURL extension is not installed or enabled
     */
    public function __construct($rootUrl, LoggerInterface $logger, $ignorePatterns)
    {
        // Store logger
        $this->logger = $logger;

        // Make sure Curl is installed and enabled
        if (!function_exists('curl_init')) {
            $this->logger->emergency('The required PHP cUrl extension is not installed or enabled');
            throw new Exception('The required PHP cUrl extension is not installed or enabled');
        }

        // Store the rootUrl
        $this->setRootUrl($rootUrl);

        // store the ignorePatterns
        $this->setIgnorePatterns($ignorePatterns, '{$rootUrl}');
    }

    /**
     * Sets the root URL of the website to scan.
     *
     * @param  string    $rootUrl
     * @param  bool      $limitToPath
     * @throws Exception If the root URL is invalid
     */
    private function setRootUrl($rootUrl, $limitToPath = true)
    {

        // If the rootUrl is *, it means that we'll pass in some URLs manually
        if ($rootUrl === '*') {
            $this->rootUrl = $rootUrl;
            return;
        }

        // Make sure the rootUrl is parse-able
        $urlParts = parse_url($rootUrl);

        if (!$urlParts || !isset($urlParts['scheme'], $urlParts['host'])) {
            $this->logger->emergency('Invalid rootUrl!');
            throw new Exception('Invalid rootUrl!');
        }

        // Force trailing / on rootUrl unless it has a file extension, it's easier for us to work with it
        $rootUrlPath = explode('/', parse_url($rootUrl)['path']);
        if (strpos(array_reverse($rootUrlPath)[0], '.') === false) {
            if (substr($rootUrl, -1) !== '/') {
                $rootUrl .= '/';
            }
        }

        // store rootUrl
        $this->rootUrl = strstr($rootUrl, '?') ? substr($rootUrl, 0, strpos($rootUrl, '?')) : $rootUrl;

        // store rootUrl without queryString
        // If we need to limit to the path of the URL (viz. at first run): take that one into account
        // Otherwise keep the already set path
        $this->rootUrlBasePath = $urlParts['scheme'].'://'.$urlParts['host'].($limitToPath ?
                $urlParts['path'] :
                $this->rootUrlParts['path']);

        if (!$limitToPath) {
            $this->logger->notice('Updated rootUrl to '.$this->rootUrl);
            $this->logger->notice('Updated rootUrlBasePath to '.$this->rootUrlBasePath);
        }

        // store urlParts
        $this->rootUrlParts = $urlParts;
    }

    /**
     * Sets the patterns to be ignored.
     *
     * @param array  $ignorePatterns
     * @param string $toReplace
     */
    public function setIgnorePatterns($ignorePatterns, $toReplace = '{$rootUrl}')
    {
        // Force trailing / on $toReplace
        if (substr($toReplace, -1) !== '/') {
            $toReplace .= '/';
        }

        // Store ignorepatterns
        $this->logger->debug('Store ignore patterns '.$p);
        $this->ignorePatterns = (array) $ignorePatterns;

        // Replace {$rootUrl} in the ignorepatterns
        foreach ($this->ignorePatterns as &$p) {
            $p = str_replace($toReplace, $this->rootUrl, $p);
            $this->logger->debug('Add ignore pattern '.$p);
        }
    }

    /**
     * Scan entire website
     * @return void
     */
    public function scan()
    {
        // Add the root URL to the list of pages
        if ($this->rootUrl !== '*') {
            $this->pages[] = $this->rootUrl;
        }

        // Give feedback on the CLI
        $this->logger->notice('Scanning '.$this->rootUrl);

        // Current index at $this->pages
        $curPageIndex = 0;

        // Start looping
        while (true) {
            // Get the current pageUrl
            $curPageUrl = $this->pages[$curPageIndex];

            // Scan a single page. Returns the mixed content (if any)
            $mixedContent = $this->scanPage($curPageUrl);

            // Got mixed content
            if ($mixedContent) {
                // Add an alert for the URL
                $this->logger->error(sprintf('%05d', $curPageIndex).' - '.$curPageUrl);

                foreach ($mixedContent as $url) {
                    $this->logger->warning($url);
                }
            } else { // No mixed content
                $this->logger->info(sprintf('%05d', $curPageIndex).' - '.$curPageUrl);
            }

            // Done scanning all pages? Then quit! Otherwise: scan the next page
            if ($curPageIndex+1 === count($this->pages)) {
                break;
            } else {
                $curPageIndex++;
            }
        }

        // Give feedback on the CLI
        $this->logger->notice('Scanned '.count($this->pages).' pages for Mixed Content');
    }

    /**
     * Scan a single URL
     * @param  string $pageUrl URL of the page to scan
     * @return string[]
     */
    private function scanPage($pageUrl)
    {
        // Get the HTML of the page
        $html = $this->getContents($pageUrl);

        // Create new DOMDocument using the fetched HTML
        // (explicitly adding \Bramus\MCS\ to indicate the difference with \DOMDocument)
        $doc = new \Bramus\MCS\DOMDocument();

        // Load up the HTML
        if ($doc->loadHTML($html)) {
            // Crawling enabled? Extract all links and queue all those found
            if ($this->crawl) {
                // Extract the links
                $links = (array) $doc->extractLinks();

                // Absolutize all links
                array_walk($links, function (&$url) use ($pageUrl) {
                    $url = $this->absolutizeUrl($url, $pageUrl);
                });

                // (Try to) Queue 'm
                $this->queueUrls($links);
            }

            // Extract mixedContent and return it
            return $doc->extractMixedContentUrls();
        }

        // No result
        return [];
    }

    /**
     * Queue an array of URLs
     * @param  string[] $urls
     * @return void
     */
    public function queueUrls(array $urls)
    {
        foreach ($urls as $url) {
            $this->queueUrl($url);
        }
    }

    /**
     * Queues an URL onto the queue if not queued yet
     * @param  string
     * @return bool
     */
    public function queueUrl($url)
    {
        // Remove fragment from URL (if any)
        if (strpos($url, '#')) {
            $url = substr($url, 0, strpos($url, '#'));
        }

        // If the URL should not be ignored (pattern matching) and isn't added
        // to the list yet, add it to the list of pages to scan.
        if ((preg_match('#^'.$this->rootUrlBasePath.'#i', $url) === 1) && !in_array($url, $this->pages, true)) {
            foreach ($this->ignorePatterns as $p) {
                if ($p && preg_match('#'.$p.'#i', $url)) {
                    return false;
                }
            }

            $this->pages[] = $url;
            $this->logger->debug('Queued '.$url);

            return true;
        }

        // Not queued
        return false;
    }

    /**
     * Make a given URL absolute
     * @param  string $linkedUrl      The URL linked to
     * @param  string $currentPageUrl The URL of the page holding the URL linked to
     * @return string
     */
    private function absolutizeUrl($linkedUrl, $currentPageUrl)
    {
        // Absolute URLs
        // --> Don't change
        if (0 === strpos($linkedUrl, 'https://') || 0 === strpos($linkedUrl, 'http://')) {
            return $this->canonicalize($linkedUrl);
        }

        // Protocol relative URLs
        // --> Prepend scheme
        if (0 === strpos($linkedUrl, '//')) {
            return $this->canonicalize($this->rootUrlParts['scheme'].':'.$linkedUrl);
        }

        // Root-relative URLs
        // --> Prepend scheme and host
        if (0 === strpos($linkedUrl, '/')) {
            return $this->canonicalize($this->rootUrlParts['scheme'].'://'.
                                       $this->rootUrlParts['host'].'/'.substr($linkedUrl, 1));
        }

        // Document fragment
        // --> Don't scan it
        if (0 === strpos($linkedUrl, '#')) {
            return '';
        }

        // Links that are not http or https (e.g. mailto:, tel:)
        // --> Don't scan it
        $linkedUrlParts = parse_url($linkedUrl);
        if (isset($linkedUrlParts['scheme']) &&
            !in_array($linkedUrlParts['scheme'], ['http', 'https', ''], true)
        ) {
            return '';
        }

        // Document-relative URLs
        // --> Append $linkedUrl to $currentPageUrl's PATH
        return $this->canonicalize(substr($currentPageUrl, 0, strrpos($currentPageUrl, '/')).'/'.$linkedUrl);
    }

    /**
     * Remove ../ and ./ from a given URL
     * @see    http://php.net/manual/en/function.realpath.php#71334
     * @param  string
     * @return string
     */
    private function canonicalize($url)
    {
        $url = explode('/', $url);
        $keys = array_keys($url, '..');

        foreach ($keys as $keypos => $key) {
            array_splice($url, $key - ($keypos * 2 + 1), 2);
        }

        $url = implode('/', $url);
        $url = str_replace('./', '', $url);

        return $url;
    }

    /**
     * Get the contents of a given URL (via GET)
     * @param  string $pageUrl The URL of the page to get the contents of
     * @return string
     */
    private function getContents(&$pageUrl)
    {
        // Init CURL
        $curl = curl_init();

        @curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_HEADER => 1, // Return both response head and response body, not only the response body
            CURLOPT_URL => $pageUrl,
            CURLOPT_TIMEOUT_MS => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => $this->checkCertificate,
            CURLOPT_SSL_VERIFYHOST => $this->getVerifyHost(),
            CURLOPT_USERAGENT => $this->getUserAgent(),
        ]);

        // Fetch the response (both head and body)
        $response = curl_exec($curl);

        // Fetch the URL of the page we actually fetched
        $newUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);

        if ($newUrl !== $pageUrl) {
            // If we started at the rootURL, and it got redirected:
            // --> overwrite the rootUrl so that we use the new one from now on
            if ($pageUrl === $this->rootUrl) {
                // Store the new rootUrl
                $this->setRootUrl($newUrl, false);

                // Update ignore patterns
                $this->setIgnorePatterns($this->ignorePatterns, $pageUrl);
            }

            // Update $pageUrl (pass by reference!)
            $pageUrl = $newUrl;
        }

        // Got an error?
        $curl_errno = curl_errno($curl);
        $curl_error = curl_error($curl);
        if ($curl_errno > 0) {
            $this->logger->critical('cURL Error ('.$curl_errno.'): '.$curl_error);
        }

        // Extract the response head and response body from the response
        $headers = substr($response, 0, curl_getinfo($curl, CURLINFO_HEADER_SIZE));
        $body = substr($response, -curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD));

        // Close it
        @curl_close($curl);

        // If the headers contain `Content-Security-Policy: upgrade-insecure-requests`
        // then the page should be skipped, as the browser will (should) then automatically
        // upgrade all requests.
        // @ref https://w3c.github.io/webappsec-upgrade-insecure-requests/


        // Return the fetched contents
        return $body;
    }

    /**
     * Get crawl value
     * @return boolean
     */
    public function getCrawl()
    {
        return $this->crawl;
    }

    /**
     * Set crawl value
     * @param boolean
     */
    public function setCrawl($crawl)
    {
        $this->crawl = (bool) $crawl;
    }

    /**
     * Get checkCertificate value
     * @return boolean
     */
    public function getCheckCertificate()
    {
        return $this->checkCertificate;
    }

    /**
     * Set checkCertificate value
     * @param boolean
     */
    public function setCheckCertificate($checkCertificate)
    {
        $this->checkCertificate = (bool) $checkCertificate;
    }

    /**
     * Get verifyHost value
     * @return int
     */
    public function getVerifyHost()
    {
        if ($this->checkCertificate) {
            return 2;
        } else {
            return 0;
        }
    }

    /**
     * Get timeout value
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set timeout value
     * @param int
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Get user agent value
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set user agent value
     * @param string
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }
}

