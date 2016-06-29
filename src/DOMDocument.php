<?php

namespace Bramus\MCS;

/**
 * Represents an entire HTML or XML document; with some extras
 * @author Bramus! <bramus@bram.us>
 */
class DOMDocument extends \DOMDocument
{
    public $tags = [
        'audio'  => ['src'],
        'embed'  => ['src'],
        'form'   => ['action'],
        'iframe' => ['src'],
        'img'    => ['src', 'srcset'],
        'link'   => ['href'],
        'object' => ['data'],
        'param'  => ['value'],
        'script' => ['src'],
        'source' => ['src', 'srcset'],
        'video'  => ['src']
    ];

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

        // Loop through all the tags and attributes we want to find
        // references to mixed content
        foreach ($this->tags as $tag => $attributes) {
            foreach ($this->getElementsByTagName($tag) as $el) {
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

        // Return found URLs
        return $mixedContentUrls;
    }
}
