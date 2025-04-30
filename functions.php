<?php
/**
 * @package WordPress
 * @subpackage wpcasterpro
 */

if ( ! defined( 'ABSPATH' ) ) { die( '-1' ); }

define( 'CHILD_DIR', get_stylesheet_directory() );

require_once __DIR__ . '/lib/wp-async-task.php';
require_once CHILD_DIR . '/importer.php';
require_once( __DIR__ . '/widgets.php' );

function my_theme_enqueue_styles() {
  $parent_style = 'wpcasterpro-style';
  wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
  wp_enqueue_style( 'child-style',
    get_stylesheet_directory_uri() . '/style.css',
    array( $parent_style ),
    wp_get_theme()->get('Version')
  );
}

add_filter( 'theme_mod_breadcrumb_on', function ($name) { return 0; } );

add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
add_action('init', 'add_podcast_meta');  

function add_podcast_meta() {
  $podcast_tab_custom = array(
    array(
      'label' => esc_html__( 'Duration', "wpcasterpro" ),
      'id'    => '_podcast_duration',
      'type'  => 'text'
    ),
    array(
      'label' => esc_html__( 'External link', "wpcasterpro" ),
      'id' 	=> '_podcast_link',
      'type' 	=> 'url',
    )
  );
  $podcast_tab_custom_box = new Custom_Add_Meta_Box( 'podcast_meta_custom', 'Podcast extra details', $podcast_tab_custom, 'podcast', true );
}

function wp1482371_custom_post_type_args( $args, $post_type ) {
  if ( $post_type == "podcast" ) {
    $args['rewrite'] = array_merge($args['rewrite'], array(
      'slug' => 'episodes',
      'with_front' => false
    ));
  } elseif ( $post_type == "post" ) {
    $args['rewrite'] = array(
      'slug' => 'blog',
      'with_front' => false
    );
  }

  return $args;
}

function wp1482371_custom_taxonomy_args( $args, $taxonomy ) {
  if ( $taxonomy == "podcast-category" ) {
    $args['rewrite'] = array_merge($args['rewrite'], array(
      'slug' => 'episode-categories',
      'with_front' => false
    ));
  }

  return $args;
}

add_filter( 'register_post_type_args', 'wp1482371_custom_post_type_args', 20, 2 );
add_filter( 'register_taxonomy_args', 'wp1482371_custom_taxonomy_args', 20, 2 );

function rewrite_blog_post_url($permalink, $post) {
  $new_link = str_replace(get_home_url().'/', get_home_url().'/blog/', $permalink);
  return $new_link;
}

add_filter( 'post_link', 'rewrite_blog_post_url', 10, 2);
