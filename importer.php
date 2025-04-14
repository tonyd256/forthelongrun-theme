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

require_once CHILD_DIR . '/Podcast_Importer.php';
$ftlrp_importer;

add_action( 'admin_menu', 'ftlrp_importer_admin_menu_register' );

function ftlrp_importer_admin_menu_register() {
  // add_submenu_page( get_template_directory() . '/inc/core-setup/custom-types/podcast/podcast-type.php', 'Podcast Importer', 'Podcast Importer', 'manage_options', 'import-podcast', 'importer_admin_menu_render' );
  $menu = add_menu_page( 'Podcast Importer', 'Podcast Importer', 'manage_options', 'import-podcast', 'ftlrp_importer_admin_menu_render' );
  add_action( 'load-'.$menu, 'ftlrp_load_scripts' );
}

function ftlrp_load_scripts() {
  add_action( 'admin_enqueue_scripts', 'ftlrp_enqueue_scripts' );
}

function ftlrp_enqueue_scripts() {
  wp_enqueue_script( 'importer-script', get_stylesheet_directory_uri() . '/js/importer.js', array( 'jquery' ), null, true );

  wp_localize_script( 'importer-script', 'settings', array(
    'ajaxurl'    => admin_url( 'admin-ajax.php' ),
    'send_label' => __( 'Import Podcast', 'importpodcast' )
  ) );
}

function ftlrp_importer_admin_menu_render() {
  global $title;

  print '<div class="wrap">';
  print "<h1>$title</h1>";
  submit_button( 'Import New Episodes' );
  print "<ul id='info'></ul>";

  print '</div>';
}

add_action('wp_ajax_import_podcast', 'ftlrp_import_podcast');
add_action('import_podcast_cron_hook', 'ftlrp_import_podcast');
function ftlrp_import_podcast() {
  if (is_null($ftlrp_importer)) {
    $ftlrp_importer = new Podcast_Importer();
  }
  $ftlrp_importer->run_action();

  exit();
}

add_action('wp_async_import_podcast', 'ftlrp_fetch_and_import');
function ftlrp_fetch_and_import() {
  error_log("Starting podcast import...");
  $rss = fetch_feed("https://anchor.fm/s/81032c4/podcast/rss");

  if ( ! is_wp_error( $rss ) ) {
    $qtn = $rss->get_item_quantity();

    for ($i = 0; $i < 5; $i++) {
      $item = $rss->get_item(4 - $i);

      $ep = array(
        "title" => $item->get_title(),
        "description" => $item->get_description(),
        "date" => $item->get_date('Y-m-d H:i:s'),
        "number" => $qtn - (4 - $i),
        "source" => $item->get_enclosure()->get_link(),
        "image" => array_values($item->get_item_tags("http://www.itunes.com/dtds/podcast-1.0.dtd", "image")[0]["attribs"])[0]["href"],
        "duration" => $item->get_item_tags("http://www.itunes.com/dtds/podcast-1.0.dtd", "duration")[0]["data"], //)[0]["href"],
        "link" => $item->get_link(),
      );
      // error_log(print_r($ep, true));
      ftlrp_import_episode($ep);
    }
  }

  error_log("Ending podcast import.");
  exit();
}

function ftlrp_import_episode($episode) {
  if ( ! function_exists( 'post_exists' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/post.php' );
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
  }

  // get post to see if it already exists
  if (post_exists(wp_encode_emoji(wp_strip_all_tags($episode['title'])), '', '', 'podcast', 'publish')) {
    return;
  }

  $post_id = wp_insert_post(array(
    'post_title' => wp_encode_emoji(wp_strip_all_tags($episode['title'])),
    'post_author' => '261764034',
    'post_content' => wp_encode_emoji($episode['description']),
    'post_date' => $episode['date'],
    'post_type' => 'podcast',
    'post_status' => 'publish',
    'meta_input' => array(
      '_podcast_ep' => $episode['number'] . "",
      '_podcast_date' => $episode['date'],
      '_podcast_resourceurl' => $episode['source'],
      'enclosure' => $episode['source'],
      '_podcast_artist' => 'jlevitt815',
      '_podcast_link' => $episode['link'],
      '_podcast_duration' => $episode['duration']
    )
  ));

  if (! empty($post_id) && ! is_wp_error($post_id)) {
    ftlrp_import_featured_image_from_url($post_id, $episode['image']);
  }

  error_log($post_id);
}

// copied from: https://gist.github.com/gkarthikeyanmca/35cd2481e63de7b8e00cb185d61d01e5
function ftlrp_import_featured_image_from_url( $post_id = '', $image_url ='' ) {
  //Check both post_id and image_url is not empty
  if($post_id == '' || $image_url == '') {
    return;
  }

  //Get the post title of the post_id
  $title = get_post_field( 'post_title', $post_id ) . " thumbnail";

  //Handle Upload - Refer https://developer.wordpress.org/reference/functions/media_sideload_image/
  $attach_id = media_sideload_image($image_url, $post_id, null, 'id');

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
