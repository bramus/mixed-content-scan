# Mixed Content Scan

Scan your HTTPS-enabled website for Mixed Content
Built by Bramus! - [https://www.bram.us/](https://www.bram.us/)

## About

`Mixed Content Scan` is a (quick and dirty) scanner to scanning all (linked) pages of an HTTPS-enabled website for Mixed Content.

The script starts at a given URL, and then starts processing it:

*  All contained `img[src]`, `iframe[src]`, `script[src]`, and `link[href][rel="stylesheet"]` elements are checked for being Mixed Content or not
*  All contained `a[href]` elements linking to the same or a deeper level are successively processed for Mixed Content.

## Usage

Run this script from the CLI, a such:

```
$ php bin/scanner.php https://www.bram.us/
```

The script itself will start scanning and give feedback whilst running. When Mixed Content is found, the URLs will be shown on screen:

```
$ php bin/scanner.php https://www.bram.us/
Scanning https://www.bram.us/
[2014-12-10 15:38:31] 00000 - https://www.bram.us/
[2014-12-10 15:38:32] 00001 - https://www.bram.us/projects/
[2014-12-10 15:38:33] 00002 - https://www.bram.us/projects/mint-custom-title/
[2014-12-10 15:38:33] 00003 - https://www.bram.us/projects/bramusicq/
[2014-12-10 15:38:33] 00004 - https://www.bram.us/projects/gm_bramus/
[2014-12-10 15:38:34] 00005 - https://www.bram.us/projects/js_bramus/
[2014-12-10 15:38:34] 00006 - https://www.bram.us/projects/js_bramus/jsprogressbarhandler/
[2014-12-10 15:38:36] 00007 - https://www.bram.us/projects/js_bramus/lazierload/
[2014-12-10 15:38:37] 00008 - https://www.bram.us/projects/the-box-office/
[2014-12-10 15:38:37] 00009 - https://www.bram.us/projects/tinymce-plugins/
[2014-12-10 15:38:38] 00010 - https://www.bram.us/projects/tinymce-plugins/tinymce-classes-and-ids-plugin-bramus_cssextras/
[2014-12-10 15:38:38] 00011 - https://www.bram.us/projects/flashlightboxinjector/
[2014-12-10 15:38:40] 00012 - https://www.bram.us/contact/
[2014-12-10 15:38:40] 00013 - https://www.bram.us/2014/12/09/youtube-rewind-2014/
[2014-12-10 15:38:41] 00014 - https://www.bram.us/2014/12/09/6-billion-tweets/
[2014-12-10 15:38:41] 00015 - https://www.bram.us/2014/12/09/little-dragon-underbart/
[2014-12-10 15:38:41] 00016 - https://www.bram.us/2014/12/09/yik-yak-messaging-app-vulnerability/
[2014-12-10 15:38:42] 00017 - https://www.bram.us/2014/11/13/https-everywhere/
[2014-12-10 15:38:42] 00018 - https://www.bram.us/2014/12/09/the-state-of-javascript-in-2015/
[2014-12-10 15:38:43] 00019 - https://www.bram.us/2013/06/27/the-franticness-of-working-in-the-web-business/
[2014-12-10 15:38:43] 00020 - https://www.bram.us/2014/12/09/crossbeat-uprising/
[2014-12-10 15:38:44] 00021 - https://www.bram.us/2014/12/09/its-all-about-time-timing-attacks-in-php/

...

[2014-12-10 15:38:56] 00050 - https://www.bram.us/2008/11/10/jsprogressbarhandler-033/
[2014-12-10 15:38:56] 00051 - https://www.bram.us/demo/projects/lazierload/
  - http://farm2.static.flickr.com/1212/1285026452_0aeb38b6e6.jpg
  - http://farm2.static.flickr.com/1074/1273115418_a77357040a.jpg
  - http://farm2.static.flickr.com/1096/1273106588_91f7a736c6.jpg
  - http://farm2.static.flickr.com/1324/1216309045_31ca82f9d9.jpg
  - http://farm2.static.flickr.com/1262/1217169586_e4b2bfa7df.jpg
  - http://farm2.static.flickr.com/1149/1216304291_63fd48d9c4.jpg
  - http://farm2.static.flickr.com/1366/1216301505_51b3c590ff.jpg
  - http://farm2.static.flickr.com/1184/1216299847_c57975bed2.jpg
  - http://farm2.static.flickr.com/1085/1217158084_a9b059d25b.jpg
  - http://farm2.static.flickr.com/1040/1216293529_3b7c044815.jpg
  - http://farm2.static.flickr.com/1029/1084232736_5b8c023f46.jpg
  - http://farm2.static.flickr.com/1318/1043062251_17071a8cc7.jpg
  - http://farm2.static.flickr.com/1221/1043059543_05713e6156.jpg
  - http://www.google-analytics.com/urchin.js
[2014-12-10 15:38:57] 00052 - https://www.bram.us/wordpress/wp-content/uploads/2008/02/lazierload_04.zip
[2014-12-10 15:38:57] 00053 - https://www.bram.us/wordpress/wp-content/uploads/2008/02/lazierload_03.zip
[2014-12-10 15:38:57] 00054 - https://www.bram.us/wordpress/wp-content/uploads/2007/09/lazierload_02.zip
[2014-12-10 15:38:57] 00055 - https://www.bram.us/2011/09/30/css-regions-and-css-exclusions/
[2014-12-10 15:38:57] 00056 - https://www.bram.us/2014/06/04/good-looking-shapes-gallery/

...
```

## Handling errors

Internally Mixed Content Scan uses CURL to perform requests. If an error should be encountered (in case of a connection loss for example), the error will be shown on screen:

```
...

[2014-12-10 15:08:58] 00125 - https://www.bram.us/2008/04/15/trapped/
 - cURL Error (28): SSL connection timeout

 ...
```

## Ignoring links

It's possible to define a list of patterns to ignore. To do so, edit the array defined in `conf/ignorePatterns.php`. The only prerequisite is that the array itself is returned, and that the patterns are PCRE patterns.

The default ignore patterns defined are those for a Wordpress installation:

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

Note: The [PHP PCRE Cheat Sheet](https://www.cs.washington.edu/education/courses/190m/12sp/cheat-sheets/php-regex-cheat-sheet.pdf) might come in handy.

## Known issues

Mixed Content Scan:

* Doesn't take `<base href="...">` into account _(but who uses that, anyways?)_
* Doesn't scan linked `.css` or `.js` files themselves for Mixed Content

Please open an issue _(or fix it and perform a pull request ;))_ when you've encountered a problem.
