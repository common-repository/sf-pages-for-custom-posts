=== SF Pages For Custom Posts ===
Contributors: GregLone
Tags: content, page, post types
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: trunk
License: GPLv3
License URI: http://www.screenfeed.fr/gpl-v3.txt

Allows you to easily assign to a page your custom post types with a settings panel, like you can do with normal posts.

== Description ==
= This plugin is not maintained anymore! =
Please see [my other plugin SF Archiver](http://wordpress.org/extend/plugins/sf-archiver/) which does quite the same thing.

= Description =
Sick of creating custom page templates with a specific loop for your custom post types?
This plugin allows you to easily assign to a page your custom post types with a settings panel, like you can do with normal posts.

Go to Settings > Pages for C. Posts, all your public custom post types will be listed.
You can choose the page where they will be displayed (of course), the number of posts per page, and also the template to use if needed (e.g. Page, Archive, Home page...). In most cases you don't have to specify a template, just let it as it is (but this can be useful with some themes).
You can also choose to display a custom post type on your home page: just specify the same page as you have chosen in Settings > Reading, and optionally, specify the "Home page" template.

Note that this plugin is not for creating a secondary loop in your page, it will replace the original loop.

This plugin is still in beta-test, so it may not work for everybody. So far, it works fine with WP 3.2.1 and TwentyEleven for me.

= Translations =
* English
* French

== Installation ==

1. Extract the plugin folder from the downloaded ZIP file.
1. Upload SF Pages For Custom Posts folder to your /wp-content/plugins/ directory.
1. Activate the plugin from the "Plugins" page in your Dashboard.
1. Go to settings.

== Frequently Asked Questions ==
= What is the best setting for the template option? =
It depends of your theme, but generally :
* If the post type is displayed in any normal page, you won't have to set a template, leave this empty.
* If the post type is displayed in the front page, the best choices are probably "Page" or "Home Page".

Eventually, check out [my blog](http://scri.in/pfcp) (sorry, it's in french)

== Screenshots ==
1. screenshot-1.png

== Changelog ==

= 0.5 =
* 2011/09/15
* Major rewrite, stability improvement
* Todo : settings page will be rewritten in the next release, you'll can choose a post type for each page, instead of the inverse

= 0.4 =
* 2011/09/12
* First public release
* Thanks to Juliobox for the security review

== Upgrade Notice ==
Nothing special