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

		// Force trailing / on rootUrl
		if (substr($rootUrl, -1) != '/') $rootUrl .= '/';

		// store rootUrl
		$this->rootUrl = $rootUrl;

		// Store ignorepatterns
		$this->ignorePatterns = (array) $ignorePatterns;

		// Replace {$rootUrl} in the ignorepatterns
		foreach ($this->ignorePatterns as &$p) {
			$p = str_replace('{$rootUrl}/', $this->rootUrl, $p);
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
					if ((preg_match('#^' . $this->rootUrl . '#i', $url) === 1) && !in_array($url, $this->pages)) {
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
					$url = $this->normalizeUrl($el->getAttribute('src'), $pageUrl);
					if (substr($url, 0, 7) == "http://") {
						$mixedContentUrls[] = $url;
					}
				}
			}

			// Check all images contained in the HTML
			foreach ($doc->getElementsByTagName('img') as $el) {
				if ($el->hasAttribute('src')) {
					$url = $this->normalizeUrl($el->getAttribute('src'), $pageUrl);
					if (substr($url, 0, 7) == "http://") {
						$mixedContentUrls[] = $url;
					}
				}
			}

			// Check all script elements contained in the HTML
			foreach ($doc->getElementsByTagName('script') as $el) {
				if ($el->hasAttribute('src')) {
					$url = $this->normalizeUrl($el->getAttribute('src'), $pageUrl);
					if (substr($url, 0, 7) == "http://") {
						$mixedContentUrls[] = $url;
					}
				}
			}

			// Check all css links contained in the HTML
			foreach ($doc->getElementsByTagName('link') as $el) {
				if ($el->hasAttribute('href') && $el->hasAttribute('rel') && ($el->getAttribute('rel') == 'stylesheet')) {
					$url = $this->normalizeUrl($el->getAttribute('href'), $pageUrl);
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
			return $linkedUrl;
		}

		// Protocol relative URLs
		// --> Prepend protocol
		if (substr($linkedUrl, 0, 2) == "//") {
			return 'https:' . $linkedUrl;
		}

		// Root-relative URLs
		// --> Prepend Root URL
		if (substr($linkedUrl, 0, 1) == "/") {
			return $this->rootUrl . substr($linkedUrl, 1);
		}

		// Document fragment
		// --> Don't scan it
		if (substr($linkedUrl, 0, 1) == "#") {
			return '';
		}

		// Document-relative URLs
		// --> Prepend the URL of the page containing the linked URL
		if (!parse_url($linkedUrl)) {

			// Force trailing slash on $pageUrlContainingTheLinkedUrl
			if (substr($pageUrlContainingTheLinkedUrl, -1) != '/') $pageUrlContainingTheLinkedUrl .= '/';

			// Append $linkedUrl to $pageUrlContainingTheLinkedUrl
			return $pageUrlContainingTheLinkedUrl . $linkedUrl;

		}

		// Would be strange if we ever got here, but hey ...
		return '';


	}


	/**
	 * Get the contents of a given URL (via GET)
	 * @param  String $url 	The URL of the page to get the contents of
	 * @return String
	 */
	private function getContents($url) {

		// Init CURL
		$curl = curl_init();

		@curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
			CURLOPT_TIMEOUT_MS => 10000
		));

		// Fetch the page contents
		$resp = curl_exec($curl);

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
