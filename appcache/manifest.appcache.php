<?php
/*
Plugin Name: HTML5 Application Cache
Plugin URI: none yet.
Description: Creates a dynamically rendered application cache manifest and embeds it into your current template. Just activate the plugin and everything gets cached. for more details see the application cache's settings panel.
Version: v0.1
Author: fwd.io
Author URI: http://www.fwd.io
License: MIT Licensed. Do what you want!
*/
if(empty($_GET['appcache_id'])){
	/************************************************* 
	*	HERE ARE THE BACKEND FUNCTIONS
	**************************************************/
	if(function_exists('add_action')){
		// add manifest="" to html tag
		//add_filter( 'language_attributes', 'add_html_manifest' );
		//function add_html_manifest( $output ) {
		//    $output .= 'manifest="'. site_url() . '/wp-content/plugins/appcache/manifest.appcache.php?appcache_id=<?php echo the_ID()."&referrer=".urlencode($_SERVER[\'SCRIPT_URL\']); ?\>';			
		//    return $output;
	    //}
		//safe_post event
		add_action( 'save_post', 'updateHash' );
		function updateHash( $post_id ){
			if ( !wp_is_post_revision( $post_id ) ) {
				global $wpdb;
				$isset = $wpdb->get_results("
					SELECT option_value 
					FROM wp_options
					WHERE option_name='appcacheupdatehash$post_id'
				");
				if(empty($isset)){
					$hash = md5(time());
					$wpdb->query("	
						INSERT INTO wp_options (option_name, option_value)
						VALUES ('appcacheupdatehash$post_id','".$hash."')
					");
				}else{
					$hash = md5(time());
					$wpdb->query("	
						UPDATE wp_options 
						SET option_value='".$hash."'
						WHERE option_name='appcacheupdatehash$post_id'
					");
				}
			}
		}
		if(in_array("HTTP_REFERRER", $_SERVER)){
			header('Content-Type: text/plain');
		}
		add_action('admin_menu', 'plugin_admin_add_page');
		function plugin_admin_add_page() {
			add_options_page('Appcache Settings', 'Appcache', 'manage_options', 'plugin', 'plugin_options_page');
		}
		function plugin_options_page() {
			?>
			<div>
			<h2>Appcache Settings</h2>
			<p><b>FIRST:</b> Simply add the <b>manifest=""</b> statement to your html tag in your theme's header.php file.<br>
			<code>
				<\html manifest="<\?php echo site_url(); ?>/wp-content/plugins/appcache/manifest.appcache.php?appcache_id=<\?php echo the_ID()."&referrer=".urlencode($_SERVER['SCRIPT_URL']); ?>">			</code>
			<br>And remove the backslashes obviously.</p>
			<p>Per default this plugin runs through the (currently activated) theme directory and caches it. 
			<p>If you use the Advanced Customfields Plugin (http://www.advancedcustomfields.com/) be sure to tick the checkbox below. The Images will then be cached.</p>
			<br>The text-input-fields below  are for extra folders that you want to be cached.<br> e.g. if you want to cache the /wp-content/uploads folder just type it in the inputfield. the '/' at the beginning and at the end are optional. <br><b>ONLY USE URLs relative to the home directory</b> (where your wp-config.php is).</p>
			<p>This script ignores <b>.less, .sass, .scss</b> files per default - you can add other file-extensions in your configuration below. These are <b>global settings</b>.</p>
			<form action="options.php" method="post">
			<?php settings_fields('appcache_plugin'); ?>
			<?php do_settings_sections('plugin'); ?>
			<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
			</form></div>
			<?php
		}
		add_action('admin_init', 'plugin_admin_init');
		function plugin_admin_init(){
			register_setting( 'appcache_plugin', 'appcache_plugin', 'plugin_options_validate' );
			add_settings_section('plugin_main', '', 'plugin_section_text', 'plugin');
			add_settings_field('plugin_text_string', '', 'plugin_setting_string', 'plugin', 'plugin_main');
		}
		function plugin_section_text() {
			echo '';
		}
		function plugin_setting_string() {
			$options = get_option('appcache_plugin');
			//print_r($options);
			echo"<input type='checkbox' name='appcache_plugin[customfield]' value='true'";
			if($options['customfield'] == 'true'){
				echo 'checked';
			}
			echo ">";
			echo "&nbsp;Do you use the Customfields plugin?<br>";
			echo "<p >Enter filetypes you want to ignore:<br> e.g.: .md or .txt (each seperated only with a <a href='http://en.wikipedia.org/wiki/Vertical_bar'>vertical bar</a> - no space in between)<br><input type='text' name='appcache_plugin[ignored_files]' value='".$options['ignored_files']."' style='width:200px;'><b>&nbsp;like: .md|.txt|.sql</b></p><br>";
			echo"<input type='checkbox' name='appcache_plugin[theme_dynamic_caching]'";if($options['theme_dynamic_caching'] == 'true'){ echo' checked ';} echo" value='true'>";
			echo "<p>disable dynamic filecaching in current theme directory</p>";
			echo "<h4>Your Customfolders you want to be cached:<br><small>use relative url like: \"/wp-content/uploads/\"</small></h4>";
			echo "<label><b>folder #1: </b></label><input id='plugin_text_string' name='appcache_plugin[url1]' size='40' type='text' value='{$options['url1']}' />
			<b>&nbsp;disable dynamic filecaching: &nbsp;</b>
				<input type='checkbox' id='plugin_text_string' name='appcache_plugin[ddf1]'"; if($options['ddf1'] == 'true'){ echo' checked ';} echo "value='true'>
			<br><br>";
			echo "<label><b>folder #2: </b></label><input id='plugin_text_string' name='appcache_plugin[url2]' size='40' type='text' value='{$options['url2']}' />
			<b>&nbsp;disable dynamic filecaching: &nbsp;</b>
				<input type='checkbox' id='plugin_text_string' name='appcache_plugin[ddf2]'"; if($options['ddf2'] == 'true'){ echo' checked ';} echo"value='true'>
			<br><br>";
			echo "<label><b>folder #3: </b></label><input id='plugin_text_string' name='appcache_plugin[url3]' size='40' type='text' value='{$options['url3']}' />
			<b>&nbsp;disable dynamic filecaching: &nbsp;</b>
				<input type='checkbox' id='plugin_text_string' name='appcache_plugin[ddf3]'";if($options['ddf3'] == 'true'){ echo' checked ';} echo"value='true'>
			<br><br>";
			echo "<label><b>folder #4: </b></label><input id='plugin_text_string' name='appcache_plugin[url4]' size='40' type='text' value='{$options['url4']}' />
			<b>&nbsp;disable dynamic filecaching: &nbsp;</b>
				<input type='checkbox' id='plugin_text_string' name='appcache_plugin[ddf4]'"; if($options['ddf4'] == 'true'){ echo' checked ';} echo"value='true'>
			<br><br>";
			echo "<h2>additional information</h2>
					<p>
						If you want to debug the Application cache try the following:<br>
						Use Google Chrome: CMD+ALT+J for debugger tool; go to the \"Console\"-Tab you can see the events and files triggered by the Application Cache.
						you can see the Cache Manifest at <code>http://www.yoururl.com/wp-content/plugins/appcache/manifest.appcache.php?appcache_id=ID-OF-YOUR-PAGE-OR-ARTICLE&referrer=%2F</code>
						This is where you can see all the Files and directories that are cached. Be sure that all images exist. The \"Cache Progress event\" gets cancelled if ONLY ONE FILE is not Found (http error 404).
						Type in the Addressbar \"chrome://appcache-internals/\" to see the currently Cached websites.
					</p>
			";
		}
		function plugin_options_validate($input) {
			$options = get_option('appcache_plugin');
			$options['url1'] = $input['url1'];
			$options['url2'] = $input['url2'];
			$options['url3'] = $input['url3'];
			$options['url4'] = $input['url4'];
			$options['ddf1'] = $input['ddf1'];
			$options['ddf2'] = $input['ddf2'];
			$options['ddf3'] = $input['ddf3'];
			$options['ddf4'] = $input['ddf4'];
			$options['theme_dynamic_caching'] = $input['theme_dynamic_caching'];
			$options['ignored_files'] = $input['ignored_files'];
			$options['customfield'] = $input['customfield'];
		return $options;
		}
	}else{
		header("Content-type: text/html; charset=windows-1252");
		?>
		
		<!DOCTYPE html>
		<html>
			<head>
				<title>Application cache plugin</title>
				<link rel="stylesheet" href="style.css">
			</head>
			<body>
				<div class="well">
					<h1>Hello, world!</h1>
					<p>If you're looking for this application cache plugin visit: <a href="#" class="fwd">wordpress plugins</a>.</p>
					<a href="http://www.fwd.io/" class="fwd">fwd.io</a>
				</div>
			</body>
			
		</html>
		
		<?php
	}
		
}else{
	global $run_dir;	
	$run_dir = array();
	/****************************************************
	*	HERE IS THE CACHE MANIFEST!
	****************************************************/
	include_once('functions.php');
	$appcache_id = $_GET['appcache_id'];
	global $wpdb;
	$appcache_plugin_options = get_option('appcache_plugin');
	if($appcache_plugin_options === false){
		$appcache_plugin_options = array('customfield'=>'true');
	}
	$template = $wpdb->get_results("SELECT option_value 
									FROM wp_options 
									WHERE option_name='template'");
	$dbhash = $wpdb->get_results("
				SELECT option_value 
				FROM wp_options
				WHERE option_name='appcacheupdatehash$appcache_id'
			");
	header("Content-Type: text/cache-manifest");
	echo "CACHE MANIFEST\n";
	if($dbhash[0]->option_value != ''){
		echo "# version: ".$dbhash[0]->option_value."\n";
	}else{
		echo '# version: v1.0';
	}
	//echo "# timestamp: ".time()."\n";
	echo "\nCACHE:\n";
	/*********************************************
	*	CACHE POST-SPECIFIC IMAGES	FROM postmeta
	*********************************************/
	echo "#cache id @ wp_postmeta\n";
	$postimg = $wpdb->get_results("	SELECT meta_value 
									FROM wp_postmeta
									WHERE post_id=$appcache_id
								 ");
	foreach($postimg as $content){
		if($content->meta_value != ''){
			$doc = new DOMDocument();
			$doc->strictErrorChecking = false;
			// $doc throws an error, cause html is not well-formed
			@$doc->loadHTML($content->meta_value);
			$imageTags = $doc->getElementsByTagName('img');
			
			foreach($imageTags as $tag){
				echo $tag->getAttribute('src')."\n";
			}

		}else{
			echo "#none.\n";
		}
	}
	/*********************************************
	*	CACHE POST-SPECIFIC IMAGES	FROM wp_posts
	*********************************************/
	echo "#cache id @ wp_posts\n";
	$postimg = $wpdb->get_results("SELECT post_content 
									FROM wp_posts 
									WHERE ID=$appcache_id
								 ");
	foreach($postimg as $content){
		if($content->post_content != ''){
			$doc = new DOMDocument();
			$doc->strictErrorChecking = false;
			// $doc throws an error, cause html is not well-formed
			@$doc->loadHTML($content->post_content);
			$imageTags = $doc->getElementsByTagName('img');
			
			foreach($imageTags as $tag){
				echo $tag->getAttribute('src')."\n";
			}
		}else{
			echo "#none.\n";
		}
	}
	/*********************************************
	*	PARSE TEXTWIDGETS FOR IMAGE TAGS
	*********************************************/
	echo "#cache widgets @ wp_options\n";
	$textwidget = $wpdb->get_results("SELECT option_value FROM wp_options
									  WHERE option_name LIKE '%widget%' AND autoload='yes'");
	foreach($textwidget as $content){
		if($content->option_value != ''){
			$doc = new DOMDocument();
			$doc->strictErrorChecking = false;
			// $doc throws an error, cause html is not well-formed
			@$doc->loadHTML($content->option_value);
			$imageTags = $doc->getElementsByTagName('img');
			foreach($imageTags as $tag){
				echo $tag->getAttribute('src')."\n";
			}
		}else{
			echo "#none.\n";
		}
	}
	/*********************************************
	*	PARSE wp_postmeta for customfields
	*********************************************/
	echo "#cache customfields: ".$appcache_plugin_options['customfield']."\n";
	if($appcache_plugin_options['customfield'] == 'true'){
		$customfields = $wpdb->get_results("SELECT meta_value 
											FROM wp_postmeta
											WHERE post_id=$appcache_id
										  ");
		//check if meta_value is INT
		$matches = array();
		foreach($customfields as $field){
			if(!preg_match('/[a-zA-Z:,;"{}\/]+/', $field->meta_value)){
				if(preg_match('/[0-9]{1,7}/', $field->meta_value)){
					$matches[] = $field->meta_value;
				}
			}
		}
		// lookup the values in the database
		
		foreach($matches as $content){
			if($content != ''){
				$guid = $wpdb->get_results("
								SELECT guid
								FROM wp_posts
								WHERE id=$content
								AND post_type='attachment'
								AND (
										post_mime_type='image/png'
										OR
										post_mime_type='image/jpeg'
										OR
										post_mime_type='image/gif'
									)
								");
				foreach($guid as $url){
					$result[] = $url->guid;
					echo $url->guid."\n";
				}
			}
		}
		if(empty($result)){echo "#no matches for this post.\n";}
	}
	/*********************************************
	*	PARSE FRONTPAGE POSTS FOR IMAGE TAGS
	*********************************************/
	echo "#frontpage posts\n";
	if($_GET['referrer'] == '/'){
		$entries_count = $wpdb->get_results("	SELECT option_value 
												FROM wp_options
												WHERE option_name='posts_per_page' 
												AND autoload='yes'");
		$count = $entries_count[0]->option_value;
		$posts = $wpdb->get_results("
									SELECT post_content 
									FROM wp_posts 
									WHERE post_type='post' 
									AND post_parent=0 
									AND post_status='publish' 
									ORDER BY post_date DESC
									LIMIT 0, $count
									");
		if(!empty($posts)){
			foreach($posts as $content){
				//parse html for IMG tag and echo out SRC
				$doc = new DOMDocument();
				$doc->loadHTML($content->post_content);
				$imageTags = $doc->getElementsByTagName('img');
				foreach($imageTags as $tag){
					echo $tag->getAttribute('src')."\n";
				}
			}
		}else{
			echo "#none.\n";
		}
	}else{
		echo "#not for this page!\n";
	}
	
	/**************************
	**** CACHE THEME DIRECTORY
	**************************/		
	echo "\n#Cached Folders:\n";
	
	$theme = get_bloginfo('stylesheet_directory');
	$slash = strrpos($theme,"/");
	$themename = substr($theme, $slash);
	$themename = str_replace("/", "", $themename);
	run_dir("../../themes/".$themename."/", 'php', $appcache_plugin_options['theme_dynamic_caching'], 		$appcache_plugin_options['ignored_files']);
	
	/**************************
	**** CACHE CUSTOM FOLDERS
	**************************/	
	if($appcache_plugin_options !== false){
		foreach($appcache_plugin_options as $key=>$folder){
			if(substr($key, 0, 3) == 'url' && !empty($folder)){					
				//check first char
				$fc = substr($folder,0,1);
				if($fc != '/'){
					$folder = '/'.$folder;
				}
				//check last char
				$lc = substr($folder, -1, 1);
				if($lc != '/'){
					$folder = $folder.'/';
				}
				$id = substr($key, -1, 1);
				$option = $appcache_plugin_options['ddf'.$id];
				if(empty($option)){
					$option == FALSE;
				}
				echo "#".$folder."\n";
				run_dir('../../..'.$folder, '.php', $option, $appcache_plugin_options['ignored_files']);
			}else{
				continue;
			}	
		}
	}
	foreach($run_dir['cache'] as $key=>$value){
		if(file_exists($value)){
			echo $value."\n";
		}
	}
	echo "\nNETWORK:\n*\n";
}
?>