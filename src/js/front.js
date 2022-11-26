// CustomEvent polyfil for IE support
( function () {

	if ( typeof window.CustomEvent === 'function' ) {
		return false;
	}

	function CustomEvent( event, params ) {
		params = params || { bubbles: false, cancellable: false, detail: undefined };

		var evt = document.createEvent( 'CustomEvent' );

		evt.initCustomEvent( event, params.bubbles, params.cancellable, params.detail );

		return evt;
	}

	CustomEvent.prototype = window.Event.prototype;

	window.CustomEvent = CustomEvent;
} )();

// ClassList polyfill for IE/Safari support.
( function () {
	var regExp = function ( name ) {
		return new RegExp( '(^| )' + name + '( |$)' );
	};

	var forEach = function ( list, fn, scope ) {
		for ( var i = 0; i < list.length; i++ ) {
			fn.call( scope, list[i] );
		}
	};

	function ClassList( element ) {
		this.element = element;
	}

	ClassList.prototype = {
		add: function () {
			forEach( arguments, function ( name ) {
				if ( !this.contains( name ) ) {
					this.element.className += this.element.className.length > 0 ? ' ' + name : name;
				}
			}, this );
		},
		remove: function () {
			forEach( arguments, function ( name ) {
				this.element.className =
					this.element.className.replace( regExp( name ), '' );
			}, this );
		},
		toggle: function ( name ) {
			return this.contains( name )
				? ( this.remove( name ), false ) : ( this.add( name ), true );
		},
		contains: function ( name ) {
			return regExp( name ).test( this.element.className );
		},
		// bonus.
		replace: function ( oldName, newName ) {
			this.remove( oldName ), this.add( newName );
		}
	};

	// IE8/9, Safari.
	if ( !( 'classList' in Element.prototype ) ) {
		Object.defineProperty( Element.prototype, 'classList', {
			get: function () {
				return new ClassList( this );
			}
		} );
	}

	if ( window.DOMTokenList && DOMTokenList.prototype.replace == null )
		DOMTokenList.prototype.replace = ClassList.prototype.replace;
} )();

// define acn.
( function ( window, document, undefined ) {

	var acn = new function () {
		// cookie status
		this.cookiesAccepted = null;

		// notice container
		this.noticeContainer = null;

		// set cookie value
		this.setStatus = function ( cookieValue ) {
			var _this = this;

			var date = new Date(),
				expireDate = new Date();

			// set cookie type and expiry time in seconds
			if ( cookieValue === 'accept' ) {
				cookieValue = 'true';
				expireDate.setTime( parseInt( date.getTime() ) + parseInt( acnSettings.cookieTime ) * 1000 );
			} else {
				cookieValue = 'false';
				expireDate.setTime( parseInt( date.getTime() ) + parseInt( acnSettings.cookieTime ) * 1000 );
			}

			// set cookie
			document.cookie = acnSettings.cookieName + '=' + cookieValue + ';expires=' + expireDate.toUTCString() + ';' + ( !!acnSettings.cookieDomain ? 'domain=' + acnSettings.cookieDomain + ';' : '' ) + ( !!acnSettings.cookiePath ? 'path=' + acnSettings.cookiePath + ';' : '' ) + ( acnSettings.secure === '1' ? 'secure;' : '' );

			// update global status.
			this.cookiesAccepted = ( cookieValue === 'true' );

			// trigger custom event.
			var event = new CustomEvent(
				'setacn',
				{
					detail: {
						value: cookieValue,
						time: date,
						expires: expireDate,
						data: acnSettings
					}
				}
			);

			document.dispatchEvent( event );

			this.setBodyClass( [ 'cookies-set', cookieValue === 'true' ? 'cookies-accepted' : 'cookies-refused' ] );

			this.hideacn();

			// show revoke notice if enabled
			if ( acnSettings.revokeCookiesOpt === 'automatic' ) {
				// show Accessible Cookie Notice after the revoke is hidden.
				this.noticeContainer.addEventListener( 'animationend', function handler() {
					_this.noticeContainer.removeEventListener( 'animationend', handler );
					_this.showRevokeNotice();
				} );
				this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
					_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
					_this.showRevokeNotice();
				} );
			}

			// redirect?
			if ( acnSettings.redirection === '1' && ( ( cookieValue === 'true' && this.cookiesAccepted === null ) || ( cookieValue !== this.cookiesAccepted && this.cookiesAccepted !== null ) ) ) {
				var url = window.location.protocol + '//',
					hostname = window.location.host + '/' + window.location.pathname;

				// enabled cache?
				if ( acnSettings.cache === '1' ) {
					url = url + hostname.replace( '//', '/' ) + ( window.location.search === '' ? '?' : window.location.search + '&' ) + 'cn-reloaded=1' + window.location.hash;

					window.location.href = url;
				} else {
					url = url + hostname.replace( '//', '/' ) + window.location.search + window.location.hash;

					window.location.reload( true );
				}

				return;
			}
		};

		// get cookie value
		this.getStatus = function ( bool ) {
			var value = "; " + document.cookie,
				parts = value.split( '; acn_accepted=' );

			if ( parts.length === 2 ) {
				var val = parts.pop().split( ';' ).shift();

				if ( bool )
					return val === 'true';
				else
					return val;
			} else
				return null;
		};

		// Show Accessible Cookie Notice
		this.showacn = function () {
			var _this = this;

			// trigger custom event
			var event = new CustomEvent(
				'showacn',
				{
					detail: {
						data: acnSettings
					}
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.remove( 'cookie-notice-hidden' );
			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.add( 'cookie-notice-visible' );

			var positionBottom = document.querySelector( '#acn.cn-position-bottom' );
			var body           = document.querySelector( 'body' );
			if ( null !== positionBottom ) {
				// If positioned on the bottom, set padding equal to banner height.
				var height = this.noticeContainer.clientHeight;
				console.log( 'height', height );
				body.style.paddingBottom = height + 'px';
			}

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.removeEventListener( 'animationend', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
			} );
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
			} );
		};

		// hide Accessible Cookie Notice
		this.hideacn = function () {
			var _this = this;

			// trigger custom event
			var event = new CustomEvent(
				'hideacn',
				{
					detail: {
						data: acnSettings
					}
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.remove( 'cookie-notice-visible' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.removeEventListener( 'animationend', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-notice-hidden' );
			} );
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-notice-hidden' );
			} );
		};

		// display revoke notice
		this.showRevokeNotice = function () {
			var _this = this;

			// trigger custom event
			var event = new CustomEvent(
				'showRevokeNotice',
				{
					detail: {
						data: acnSettings
					}
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.remove( 'cookie-revoke-hidden' );
			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.add( 'cookie-revoke-visible' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.removeEventListener( 'animationend', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
			} );
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
			} );
		};

		// hide revoke notice
		this.hideRevokeNotice = function () {
			var _this = this;

			// trigger custom event
			var event = new CustomEvent(
				'hideRevokeNotice',
				{
					detail: {
						data: acnSettings
					}
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.remove( 'cookie-revoke-visible' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.removeEventListener( 'animationend', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-revoke-hidden' );
			} );
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-revoke-hidden' );
			} );
		};

		// change body classes
		this.setBodyClass = function ( classes ) {
			// remove body classes
			document.body.classList.remove( 'cookies-revoke' );
			document.body.classList.remove( 'cookies-accepted' );
			document.body.classList.remove( 'cookies-refused' );
			document.body.classList.remove( 'cookies-set' );
			document.body.classList.remove( 'cookies-not-set' );

			// add body classes
			for ( var i = 0; i < classes.length; i++ ) {
				document.body.classList.add( classes[i] );
			}
		};

		// initialize
		this.init = function () {
			var _this = this;

			this.cookiesAccepted = this.getStatus( true );
			this.noticeContainer = document.getElementById( 'acn' );

			// If positioned to top, move in DOM.
			var positionTop = document.querySelector( '#acn.cn-position-top' );
			var body        = document.querySelector( 'body' );
			if ( null !== positionTop ) {
				positionTop.remove();
				body.insertAdjacentElement( 'afterbegin', positionTop );
			}

			var cookieButtons = document.getElementsByClassName( 'cn-set-cookie' ),
				revokeButtons = document.getElementsByClassName( 'cn-revoke-cookie' );

			// add effect class
			this.noticeContainer.classList.add( 'cn-effect-' + acnSettings.hideEffect );

			// check cookies status
			if ( this.cookiesAccepted === null ) {
				this.setBodyClass( [ 'cookies-not-set' ] );
				// show Accessible Cookie Notice
				this.showacn();
			} else {
				this.setBodyClass( [ 'cookies-set', this.cookiesAccepted === true ? 'cookies-accepted' : 'cookies-refused' ] );

				// show revoke notice if enabled
				if ( acnSettings.revokeCookies === '1' && acnSettings.revokeCookiesOpt === 'automatic' ) {
					this.showRevokeNotice();
				}
			}

			// handle cookie buttons click
			for ( var i = 0; i < cookieButtons.length; i++ ) {
				cookieButtons[i].addEventListener( 'click', function ( e ) {
					e.preventDefault();
					// Chrome double click event fix
					e.stopPropagation();

					_this.setStatus( this.dataset.cookieSet );
				} );
			}

			// handle revoke buttons click
			for ( var i = 0; i < revokeButtons.length; i++ ) {
				revokeButtons[i].addEventListener( 'click', function ( e ) {
					e.preventDefault();

					// hide revoke notice
					if ( _this.noticeContainer.classList.contains( 'cookie-revoke-visible' ) ) {
						_this.hideRevokeNotice();

						// show Accessible Cookie Notice after the revoke is hidden
						_this.noticeContainer.addEventListener( 'animationend', function handler() {
							_this.noticeContainer.removeEventListener( 'animationend', handler );
							_this.showacn();
						} );
						_this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
							_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
							_this.showacn();
						} );
						// show Accessible Cookie Notice
					} else if ( _this.noticeContainer.classList.contains( 'cookie-notice-hidden' ) && _this.noticeContainer.classList.contains( 'cookie-revoke-hidden' ) ) {
						_this.showacn();
					}
				} );
			}
		};
	}

	// initialize plugin
	window.addEventListener( 'load', function () {
		acn.init();
	}, false );

} )( window, document, undefined );

/**
 * Delete unapproved cookies.
 */
function deleteCookies() {
	var cookies    = document.cookie.split(";");
	var all_cookies = '';

	for (var i = 0; i < cookies.length; i++) {
		var cookie_name  = cookies[i].split("=")[0];
		var cookie_value = cookies[i].split("=")[1];

		// Disallowed cookies.
		if ( cookie_name.trim() != '__utmb' ) {
			all_cookies = all_cookies + cookies[i] + ";";
		}
	}

	if ( !document.__defineGetter__) {
		Object.defineProperty(document, 'cookie', {
			get: function(){return all_cookies; },
			set: function(){return true},
		});
	} else {
		document.__defineGetter__( 'cookie', function() { return all_cookies; } );
		document.__defineSetter__( 'cookie', function() { return true; } );
	}
}