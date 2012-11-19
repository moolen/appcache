appcache
========

Appcache Plugin (Wordpress)
This plugin creates a dynamic appcache manifest.

#Installation

Installation: 
move the appcache folder into www.your-url.com/wp-content/plugins/
add to your themes' header.php the appcache manifest:
<\html manifest="<\?php echo site_url(); ?>/wp-content/plugins/appcache/manifest.appcache.php?appcache_id=<\?php echo the_ID()."&referrer=".urlencode($_SERVER['SCRIPT_URL']); ?>">
..and remove the backslashes obviously.

The file itselfs gets 2 GET parameters: 
1: ID of the post
2: referrer (which site or post is sending the request it?)

The file creates a appcache manifest based upon the parameters. 
Example: ID is 354 and referrer is '/my-sub-page' .

1. It gets the id-hash from database (outputs: "# version: <somehash>" OR  if not set "# version: 1.0")
2. parse wp_postmeta for image tags (where post_id = 354)
3. parse wp_posts for image tags (where post_id= 354)
4. Parse widgets for img tags
5. PARSE wp_postmeta for customfields (if option is enabled)
6. parse frontpage posts for image tags (only if referrer = / )
7. Cache Themedirectory
8. Cache other directories

On the save_post event a md5-hash of the current timestamp will be written into the database (specified by ID).
So the appcache manifest gets updated IF there is a new version of a post (see point 1).

In addition it will cache the whole /wp-content/themes/current-theme/ directory (except .php, .sass, .less ...)
You can also cache a custom folder in the settings panel.

furthermore this appcache plugin supports the AdvancedCustomFields plugin (www.advancedcustomfields.com)
watch the plugin in action: www.skankshot.de (in Chrome: open dev-tools CMD+ALT+J, go to Console-Tab; this is where you can see the appcache-events)

The Repository of the Appcache: 
https://github.com/moolen/appcache