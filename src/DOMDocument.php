<?php

namespace Bramus\MCS;

/**
 * Represents an entire HTML or XML document; with some extras
 * @author Bramus! <bramus@bram.us>
 */
class DOMDocument extends \DOMDocument
{
    /**
     * Collection that indicate sources of element attribute values we want to check.
     *
     * @var array
     */
    public $tags = [
        'audio'  => ['src'],
        'embed'  => ['src'],
        'form'   => ['action'],
        'iframe' => ['src'],
        'img'    => ['src', 'srcset', 'data-src'],
        'object' => ['data'],
        'param'  => ['value'],
        'script' => ['src'],
        'source' => ['src', 'srcset'],
        'video'  => ['src']
    ];

    /**
     * Loop all links and extract their href value.
     *
     * @return array All href values.
     */
    public function extractLinks()
    {
        $links = [];

        /** @var \DOMElement $el */
        foreach ($this->getElementsByTagName('a') as $el) {
            if ($el->hasAttribute('href')) {
                $links[] = $el->getAttribute('href');
            }
        }

        return $links;
    }

    /**
     * Extract URLs which are found to be Mixed Content.
     *
     * @return array URLs
     */
    public function extractMixedContentUrls()
    {
        // Array holding all URLs which are found to be Mixed Content
        // We'll return this one at the very end
        $mixedContentUrls = [];

        // Loop through all the tags and attributes we want to find
        // references to mixed content
        foreach ($this->tags as $tag => $attributes) {
            /** @var \DOMElement $el */
            foreach ($this->getElementsByTagName($tag) as $el) {
                /** @var array $attributes */
                foreach ($attributes as $attribute) {
                    if ($el->hasAttribute($attribute)) {
                        $url = $el->getAttribute($attribute);
                        if (stripos($url, 'http://') !== false) {
                            $mixedContentUrls[] = $url;
                        }
                    }
                }
            }
        }
        
        $links=$this->extractLinksTags();
        if (count($links)) $mixedContentUrls=array_merge($mixedContentUrls,$links);
        
        // Return found URLs
        return $mixedContentUrls;
    }

    public function extractLinksTags()
    {
        $mixedContentUrls = [];
        foreach ($this->getElementsByTagName("link") as $el) {
            // skip <link tag whose "rel" attribute is "profile" or "alternate": they are not included by browsers in the page.
            if ($el->hasAttribute("rel")) {
                $rel = $el->getAttribute("rel");
                if ($rel=="profile" || $rel=="alternate") continue;
            }
            if ($el->hasAttribute("href")) {
                $url = $el->getAttribute($attribute);
                if (stripos($url, 'http://') !== false) {
                    $mixedContentUrls[] = $url;
                }
            }
        }
        
        // Return found URLs
        return $mixedContentUrls;
    }

}
