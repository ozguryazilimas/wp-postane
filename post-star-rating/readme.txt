=== Post Star Rating ===
Tags: rating, voting, post, stars
Contributors: O Doutor

Post Star Rating is a plugin that allows blog users to rate posts in a classic five stars way.

== Installation ==

1. Upload to your plugins folder, usually 'wp-content/plugins/'

2. Activate the plugin on the plugin screen.

2.1. If you have a Wordpress version previous at 2.0 you have to run the install.php script. You can do this by typing this URL in your navigator: http://your_server_domain/your_blog_directory/wp-content/plugins/post-star-rating/install.php
]
3. Add the PSR_show_voting_stars() tag in your template files. For example: put this after the post title: <?php PSR_show_voting_stars(); ?>

4. Start rating!!

== Extra tags ==

If you want to show scoreboards on your blog you can use the following tags:

- PSR_bests_of_month(): Shows a list with the 10 best post of the current month
- PSR_bests_of_month(month): Shows a list with the 10 best post of the "month" specified
- PSR_bests_of_month(month, limit): Shows a list with the "limit" best post of the "month" specified
- PSR_bests_of_moment(): Shows a list with the 10 best post of the moment. It shows trends too.
- PSR_bests_of_moment(limit): Shows a list with the "limit" best post of the moment. It shows trends too.


== Screenshots ==

1. Stars after a votation.
2. Stars for rating.
3. Output of PSR_bests_of_moment() after a little CSS tunning.
