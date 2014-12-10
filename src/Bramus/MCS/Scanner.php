<?php

namespace Bramus\MCS;

/**
 * A (quick and dirty) scanner to scanning all (linked) pages of an https-enabled website for Mixed Content
 * @author Bramus! <bramus@bram.us>
 * @version 1.0
 */
class Scanner {


	/**
	 * The root URL to start scanning at
	 * @var String
	 */
	private $rootUrl;


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
	public function __construct($rootUrl, $ignorePatterns) {

		// Store the rootUrl
		$this->setRootUrl($rootUrl);

		// store the ignorePatterns
		$this->setIgnorePatterns($ignorePatterns, '{$rootUrl}');

	}

	private function setRootUrl($rootUrl) {

		// Make sure the rootUrl is parse-able
		$urlParts = parse_url($rootUrl);
		if (!$urlParts) exit('Invalid rootUrl!');

		// Force trailing / on rootUrl, it's easier for us to work with it
		if (substr($rootUrl, -1) != '/') $rootUrl .= '/';

		// store rootUrl
		$this->rootUrl = strstr($rootUrl, '?') ? substr($rootUrl, 0, strpos($rootUrl, '?')) : $rootUrl;

		// store rootUrl without queryString
		$this->rootUrlBasePath = substr($this->rootUrl, 0, strrpos($this->rootUrl, '/') + 1);

		// store urlParts
		$this->rootUrlParts = $urlParts;

	}

	private function setIgnorePatterns($ignorePatterns, $toReplace = '{$rootUrl}') {

		// Force trailing / on $toReplace
		if (substr($toReplace, -1) != '/') $toReplace .= '/';

		// Store ignorepatterns
		$this->ignorePatterns = (array) $ignorePatterns;

		// Replace {$rootUrl} in the ignorepatterns
		foreach ($this->ignorePatterns as &$p) {
			$p = str_replace($toReplace, $this->rootUrl, $p);
		}

	}


	/**
	 * Scan entire website
	 * @return void
	 */
	public function scan() {

		// Add the root URL to the list of pages
		$this->pages[] = $this->rootUrl;

		// Give feedback on the CLI
		echo 'Scanning ' . $this->rootUrl . PHP_EOL;

		// Current index at $this->pages
		$curPageIndex = 0;

		// Start looping
		while(true) {

			// Get the current pageUrl
			$curPageUrl = $this->pages[$curPageIndex];

			// Give feedback on the CLI
			echo '[' . date('Y-m-d H:i:s') . '] ' . sprintf('%05d', $curPageIndex) . ' - ' . $curPageUrl . PHP_EOL;

			// Scan a single page. Returns the mixed content (if any)
			$mixedContent = $this->scanPage($curPageUrl);

			// Got mixed content? Give feedback on the CLI
			if ($mixedContent) {
				foreach ($mixedContent as $url) {
					echo '  - ' . $url . PHP_EOL;
				}
			}

			// Done scanning all pages? Then quit! Otherwise: scan the next page
			if ($curPageIndex+1 == sizeof($this->pages)) break;
			else $curPageIndex++;

		}

		// Give feedback on the CLI
		echo 'Scanned ' . sizeof($this->pages) . ' pages for Mixed Content' . PHP_EOL;

	}


	/**
	 * Scan a single URL
	 * @param  String $pageUrl 	URL of the page to scan
	 * @return array
	 */
	private function scanPage($pageUrl) {

		// Array holding all URLs which are found to be Mixed Content
		// We'll return this one at the very end
		$mixedContentUrls = [];

		// Get the HTML of the page
		$html = $this->getContents($pageUrl);

		// Create new DOMDocument using the fetched HTML
		$doc = new \DOMDocument();
		if ($doc->loadHTML($html)) {

			// Loop all links found
			foreach ($doc->getElementsByTagName('a') as $el) {
				if ($el->hasAttribute('href')) {

					// Normalize the URL first so that it's an absolute URL.
					$url = $this->normalizeUrl($el->getAttribute('href'), $pageUrl);

					// Remove fragment from URL (if any)
					if (strpos($url, '#')) $url = substr($url, 0, strpos($url, '#'));

					// If the URL should not be ignored (pattern matching) and isn't added to the list yet, add it to the list of pages to scan.
					if ((preg_match('#^' . $this->rootUrlBasePath . '#i', $url) === 1) && !in_array($url, $this->pages)) {
						
						$ignorePatternMatched = false;
						foreach ($this->ignorePatterns as $p) {
							if ($p && preg_match('#' . $p . '#i', $url)) {
								$ignorePatternMatched = true;
								// echo ' - ignoring ' . $url . PHP_EOL;
								break;
							}
						}
						if (!$ignorePatternMatched) {
							$this->pages[] = $url;
						}
					}

				}
			}

			// Check all iframes contained in the HTML
			foreach ($doc->getElementsByTagName('iframe') as $el) {
				if ($el->hasAttribute('src')) {
					$url = $el->getAttribute('src');
					if (substr($url, 0, 7) == "http://") {
						$mixedContentUrls[] = $url;
					}
				}
			}

			// Check all images contained in the HTML
			foreach ($doc->getElementsByTagName('img') as $el) {
				if ($el->hasAttribute('src')) {
					$url = $el->getAttribute('src');
					if (substr($url, 0, 7) == "http://") {
						$mixedContentUrls[] = $url;
					}
				}
			}

			// Check all script elements contained in the HTML
			foreach ($doc->getElementsByTagName('script') as $el) {
				if ($el->hasAttribute('src')) {
					$url = $el->getAttribute('src');
					if (substr($url, 0, 7) == "http://") {
						$mixedContentUrls[] = $url;
					}
				}
			}

			// Check all stylesheet links contained in the HTML
			foreach ($doc->getElementsByTagName('link') as $el) {
				if ($el->hasAttribute('href') && $el->hasAttribute('rel') && ($el->getAttribute('rel') == 'stylesheet')) {
					$url = $el->getAttribute('href');
					if (substr($url, 0, 7) == "http://") {
						$mixedContentUrls[] = $url;
					}
				}
			}

			// Check all `object` elements contained in the HTML
			foreach ($doc->getElementsByTagName('object') as $el) {
				if ($el->hasAttribute('data')) {
					$url = $el->getAttribute('data');
					if (substr($url, 0, 7) == "http://") {
						$mixedContentUrls[] = $url;
					}
				}
			}

		}

		// Return the array of Mixed Content
		return $mixedContentUrls;

	}


	/**
	 * Normalizes a URL to become an absolute URL
	 * @param  String $linkedUrl	The URL linked to
	 * @param  String $pageUrlContainingTheLinkedUrl	The URL of the page holding the URL linked to
	 * @return String
	 */
	private function normalizeUrl($linkedUrl, $pageUrlContainingTheLinkedUrl) {

		// Absolute URLs
		// --> Don't change
		if (substr($linkedUrl, 0, 8) == "https://" || substr($linkedUrl, 0, 7) == "http://") {
			return $this->canonicalize($linkedUrl);
		}

		// Protocol relative URLs
		// --> Prepend scheme
		if (substr($linkedUrl, 0, 2) == "//") {
			return $this->canonicalize($this->rootUrlParts['scheme'] . ':' . $linkedUrl);
		}

		// Root-relative URLs
		// --> Prepend scheme and host
		if (substr($linkedUrl, 0, 1) == "/") {
			return $this->canonicalize($this->rootUrlParts['scheme'] . '://' . $this->rootUrlParts['host'] . '/' . substr($linkedUrl, 1));
		}

		// Document fragment
		// --> Don't scan it
		if (substr($linkedUrl, 0, 1) == "#") {
			return '';
		}

		// Links that are not http or https (e.g. mailto:, tel:)
		// --> Don't scan it
		$linkedUrlParts = parse_url($linkedUrl);
		if (isset($linkedUrlParts['scheme']) && !in_array($linkedUrlParts['scheme'], array('http','https',''))) {
			return '';
		}

		// Document-relative URLs
		// --> Append $linkedUrl to $pageUrlContainingTheLinkedUrl's PATH
		return $this->canonicalize(substr($pageUrlContainingTheLinkedUrl, 0, strrpos($pageUrlContainingTheLinkedUrl, '/')) . '/' . $linkedUrl);

	}


	/**
	 * Remove ../ and ./ from a given URL
	 * @see  http://php.net/manual/en/function.realpath.php#71334
	 * @param  String
	 * @return String
	 */
	private function canonicalize($url) {

		$url = explode('/', $url);
		$keys = array_keys($url, '..');

		foreach($keys AS $keypos => $key) {
			array_splice($url, $key - ($keypos * 2 + 1), 2);
		}

		$url = implode('/', $url);
		$url = str_replace('./', '', $url);

		return $url;
	}


	/**
	 * Get the contents of a given URL (via GET)
	 * @param  String $pageUrl 	The URL of the page to get the contents of
	 * @return String
	 */
	private function getContents(&$pageUrl) {

		// Init CURL
		$curl = curl_init();

		@curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_URL => $pageUrl,
			CURLOPT_TIMEOUT_MS => 10000
		));

		// Fetch the page contents
		$resp = curl_exec($curl);

		// Fetch the URL of the page we actually fetched
		$newUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);

		if ($newUrl != $pageUrl) {
			
			// echo ' >> ' . $newUrl . PHP_EOL;

			// If we started at the rootURL, and it got redirected:
			// --> overwrite the rootUrl so that we use the new one from now on
			if ($pageUrl == $this->rootUrl) {

				// Store the new rootUrl
				$this->setRootUrl($newUrl);
				echo ' > Updated rootUrl to ' . $this->rootUrl . PHP_EOL;
				
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
			echo ' - cURL Error (' . $curl_errno . '): ' . $curl_error . PHP_EOL;
		}

		// Close it
		@curl_close($curl);

		// Return the fetched contents
		return $resp;

	}

}
