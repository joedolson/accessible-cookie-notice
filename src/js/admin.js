( function( $ ) {

	// ready event
	$( function() {
		// initialize color picker.
		$( '.cn_color' ).wpColorPicker();

		// refuse option
		$( '#cn_refuse_opt' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) )
				$( '#cn_refuse_opt_container' ).slideDown( 'fast' );
			else
				$( '#cn_refuse_opt_container' ).slideUp( 'fast' );
		} );
		
		// revoke option
		$( '#cn_revoke_cookies' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) )
				$( '#cn_revoke_opt_container' ).slideDown( 'fast' );
			else
				$( '#cn_revoke_opt_container' ).slideUp( 'fast' );
		} );

		// privacy policy option
		$( '#cn_see_more' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) )
				$( '#cn_see_more_opt' ).slideDown( 'fast' );
			else
				$( '#cn_see_more_opt' ).slideUp( 'fast' );
		} );

		// on scroll option
		$( '#cn_on_scroll' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) )
				$( '#cn_on_scroll_offset' ).slideDown( 'fast' );
			else
				$( '#cn_on_scroll_offset' ).slideUp( 'fast' );
		} );

		// privacy policy link
		$( '#cn_see_more_link-custom, #cn_see_more_link-page' ).on( 'change', function() {
			if ( $( '#cn_see_more_link-custom:checked' ).val() === 'custom' ) {
				$( '#cn_see_more_opt_page' ).slideUp( 'fast', function() {
					$( '#cn_see_more_opt_link' ).slideDown( 'fast' );
				} );
			} else if ( $( '#cn_see_more_link-page:checked' ).val() === 'page' ) {
				$( '#cn_see_more_opt_link' ).slideUp( 'fast', function() {
					$( '#cn_see_more_opt_page' ).slideDown( 'fast' );
				} );
			}
		} );

		var firstItem = window.location.hash;
		if ( firstItem ) {
			showPanel( firstItem );
		} else {
			firstItem = $( '.nav-tab-wrapper' ).attr( 'data-default' );
			if ( 'undefined' !== typeof( firstItem ) ) {
				showPanel( firstItem );
			}
		}
		var tabs = document.querySelectorAll('.nav-tab-wrapper [role=tab]'); //get all role=tab elements as a variable
		for (i = 0; i < tabs.length; i++) {
			tabs[i].addEventListener('click', showTabPanel);
		} //add click event to each tab to run the showTabPanel function
		/**
		 * Activate a panel from the click event.
		 *
		 * @param event Click event.
		 */
		function showTabPanel(e) {
			var tabs2 = document.querySelectorAll('.nav-tab-wrapper [role=tab]'); //get tabs
			for (i = 0; i < tabs2.length; i++) {
				tabs2[i].setAttribute('aria-selected', 'false');
				tabs2[i].setAttribute('style', 'font-weight:normal');
				tabs2[i].classList.remove( 'nav-tab-active' );
			} // reset all tabs to aria-selected=false and normal font weight
			e.target.setAttribute('aria-selected', 'true'); //set aria-selected=true for clicked tab
			e.target.classList.add( 'nav-tab-active' );
			var tabPanelToOpen = e.target.getAttribute('aria-controls');
			var tabPanels = document.querySelectorAll('[role=tabpanel]'); //get all tabpanels
			for (i = 0; i < tabPanels.length; i++) {
				tabPanels[i].style.display = "none";
			} // hide all tabpanels
			window.location.hash = tabPanelToOpen;
			document.getElementById(tabPanelToOpen).style.display = "block"; //show tabpanel
		}

		/**
		 * Activate a panel from panel ID.
		 *
		 * @param string hash Item ID.
		 */
		function showPanel(hash) {
			var id = hash.replace( '#', '' );
			var control = $( 'button[aria-controls=' + id + ']' );
			var tabs2 = document.querySelectorAll('.nav-tab-wrapper [role=tab]'); //get tabs
			for (i = 0; i < tabs2.length; i++) {
				tabs2[i].setAttribute('aria-selected', 'false');
				tabs2[i].setAttribute('style', 'font-weight:normal');
			} //reset all tabs to aria-selected=false and normal font weight
			control.attr('aria-selected', 'true'); //set aria-selected=true for clicked tab
			var tabPanels = document.querySelectorAll('[role=tabpanel]'); //get all tabpanels
			for (i = 0; i < tabPanels.length; i++) {
				tabPanels[i].style.display = "none";
			}
			var currentPanel = document.getElementById(id);
			if ( null !== currentPanel ) {
				currentPanel.style.display = "block"; //show tabpanel
			}
		}
		// Arrow key handlers.
		$('.mc-tabs [role=tablist]').keydown(function(e) {
			if (e.keyCode == 37) {
				$("[aria-selected=true]").prev().trigger('click').trigger('focus');
				e.preventDefault();
			}
			if (e.keyCode == 38) {
				$("[aria-selected=true]").prev().trigger('click').trigger('focus');
				e.preventDefault();
			}
			if (e.keyCode == 39) {
				$("[aria-selected=true]").next().trigger('click').trigger('focus');
				e.preventDefault();
			}
			if (e.keyCode == 40) {
				$("[aria-selected=true]").next().trigger('click').trigger('focus');
				e.preventDefault();
			}
		});

	} );

} )( jQuery );