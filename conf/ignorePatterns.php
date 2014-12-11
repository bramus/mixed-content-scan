<?php

/**
 * A set of patterns to ignore when scanning
 * 
 * This list defaults to Wordpress Archive Pages which shouldn't be
 * crawled as the contain all single pages +
 * 
 * Pro-tip: if your paginated overview links contain all full posts, 
 * it's safe to comment out line 16 and remove the comment from
 * line 17. It will speed things up significantly as the scanner
 * then doesn't have to scan each post individually.
 * 
 */
return [
	'\.(jpg|jpeg|png|gif|zip|pdf)$',
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

	// '^{$rootUrl}/tweets/', // Tweets webapp
];