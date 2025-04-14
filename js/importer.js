( function( $ ) {
  $( document ).ready( function() {
    $( '#submit' ).on( 'click', function( event ) {
      event.preventDefault();
      jQuery.post(settings.ajaxurl, { action: 'import_podcast' });
    });
  });
})( jQuery );
