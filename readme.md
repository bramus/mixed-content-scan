# Mixed Content Scan

![Source](http://img.shields.io/badge/source-bramus/mixed--content--scan-blue.svg?style=flat-square) ![Version](https://img.shields.io/packagist/v/bramus/mixed-content-scan.svg?style=flat-square) ![Downloads](https://img.shields.io/packagist/dt/bramus/mixed-content-scan.svg?style=flat-square) ![License](https://img.shields.io/packagist/l/bramus/mixed-content-scan.svg?style=flat-square)


Scan your HTTPS-enabled website for Mixed Content

Built by Bramus! ([https://www.bram.us/](https://www.bram.us/)) and [Contributors](https://github.com/bramus/mixed-content-scan/graphs/contributors)

## About

`Mixed Content Scan` is a CLI Script which crawls+scans HTTPS-enabled websites for Mixed Content.

The script starts at a given URL, and then starts processing it:

*  All contained `img[src|srcset]`, `iframe[src]`, `script[src]`, `link[href][rel="stylesheet"]`, `object[data]`, `form[action]`, `embed[src]`, `video[src]`, `audio[src]`, `source[src|srcset]`, and `params[name="movie"][value]` elements are checked for being Mixed Content or not
*  All contained `a[href]` elements linking to the same or a deeper level are successively processed for Mixed Content.

## Installation

Installation is possible using [Composer](https://getcomposer.org/)

```
composer global require bramus/mixed-content-scan:~2.8
```

_New to Composer?_ It's a command line tool for dependency management in PHP. On Linux/Unix/OSX you will need to [download and run the install script](https://getcomposer.org/download/) and _(recommended)_ successively [move `composer.phar` to a global location](https://getcomposer.org/doc/00-intro.md#globally). On Windows you will need to [run the installer](https://getcomposer.org/doc/00-intro.md#installation-windows)

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

Mixed Content Scan uses ANSI coloring, provided by [bramus/ansi-php](https://github.com/bramus/ansi-php), so one can easily spot errors based on the color.

## Advanced usage / CLI Options

Mixed Content Scan support several CLI options which can manipulate its behavior:

- `--output=path/to/file`: File to output results to. Defaults to `php://stdout` (= show on screen).
- `--format=ansi|no-ansi|json`: Define which formatter to use for outputting the results
    - `ansi` _(Default)_: ANSI Colored Line Formatter
    - `no-ansi`: Monolog Line Formatter
    - `json`: Monolog JSON Formatter
- `--no-crawl`: Don't crawl scanned pages for new pages
- `--no-check-certificate`: Don\'t check the certificate for validity (e.g. allow self-signed or missing certificates)
- `--timeout=value-in-milliseconds`: How long to wait for each request to complete. Defaults to 10000ms.
- `--input=path/to/file`: Specify a file containing a list of links as the source, instead of parsing the passed in URL. Automatically enables `--no-crawl`
- `--ignore=path/to/file`: File containing URL patterns to ignore. See _Ignoring links_ further down on how to build this file.
- `--loglevel=level`: The Monolog loglevel to log at. Defaults to `200` (= `info`). Both numeric values, as string (lowercase) values are supported as input. See [Monolog Log Levels](https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#log-levels) for more info.
- `--user-agent='user-agent'`: Set the user agent to be used when crawling.

Example: `mixed-content-scan https://www.bram.us/ --ignore=./wordpress.txt --output=./results.txt --format=no-ansi`

## Handling errors

Internally Mixed Content Scan uses Curl to perform requests. If an error should be encountered (in case of a connection loss for example), the error will be shown on screen:

```
...
[2015-01-07 12:56:43] MCS.INFO: 00003 - https://www.bram.us/projects/bramusicq/ [] []
[2015-01-07 12:56:53] MCS.CRITICAL: cURL Error (28): SSL connection timeout [] []
...
```

## Ignoring links

It's possible to define a list of patterns to ignore. To do so, create a text file with on each line a PCRE pattern to ignore. Pass in the path to that file using the `--ignore` option. Lines starting with `#` are considered being comments and therefore are ignored.

For a WordPress installation, the ignore pattern file – which is distributed with Mixed Content Scan in `ignorepattens/wordpress.txt` – would be this:

```
# Paginated Overview Links
^{$rootUrl}/page/(\d+)/$

# Single Post Links
# ^{$rootUrl}/(\d+)/(\d+)/

# Tag Overview Links
^{$rootUrl}/tag/

# Author Overview Links
^{$rootUrl}/author/

# Category Overview Links
^{$rootUrl}/category/

# Monthly Overview Links
^{$rootUrl}/(\d+)/(\d+)/$

# Year Overview Links
^{$rootUrl}/(\d+)/$

# Comment Subscription Link
^{$rootUrl}/comment-subscriptions

# Wordpress Core File Links
^{$rootUrl}/(.*)?wp\-(.*)\.php

# Archive Links
^{$rootUrl}/archive/

# Replyto Links
\?replytocom\=
```

The `{$rootUrl}` token in each pattern will be replaced with the (root) URL passed into the script.

Note: The [PHP PCRE Cheat Sheet](https://www.cs.washington.edu/education/courses/190m/12sp/cheat-sheets/php-regex-cheat-sheet.pdf) might come in handy.

## Known issues

Mixed Content Scan:

* Doesn't take `<base href="...">` into account _(but who uses that, anyways?)_
* Doesn't scan linked `.css` or `.js` files themselves for Mixed Content
* Doesn't scan inline `<script>` or `<style>` for mixed content

Please open an issue _(or fix it and perform a pull request ;))_ when you've encountered a problem.
