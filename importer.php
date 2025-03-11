<?php

// Pull latest rss feed from url
// For each, check to see if the title exists in posts
// if it does skip, if not import it
// use ffmpeg to download m4a file and convert to aac, add to posts as
// "podcast?"
// add that as _podcast_url in post meta
// download image to media files
// calculate episode number
//
//
add_action( 'admin_menu', 'importer_admin_menu_register' );

function importer_admin_menu_register() {
  // add_submenu_page( get_template_directory() . '/inc/core-setup/custom-types/podcast/podcast-type.php', 'Podcast Importer', 'Podcast Importer', 'manage_options', 'import-podcast', 'importer_admin_menu_render' );
  $menu = add_menu_page( 'Podcast Importer', 'Podcast Importer', 'manage_options', 'import-podcast', 'importer_admin_menu_render' );
  add_action( 'load-'.$menu, 'load_scripts' );
}

function load_scripts() {
  add_action( 'admin_enqueue_scripts', 'enqueue_scripts' );
}

function enqueue_scripts() {
  wp_enqueue_script( 'importer-script', get_stylesheet_directory_uri() . '/js/importer.js', array( 'jquery' ), null, true );

  wp_localize_script( 'importer-script', 'settings', array(
    'ajaxurl'    => admin_url( 'admin-ajax.php' ),
    'send_label' => __( 'Import Podcast', 'importpodcast' )
  ) );
}

function importer_admin_menu_render() {
  global $title;

  print '<div class="wrap">';
  print "<h1>$title</h1>";
  submit_button( 'Import New Episodes' );
  print "<ul id='info'></ul>";

  print '</div>';
}

add_action('wp_ajax_import_podcast', 'import_podcast');
function import_podcast() {
  // import podcast.
  // get post to see if it already exists
  if (post_exists(wp_encode_emoji(wp_strip_all_tags($_POST['title'])), '', '', 'podcast', 'published')) {
    echo "0";
    exit();
  }

  $date = DateTime::createFromFormat('D, d M Y G:i:s T', $_POST['pubDate']);

  $post_id = wp_insert_post(array(
    'post_title' => wp_encode_emoji(wp_strip_all_tags($_POST['title'])),
    'post_author' => '261764034',
    'post_content' => wp_encode_emoji($_POST['description']),
    'post_date' => $date->format('Y-m-d H:i:s'),
    'post_type' => 'podcast',
    'post_status' => 'publish',
    'meta_input' => array(
      '_podcast_ep' => $_POST['number'] . "",
      '_podcast_date' => $date->format('Y-m-d'),
      '_podcast_resourceurl' => $_POST['source'],
      'enclosure' => $_POST['source'],
      '_podcast_artist' => 'jlevitt815',
      '_podcast_link' => $_POST['link'],
      '_podcast_duration' => $_POST['duration']
    )
  ));

  import_featured_image_from_url($post_id, $_POST['image']);

  echo $post_id;
  exit();
}

// copied from: https://gist.github.com/gkarthikeyanmca/35cd2481e63de7b8e00cb185d61d01e5
function import_featured_image_from_url( $post_id = '', $image_url ='' ) {
  //Check both post_id and image_url is not empty
  if($post_id == '' || $image_url == '') {
    return;
  }

  //Get the post title of the post_id
  $title = get_post_field( 'post_title', $post_id ) . " thumbnail";

  //Handle Upload - Refer https://developer.wordpress.org/reference/functions/media_sideload_image/
  $attach_id = media_sideload_image($image_url, $post_id, null, 'id');

  // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
  require_once( ABSPATH . 'wp-admin/includes/image.php' );

  // Generate the metadata for the attachment, and update the database record.
  $attach_data = wp_generate_attachment_metadata( $attach_id, get_attached_file( $attach_id ) );
  wp_update_attachment_metadata( $attach_id, $attach_data );

  //set ALT text
  update_post_meta($attach_id, '_wp_attachment_image_alt', $title);

  //Set default image title with post title
  $args=array(
    'ID' => $attach_id,
    'post_title' => $title
  );
  wp_update_post($args);

  //assign uploaded image as featured image for the post_id
  set_post_thumbnail( $post_id, $attach_id );
}
