<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

function register_sponsors_widget( $widgets_manager ) {
  require_once( __DIR__ . '/widgets/sponsors-widget.php' );

  $widgets_manager->register( new \Elementor_Sponsors_Widget() );
}

add_action( 'elementor/widgets/register', 'register_sponsors_widget' );

function elementor_widgets_dependencies() {
  /* Scripts */

  /* Styles */
  wp_enqueue_style( 'sponsors-widget-style', get_stylesheet_directory_uri() . '/assets/css/sponsors-widget-style.css' );
}

add_action( 'wp_enqueue_scripts', 'elementor_widgets_dependencies' );
