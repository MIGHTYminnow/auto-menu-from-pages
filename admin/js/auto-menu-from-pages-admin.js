(function( $ ) {
	'use strict';

	$( document ).ready( function() {

		/**
		 * Admin bar JS.
		 */
		$( '#wp-admin-bar-sync_auto_menu' ).on( 'click', function( e ) {

			// Prevent default link action.
			e.preventDefault();

			var $link, $icon, data;

			// Get link element and add appropriate class, plus remove focus.
			$link = $( this ).find( 'a' )
				.addClass( 'syncing' )
				.blur();

			data = {
				'action': 'sync_auto_menu',
			};

			$.post(
				ajaxurl,
				data,
				function( response ) {
					
					// Add/remove success classes.
					$link.removeClass( 'syncing' ).addClass( 'success' ).delay( 2000 ).queue(function(){
						$(this).removeClass( 'success' ).dequeue();
					});

					// Refresh front-end pages to show menu change.
					if ( ! $( 'body' ).hasClass( 'wp-admin' ) ) {
						location.reload();
					}

				}
			);

		});

		/**
		 * Menu screen JS.
		 *
		 * Note: amfpVars variable is passed via wp_localize_scipt() for the
		 * menu screen only.
		 */
		if ( 'undefined' != typeof amfpVars ) {

			// Add new heading and instructions/message.

			$( 'body.auto-menu-active #post-body-content' ).prepend( '<div class="custom-menu-message"></div>' );
			$( 'body.auto-menu-active .custom-menu-message' ).append( '<h3>' + amfpVars.menu_title + '</h3>' );
			$( 'body.auto-menu-active .custom-menu-message' ).append( '<p class="menu-note">' + amfpVars.menu_desc_text + '</p>' );

		}

	});

})( jQuery );
