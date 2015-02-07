# Mixed Content Scan

Scan your HTTPS-enabled website for Mixed Content

Built by Bramus! - [https://www.bram.us/](https://www.bram.us/)

## About

`Mixed Content Scan` is a CLI Script which crawls+scans HTTPS-enabled websites for Mixed Content.

The script starts at a given URL, and then starts processing it:

*  All contained `img[src]`, `iframe[src]`, `script[src]`, and `link[href][rel="stylesheet"]`, and `object[data]` elements are checked for being Mixed Content or not
*  All contained `a[href]` elements linking to the same or a deeper level are successively processed for Mixed Content.

## Installation

Installation is possible using Composer

```
composer global require bramus/mixed-content-scan ~2.0
```

_Don't know what this Composer thing is?_ If you don't know how to work with Composer you may download the 1.0 release from [the Releases page](https://github.com/bramus/mixed-content-scan/releases). It's a first (rough) version of MCS which also does the job.

## Usage

Run this script from the CLI, a such:

```
$ mixed-content-scan https://www.bram.us/
```

The script itself will start scanning and give feedback whilst running. When Mixed Content is found, the URLs causing Mixed Content warnings will be shown on screen:

```
$ mixed-content-scan https://www.bram.us/
[2015-01-07 12:54:20] MCS.NOTICE: Scanning https://www.bram.us/ [] []
[2015-01-07 12:54:21] MCS.INFO: 00000 - https://www.bram.us/ [] []
[2015-01-07 12:54:22] MCS.INFO: 00001 - https://www.bram.us/projects/ [] []
[2015-01-07 12:54:22] MCS.INFO: 00002 - https://www.bram.us/projects/mint-custom-title/ [] []
[2015-01-07 12:54:23] MCS.INFO: 00003 - https://www.bram.us/projects/bramusicq/ [] []
[2015-01-07 12:54:24] MCS.INFO: 00004 - https://www.bram.us/projects/gm_bramus/ [] []
[2015-01-07 12:54:24] MCS.INFO: 00005 - https://www.bram.us/projects/js_bramus/ [] []
[2015-01-07 12:54:26] MCS.INFO: 00006 - https://www.bram.us/projects/js_bramus/jsprogressbarhandler/ [] []
[2015-01-07 12:54:27] MCS.INFO: 00007 - https://www.bram.us/projects/js_bramus/lazierload/ [] []
[2015-01-07 12:54:27] MCS.INFO: 00008 - https://www.bram.us/projects/the-box-office/ [] []
[2015-01-07 12:54:28] MCS.INFO: 00009 - https://www.bram.us/projects/tinymce-plugins/ [] []
[2015-01-07 12:54:29] MCS.INFO: 00010 - https://www.bram.us/projects/tinymce-plugins/tinymce-classes-and-ids-plugin-bramus_cssextras/ [] []
[2015-01-07 12:54:30] MCS.INFO: 00011 - https://www.bram.us/projects/flashlightboxinjector/ [] []

...

[2015-01-07 12:54:45] MCS.INFO: 00036 - https://www.bram.us/2007/06/04/accessible-expanding-and-collapsing-menu/ [] []
[2015-01-07 12:54:45] MCS.ERROR: 00037 - https://www.bram.us/demo/projects/jsprogressbarhandler/ [] []
[2015-01-07 12:54:45] MCS.WARNING: http://www.google-analytics.com/urchin.js [] []
[2015-01-07 12:54:46] MCS.INFO: 00038 - https://www.bram.us/2008/07/11/ror-progress-bar-helper/ [] []
[2015-01-07 12:54:46] MCS.INFO: 00039 - https://www.bram.us/2008/11/10/jsprogressbarhandler-033/ [] []
[2015-01-07 12:54:47] MCS.ERROR: 00040 - https://www.bram.us/demo/projects/lazierload/ [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1212/1285026452_0aeb38b6e6.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1074/1273115418_a77357040a.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1096/1273106588_91f7a736c6.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1324/1216309045_31ca82f9d9.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1262/1217169586_e4b2bfa7df.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1149/1216304291_63fd48d9c4.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1366/1216301505_51b3c590ff.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1184/1216299847_c57975bed2.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1085/1217158084_a9b059d25b.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1040/1216293529_3b7c044815.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1029/1084232736_5b8c023f46.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1318/1043062251_17071a8cc7.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://farm2.static.flickr.com/1221/1043059543_05713e6156.jpg [] []
[2015-01-07 12:54:47] MCS.WARNING: http://www.google-analytics.com/urchin.js [] []
[2015-01-07 12:54:47] MCS.INFO: 00041 - https://www.bram.us/2011/09/30/css-regions-and-css-exclusions/ [] []
[2015-01-07 12:54:47] MCS.INFO: 00042 - https://www.bram.us/2014/06/04/good-looking-shapes-gallery/ [] []

...
```

Mixed Content Scan uses ANSI coloring so one can easily spot errors based on the color.

## Handling errors

Internally Mixed Content Scan uses Curl to perform requests. If an error should be encountered (in case of a connection loss for example), the error will be shown on screen:

```
...
[2015-01-07 12:56:43] MCS.INFO: 00003 - https://www.bram.us/projects/bramusicq/ [] []
[2015-01-07 12:56:53] MCS.CRITICAL: cURL Error (28): SSL connection timeout [] []
...
```

## Ignoring links

It's possible to define a list of patterns to ignore. To do so, edit the array defined in `conf/ignorePatterns.php`. The only prerequisite is that the array itself is returned, and that the patterns are PCRE patterns.

The default ignore patterns defined are those for a WordPress installation:

```
return [
    '^{$rootUrl}/page/(\d+)/$', // Paginated Overview Links
    // '^{$rootUrl}/(\d+)/(\d+)/', // Single Post Links
    '^{$rootUrl}/tag/', // Tag Overview Links
    '^{$rootUrl}/author/', // Author Overview Links
    '^{$rootUrl}/category/', // Category Overview Links
    '^{$rootUrl}/(\d+)/(\d+)/$', // Monthly Overview Links
    '^{$rootUrl}/(\d+)/$',  // Year Overview Links
    '^{$rootUrl}/comment-subscriptions', // Comment Subscription Link
    '^{$rootUrl}/(.*)?wp\-(.*)\.php', // Wordpress Core File Links
    '^{$rootUrl}/archive/', // Archive Links
    '\?replytocom\=', // Replyto Links
];
```

The `{$rootUrl}` token in each pattern will be replaced with the (root) URL passed into the script.

Note: The [PHP PCRE Cheat Sheet](https://www.cs.washington.edu/education/courses/190m/12sp/cheat-sheets/php-regex-cheat-sheet.pdf) might come in handy.

## Known issues

Mixed Content Scan:

* Doesn't take `<base href="...">` into account _(but who uses that, anyways?)_
* Doesn't scan linked `.css` or `.js` files themselves for Mixed Content
* Doesn't scan inline `<script>` or `<style>` for mixed content
* Doesn't scan `<form>` tags that point at `http://` endpoints

Please open an issue _(or fix it and perform a pull request ;))_ when you've encountered a problem.
