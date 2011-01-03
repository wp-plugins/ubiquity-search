<?php
/*
Plugin Name: Ubiquity-Blog-Search
Plugin URI: http://notizblog.org/projects/ubiquity-search-for-wordpress/
Description: A WordPress-Search-Plugin for the Ubiquity.
Version: 1.0.2
Author: Matthias Pfefferle
Author URI: http://notizblog.org/
*/

// register
if (isset($wp_version)) {
  add_filter('query_vars', array('UbiquitySearch', 'queryVars'));
  add_action('parse_query', array('UbiquitySearch', 'parseQuery'));
  add_action('init', array('UbiquitySearch', 'init'));
  
  add_action('wp_head', array('UbiquitySearch', 'metaTags'), 5);
  
  // json feed
  add_action('do_feed_ubiquity', array('UbiquitySearch', 'json_feed'));
}

/**
 * UbiquitySearch Class
 * 
 * @author Matthias Pfefferle
 */
class UbiquitySearch {
  
  function init() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }

  /**
   * add 'ubiquity' as a valid query variables.
   *
   * @param array $vars
   * @return array
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
   * json output
   */
  function json_feed() {
    $output = array();
    while (have_posts()) {
      the_post();
      $output[] = array('title' => get_the_title(),
                        'excerpt' => get_the_excerpt(),
                        'date' => get_the_time('F j, Y H:i'),
                        'link' => get_permalink());
    }

    header('Content-Type: application/json; charset=' . get_option('blog_charset'), true);
    echo json_encode($output);
  }

  /**
   * print JavaScript code
   */
  function javaScriptCode() {
    global $wp_query, $wp_version;
    $splittedMail = split('@', get_bloginfo('admin_email'));

    header('Content-type: application/x-javascript');
?>
CmdUtils.makeSearchCommand({
  icon: "<?php bloginfo('url'); ?>/favicon.ico",
  url: "<?php bloginfo('wpurl'); ?>",
  names: ["<?php echo sanitize_title(get_bloginfo('name')); ?> search",
          "search <?php echo sanitize_title(get_bloginfo('name')); ?>"],
  author: {name: "<?php echo $splittedMail[0]; ?>", email: "<?php bloginfo('admin_email'); ?>"},
  description: "'<?php bloginfo('name'); ?>' blog-search",
  help: "What do you want to find on <em><?php bloginfo('name') ?></em>. Mark a word in the text or type in the term you want to search for.",
  url: "<?php bloginfo('wpurl'); ?>/?s={QUERY}",
  
  // preview output
  preview : function(previewBlock, {object}){ 
    if (object.text == "") {
      previewBlock.innerHTML = this.help;
    } else {
  
      previewBlock.innerHTML = _("Searching for posts on notizblog...");
        
      // preview command
      CmdUtils.previewAjax(previewBlock, {
        type: "GET",
        url: "<?php bloginfo('wpurl'); ?>/",
        data: {
          feed: "ubiquity",
          s: object.text
        },
        dataType: "json",
        error: function() {
          previewBlock.innerHTML = "<p class='error'>"+_("Error searching notizblog")+"</p>";
        },
        success: function(responseData) {
          var htmlTemplate = "Searchresults for '<em>"+object.text+"</em>': <ul>";
          
          for (var i = 0; i < responseData.length; ++i) {
            // html output
            htmlTemplate += "<li><a href='"+responseData[i].link+"'>";
            htmlTemplate += responseData[i].title.replace(new RegExp(object.text,"i"), "<span style='background-color: red;'>"+object.text+"</span>");
            htmlTemplate += "</a></li>"; 
          }
    
          previewBlock.innerHTML = htmlTemplate+"</ul>";       
        }
      });
    }
  }
});
<?php
    exit;
  }
}
?>