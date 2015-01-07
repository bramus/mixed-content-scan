<?php

namespace Bramus\MCS;

/**
 * Represents an entire HTML or XML document; with some extras
 * @author Bramus! <bramus@bram.us>
 */
class DOMDocument extends \DOMDocument
{
    public function extractLinks()
    {
        $links = [];

        // Loop all links and extract their href value
        foreach ($this->getElementsByTagName('a') as $el) {
            if ($el->hasAttribute('href')) {
                $links[] = $el->getAttribute('href');
            }
        }

        return $links;
    }

    public function extractMixedContentUrls()
    {
        // Array holding all URLs which are found to be Mixed Content
        // We'll return this one at the very end
        $mixedContentUrls = [];

        // Check all iframes contained in the HTML
        foreach ($this->getElementsByTagName('iframe') as $el) {
            if ($el->hasAttribute('src')) {
                $url = $el->getAttribute('src');
                if (substr($url, 0, 7) == "http://") {
                    $mixedContentUrls[] = $url;
                }
            }
        }

        // Check all images contained in the HTML
        foreach ($this->getElementsByTagName('img') as $el) {
            if ($el->hasAttribute('src')) {
                $url = $el->getAttribute('src');
                if (substr($url, 0, 7) == "http://") {
                    $mixedContentUrls[] = $url;
                }
            }
        }

        // Check all script elements contained in the HTML
        foreach ($this->getElementsByTagName('script') as $el) {
            if ($el->hasAttribute('src')) {
                $url = $el->getAttribute('src');
                if (substr($url, 0, 7) == "http://") {
                    $mixedContentUrls[] = $url;
                }
            }
        }

        // Check all stylesheet links contained in the HTML
        foreach ($this->getElementsByTagName('link') as $el) {
            if ($el->hasAttribute('href') && $el->hasAttribute('rel') && ($el->getAttribute('rel') == 'stylesheet')) {
                $url = $el->getAttribute('href');
                if (substr($url, 0, 7) == "http://") {
                    $mixedContentUrls[] = $url;
                }
            }
        }

        // Check all `object` elements contained in the HTML
        foreach ($this->getElementsByTagName('object') as $el) {
            if ($el->hasAttribute('data')) {
                $url = $el->getAttribute('data');
                if (substr($url, 0, 7) == "http://") {
                    $mixedContentUrls[] = $url;
                }
            }
        }

        // Return found URLs
        return $mixedContentUrls;
    }
}
