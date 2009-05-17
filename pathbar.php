<?php
/*
Plugin Name: Pathbar
Plugin URI:
Description: This plugin adds the possiblity to show the path to a static page.
Version: 1.2
Author: Jan Gosmann
Author URI: http://www.hyper-world.de
*/

//------------------------------------------------------------------------------
// Outputs the pathbar. The path will be recognized automatically, if $elements
// is empty. Otherwise $elements has to be an array with each Element of the
// path without the root. $elements[i]["url"] has to be the URL, and 
// $elements[i]["title"] the title.
//------------------------------------------------------------------------------
function the_pathbar( $elements = '' )
{
	global $wpdb, $wp_the_query, $post;

  $blog_link = '<a href="' . get_settings( 'siteurl' ) . '">'
			. get_option( 'pathbar_home' ) . '</a>';
	$divider = get_option( 'pathbar_divider' );

  if( $elements == '' ) {
    if( $wp_the_query->post_count == 1 ) { // Check if only one post/page is shown.
      $path = apply_filters( 'the_title', $wp_the_query->posts[0]->post_title );
      if( $wp_the_query->posts[0]->post_type == 'page' ) {
        $parent = $wp_the_query->posts[0];
        while( $parent->post_parent != 0 ) {
          $parent = get_page( $parent->post_parent );
          if( $parent->ID == 178 || $parent->ID == 182 ) continue;
          $title = apply_filters( 'the_title', $parent->post_title );
          $path = '<a href="' . get_page_link( $parent->ID ) . '">'
              . $title . '</a>' . $divider . $path;
        }
       $path = $blog_link . $divider . $path;
      }
      else {
        if( get_option( 'pathbar_postdate' ) == "pathbar_postdate" )
          $path = $blog_link . $divider . "<a href=\"" . get_year_link( get_the_time( 'Y' ) ) . "\">" . get_the_time( 'Y' ) . "</a>" . $divider . "<a href=\"" . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . "\">" . get_the_time( 'F' ) . "</a>" . $divider . $path;
        else
	  $path = $blog_link . $divider . $path;
      }
    }
    else {
      if( is_category() ) {
        $category = get_category( get_query_var( 'cat' ) );
        $path = $category->cat_name;
        while( $category->category_parent != 0 ) {
          $category = get_category( $category->category_parent );
          $path = '<a href="' . get_category_link( $category->cat_ID ) . '">'
              . $category->cat_name . '</a>' . $divider . $path;
        }
        $path = $blog_link . $divider . $path;
      }
      else if( is_search() ) {
        $path = $blog_link . $divider . 'Search';
      }
      else if( is_archive() ) {
        if( is_day() ) {
          $path = '</a>' . $divider . get_the_time( 'l' ) . ',&nbsp;the&nbsp;'
              . get_the_time( 'j.' );
        }
  
        if( is_day() || is_month() )
          $path = get_the_time( 'F' ) . $path;
        if( is_day() )
          $path = '<a href="' . get_month_link( get_the_time( 'Y' ),
              get_the_time( 'm' ) ) . '">' . $path;
        if( is_day() || is_month() )
          $path = '</a>' . $divider . $path;
  
        if( is_day() || is_month() || is_year() )
          $path = get_the_time( 'Y' ) . $path;
        if( is_day() || is_month() )
          $path = '<a href="' . get_year_link( get_the_time( 'Y' ) ) . '">'
              . $path;
        if( is_day() || is_month() || is_year() )
          $path = $divider . $path;
  
        $path = $blog_link . $path;
      }
      else
        $path = get_option( 'pathbar_home' );
    }
  }
  else {
    $path = $blog_link;
    for( $i = 0; $i < sizeof( $elements ); $i++ ) {
      $path .= $divider;
      if( $elements[$i]['url'] != '' )
        $path .= '<a href="' . $elements[$i]['url'] . '">';
      $path .= $elements[$i]['title'];
      if( $elements[$i]['url'] != '' )
        $path .= '</a>';
    }
  }

	echo $path;
}

//------------------------------------------------------------------------------
// Installs the pathbar plugin.
//------------------------------------------------------------------------------
function pathbar_install()
{
	add_option( 'pathbar_home', get_bloginfo( 'name' ),
			'This is the text for the pathbar link to the home or main blog site.' );
	add_option( 'pathbar_divider', '&nbsp;&gt;&nbsp;',
			'String which divides the links in the pathbar.' );
	add_option( 'pathbar_postdate', 'pathbar_postdate',
	                'Set to pathbar_postdate to display links to the year and moth archive if a post is displayed.' );
}

//------------------------------------------------------------------------------
// Provides a configuration menu for this plugin.
//------------------------------------------------------------------------------
function pathbar_confmenu()
{
  add_options_page( 'Pathbar', 'Pathbar', 'switch_themes', 'pathbar.php',
			'pathbar_confpage' );
}

//------------------------------------------------------------------------------
// Display the configuration page.
//------------------------------------------------------------------------------
function pathbar_confpage()
{
	if( $_POST['pathbar_update_options'] == 'Save' ) {
		update_option( 'pathbar_home', $_POST['pathbar_home'] );
		update_option( 'pathbar_divider', $_POST['pathbar_divider'] );
		update_option( 'pathbar_postdate', $_POST['pathbar_postdate'] );
	}

	$home = get_option( 'pathbar_home' );
	$home = str_replace( '&', '&amp;', $home);
	$divider = get_option( 'pathbar_divider' );
	$divider = str_replace( '&', '&amp;', $divider );
	$postdate = get_option( 'pathbar_postdate' );

	?>
		<div class="wrap" id="pathbar_confpage">
			<h2>Pathbar</h2>
			<form method="post">
				Home title: <input type="text" name="pathbar_home" value="<?php echo $home; ?>" /><br />
				Divider: <input type="text" name="pathbar_divider" value="<?php echo $divider; ?>" /><br />
				<input type="checkbox" name="pathbar_postdate" value="pathbar_postdate" <?php echo ($postdate == "pathbar_postdate") ? "checked=\"checked\"" : ""; ?> /> Show link to year and month archives when displaying a post.<br />
				<p class="submit">
					<input type="submit" name="pathbar_update_options" value="Save"/>
				</p>
			</form>
		</div>
	<?php
}

//------------------------------------------------------------------------------
// Hooks
//------------------------------------------------------------------------------
add_action( 'activate_pathbar.php' , 'pathbar_install' );
add_action( 'admin_menu', 'pathbar_confmenu' );

?>
