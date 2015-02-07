<?php

namespace Bramus\MCS;

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
     * Logger
     * @var Object
     */
    private $logger;

    /**
     * The root URL to start scanning at
     * @var String
     */
    private $rootUrl;

    /**
     * The Base path of the root URL
     * @var String
     */
    private $rootUrlBasePath;

    /**
     * The URL parts of the root URL, as parsed by parse_url()
     * @var Array
     */
    private $rootUrlParts;

    /**
     * Array of all pages scanned / about to be scanned
     * @var Array
     */
    private $pages = [];

    /**
     * Array of patterns in URLs to ignore to fetch content from
     * @var  Array
     */
    private $ignorePatterns = [];

    /**
     * Create a new Scanner instance.
     * @param String $rootUrl The (root)URL to start scanning
     */
    public function __construct($rootUrl, $logger, $ignorePatterns)
    {
        // Store the rootUrl
        $this->setRootUrl($rootUrl);

        // Store logger
        $this->logger = $logger;

        // store the ignorePatterns
        $this->setIgnorePatterns($ignorePatterns, '{$rootUrl}');
    }

    /**
     * Sets the root URL of the website to scan
     * @param String
     * @param boolean
     */
    private function setRootUrl($rootUrl, $limitToPath = true)
    {

        // If the rootUrl is *, it means that we'll pass in some URLs manually
        if ($rootUrl == '*') {
            $this->rootUrl = $rootUrl;
            return;
        }

        // Make sure the rootUrl is parse-able
        $urlParts = parse_url($rootUrl);
        if (!$urlParts) {
            $this->logger->addEmergency('Invalid rootUrl!');
            exit();
        }

        // Force trailing / on rootUrl, it's easier for us to work with it
        if (substr($rootUrl, -1) != '/') {
            $rootUrl .= '/';
        }

        // store rootUrl
        $this->rootUrl = strstr($rootUrl, '?') ? substr($rootUrl, 0, strpos($rootUrl, '?')) : $rootUrl;

        // store rootUrl without queryString
        // If we need to limit to the path of the URL (viz. at first run): take that one into account
        // Otherwise keep the already set path
        $this->rootUrlBasePath = $urlParts['scheme'].'://'.$urlParts['host'].($limitToPath ? $urlParts['path'] : $this->rootUrlParts['path']);

        if (!$limitToPath) {
            $this->logger->addNotice('Updated rootUrl to '.$this->rootUrl);
            $this->logger->addNotice('Updated rootUrlBasePath to '.$this->rootUrlBasePath);
        }

        // store urlParts
        $this->rootUrlParts = $urlParts;
    }

    public function setIgnorePatterns($ignorePatterns, $toReplace = '{$rootUrl}')
    {
        // Force trailing / on $toReplace
        if (substr($toReplace, -1) != '/') {
            $toReplace .= '/';
        }

        // Store ignorepatterns
        $this->logger->addDebug('Store ignore patterns '.$p);
        $this->ignorePatterns = (array) $ignorePatterns;

        // Replace {$rootUrl} in the ignorepatterns
        foreach ($this->ignorePatterns as &$p) {
            $p = str_replace($toReplace, $this->rootUrl, $p);
            $this->logger->addDebug('Add ignore pattern '.$p);
        }
    }

    /**
     * Scan entire website
     * @return void
     */
    public function scan()
    {
        // Add the root URL to the list of pages
        if ($this->rootUrl != '*') {
            $this->pages[] = $this->rootUrl;
        }

        // Give feedback on the CLI
        $this->logger->addNotice('Scanning '.$this->rootUrl);

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
                $this->logger->addError(sprintf('%05d', $curPageIndex).' - '.$curPageUrl);

                foreach ($mixedContent as $url) {
                    $this->logger->addWarning($url);
                }
            }

            // No mixed content
            else {
                $this->logger->addInfo(sprintf('%05d', $curPageIndex).' - '.$curPageUrl);
            }

            // Done scanning all pages? Then quit! Otherwise: scan the next page
            if ($curPageIndex+1 == sizeof($this->pages)) {
                break;
            } else {
                $curPageIndex++;
            }
        }

        // Give feedback on the CLI
        $this->logger->addNotice('Scanned '.sizeof($this->pages).' pages for Mixed Content');
    }

    /**
     * Scan a single URL
     * @param  String $pageUrl URL of the page to scan
     * @return array
     */
    private function scanPage($pageUrl)
    {
        // Get the HTML of the page
        $html = $this->getContents($pageUrl);

        // Create new DOMDocument using the fetched HTML
        // (explicitly adding \Bramus\MCS\ to indicate the difference with \DOMDocuemnt)
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
     * @param  array
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
     * @param  String
     * @return bool
     */
    public function queueUrl($url)
    {
        // Remove fragment from URL (if any)
        if (strpos($url, '#')) {
            $url = substr($url, 0, strpos($url, '#'));
        }

        // If the URL should not be ignored (pattern matching) and isn't added to the list yet, add it to the list of pages to scan.
        if ((preg_match('#^'.$this->rootUrlBasePath.'#i', $url) === 1) && !in_array($url, $this->pages)) {
            $ignorePatternMatched = false;
            foreach ($this->ignorePatterns as $p) {
                if ($p && preg_match('#'.$p.'#i', $url)) {
                    $ignorePatternMatched = true;

                    return false;
                }
            }

            if (!$ignorePatternMatched) {
                $this->pages[] = $url;
                $this->logger->addDebug('Queued '.$url);

                return true;
            }
        }

        // Not queued
        return false;
    }

    /**
     * Make a given URL absolute
     * @param  String $linkedUrl      The URL linked to
     * @param  String $currentPageUrl The URL of the page holding the URL linked to
     * @return String
     */
    private function absolutizeUrl($linkedUrl, $currentPageUrl)
    {
        // Absolute URLs
        // --> Don't change
        if (substr($linkedUrl, 0, 8) == "https://" || substr($linkedUrl, 0, 7) == "http://") {
            return $this->canonicalize($linkedUrl);
        }

        // Protocol relative URLs
        // --> Prepend scheme
        if (substr($linkedUrl, 0, 2) == "//") {
            return $this->canonicalize($this->rootUrlParts['scheme'].':'.$linkedUrl);
        }

        // Root-relative URLs
        // --> Prepend scheme and host
        if (substr($linkedUrl, 0, 1) == "/") {
            return $this->canonicalize($this->rootUrlParts['scheme'].'://'.$this->rootUrlParts['host'].'/'.substr($linkedUrl, 1));
        }

        // Document fragment
        // --> Don't scan it
        if (substr($linkedUrl, 0, 1) == "#") {
            return '';
        }

        // Links that are not http or https (e.g. mailto:, tel:)
        // --> Don't scan it
        $linkedUrlParts = parse_url($linkedUrl);
        if (isset($linkedUrlParts['scheme']) && !in_array($linkedUrlParts['scheme'], array('http', 'https', ''))) {
            return '';
        }

        // Document-relative URLs
        // --> Append $linkedUrl to $currentPageUrl's PATH
        return $this->canonicalize(substr($currentPageUrl, 0, strrpos($currentPageUrl, '/')).'/'.$linkedUrl);
    }

    /**
     * Remove ../ and ./ from a given URL
     * @see  http://php.net/manual/en/function.realpath.php#71334
     * @param  String
     * @return String
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
     * @param  String $pageUrl The URL of the page to get the contents of
     * @return String
     */
    private function getContents(&$pageUrl)
    {
        // Init CURL
        $curl = curl_init();

        @curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_URL => $pageUrl,
            CURLOPT_TIMEOUT_MS => 10000,
        ));

        // Fetch the page contents
        $resp = curl_exec($curl);

        // Fetch the URL of the page we actually fetched
        $newUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);

        if ($newUrl != $pageUrl) {
            // If we started at the rootURL, and it got redirected:
            // --> overwrite the rootUrl so that we use the new one from now on
            if ($pageUrl == $this->rootUrl) {
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
            $this->logger->addCritical('cURL Error ('.$curl_errno.'): '.$curl_error);
        }

        // Close it
        @curl_close($curl);

        // Return the fetched contents
        return $resp;
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
}
