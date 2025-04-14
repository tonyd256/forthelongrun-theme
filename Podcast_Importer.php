<?php

class Podcast_Importer extends WP_Async_Task {
  /**
   * @var string
   */
  // protected $prefix = 'FTLR_';

  /**
   * @var string
   */
  protected $action = 'import_podcast';


  protected function prepare_data( $data ) {}

  /**
   * Handle a dispatched request.
   *
   * Override this method to perform any actions required
   * during the async request.
   */
  function run_action() {
    do_action("wp_async_$this->action");
  }

}
