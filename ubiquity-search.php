<?php
/*
Plugin Name: Ubiquity-Blog-Search
Plugin URI: http://notizblog.org/projects/ubiquity-search-for-wordpress/
Description: A WordPress-Search-Plugin for the Ubiquity.
Version: 0.2
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

// register
if (isset($wp_version)) {
  add_filter('query_vars', array('UbiquitySearch', 'queryVars'));
  add_action('parse_query', array('UbiquitySearch', 'parseQuery'));
  add_action('init', array('UbiquitySearch', 'init'));
  
  add_action('wp_head', array('UbiquitySearch', 'metaTags'), 5);
}

/**
 * UbiquitySearch Class
 * 
 */
class UbiquitySearch {
  
  function init() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }

  /**
   * add 'ubiquity' as a valid query variables.
   */
  function queryVars($vars) {
    $vars[] = 'ubiquity';

    return $vars;
  }
  
  /**
   * Insert the meta tags
   */
  function metaTags() {
    global $wp_rewrite;

    if (is_home()) {
      echo '<link rel="commands" title="Search '.htmlentities(get_bloginfo('name')).' Weblog" href="'.get_option('home').'/index.php?ubiquity=search" />' . "\n";
    }
  }
  
  /**
   * parse query
   */
  function parseQuery() {
    global $wp_query, $wp_version;
    
    $var = $wp_query->query_vars['ubiquity'];
    
    if( isset( $var )) {
      if ($var == 'search') {
        UbiquitySearch::javaScriptCode();
      }
    }
  }

  /**
   * print JavaScript code
   */
  function javaScriptCode() {
    global $wp_query, $wp_version;
    $splittedMail = split('@', get_bloginfo('admin_email'));

    header('Content-type: application/x-javascript');
?>
makeSearchCommand({
  icon: "<?php bloginfo('url'); ?>/favicon.ico",
  name: "search-<?php echo sanitize_title(get_bloginfo('name')); ?>",
  author: {name: "<?php echo $splittedMail[0]; ?>", email: "<?php bloginfo('admin_email'); ?>"},
  description: "Searches the '<?php bloginfo('name'); ?>' weblog",
  url:"<?php bloginfo('wpurl'); ?>/?s={QUERY}",
  preview: function( pblock ) {
    pblock.innerHTML = CmdUtils.renderTemplate("Search the <em><a href='<?php bloginfo('url'); ?>'><?php bloginfo('name'); ?></a></em> weblog");
  }
});
<?php
    exit;
  }
}
?>