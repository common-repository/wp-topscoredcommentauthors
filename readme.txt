=== WP-TopScoredCommentAuthors ===
Contributors: luzifer
Donate link: http://blog.knut.me/wp-donate/
Tags: comments, backlink, score
Requires at least: 2.3
Tested up to: 2.9
Stable tag: 0.4.1

This Widget shows your Top-Comment-Authors based on a score calculated from comment-age and frequency

== Description ==

This plugin is a replacement for the default "Top Ten Comments" because that plugin did not really do 
what I wanted. In the default plugin shows ten people who gave the most comments ever. But if an author
left 500 comments two years ago and left the page for ever after this he would show up at the top 
position until another author would reach more than 500 comments.

In this plugin the top commenters score is calculated by using the time left since a comment and also 
the number of comments. So the author mentioned in first paragraph would slowly move out of the 
toplist and other comment authors posting more recently will get a chance to show up in the list.

== Installation ==

1. Upload `WP-TopScoredCommentAuthors.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the widgets using the settings-page to be found in the settings menu
1. Place the widget you want to use in your sidebar (`WP-TopScoredCommentAuthors` = List of people with a simple Link, `WP-TopScoredCommentGrid` = Grid of Gravatars for the people linked to their pages)

== Screenshots ==

1. The widget settings page (English translation)

== Changelog ==

= 0.4.1 =
* Fixed xHTML-Error in gravatar grid

= 0.4.0 =
* Added french translation of the backend (incomplete)
* Added spanish translation of the backend (incomplete)
* Added option to show score for each entry to a logged in admin
* Moved settings to separate settings page
* Added gravatar view of the list

= 0.3.0 =
* Added I18n-support
* Added german translation of the backend

= 0.2.1 =
* Hotfix for hosts not supporting shorttags

= 0.2.0 =
* Initial published version of the plugin