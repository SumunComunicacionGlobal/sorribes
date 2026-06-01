/**
 * File navigation.js.
 *
 * Handles toggling the navigation menu for small screens and enables TAB key
 * navigation support for dropdown menus.
 */
( function() {
	const siteNavigation = document.getElementById( 'site-navigation' );

	// Return early if the navigation doesn't exist.
	if ( ! siteNavigation ) {
		return;
	}

	const button = siteNavigation.getElementsByTagName( 'button' )[ 0 ];

	// Return early if the button doesn't exist.
	if ( 'undefined' === typeof button ) {
		return;
	}

	const menu = siteNavigation.getElementsByTagName( 'ul' )[ 0 ];

	// Hide menu toggle button if menu is empty and return early.
	if ( 'undefined' === typeof menu ) {
		button.style.display = 'none';
		return;
	}

	if ( ! menu.classList.contains( 'nav-menu' ) ) {
		menu.classList.add( 'nav-menu' );
	}

	// Toggle the .toggled class and the aria-expanded value each time the button is clicked.
	button.addEventListener( 'click', function() {
		siteNavigation.classList.toggle( 'toggled' );

		if ( button.getAttribute( 'aria-expanded' ) === 'true' ) {
			button.setAttribute( 'aria-expanded', 'false' );
		} else {
			button.setAttribute( 'aria-expanded', 'true' );
		}
	} );

	// Remove the .toggled class and set aria-expanded to false when the user clicks outside the navigation.
	document.addEventListener( 'click', function( event ) {
		const isClickInside = siteNavigation.contains( event.target );

		if ( ! isClickInside ) {
			siteNavigation.classList.remove( 'toggled' );
			button.setAttribute( 'aria-expanded', 'false' );
		}
	} );

	// Get all the link elements within the menu.
	const links = menu.getElementsByTagName( 'a' );

	// Get all the link elements with children within the menu.
	const linksWithChildren = menu.querySelectorAll( '.menu-item-has-children > a, .page_item_has_children > a' );

	// Toggle focus each time a menu link is focused or blurred.
	for ( const link of links ) {
		link.addEventListener( 'focus', toggleFocus, true );
		link.addEventListener( 'blur', toggleFocus, true );
	}

	// Toggle focus each time a menu link with children receive a touch event.
	for ( const link of linksWithChildren ) {
		link.addEventListener( 'touchstart', toggleFocus, false );
	}

	/**
	 * Sets or removes .focus class on an element.
	 */
	function toggleFocus() {
		if ( event.type === 'focus' || event.type === 'blur' ) {
			let self = this;
			// Move up through the ancestors of the current link until we hit .nav-menu.
			while ( ! self.classList.contains( 'nav-menu' ) ) {
				// On li elements toggle the class .focus.
				if ( 'li' === self.tagName.toLowerCase() ) {
					self.classList.toggle( 'focus' );
				}
				self = self.parentNode;
			}
		}

		if ( event.type === 'touchstart' ) {
			const menuItem = this.parentNode;
			event.preventDefault();
			for ( const link of menuItem.parentNode.children ) {
				if ( menuItem !== link ) {
					link.classList.remove( 'focus' );
				}
			}
			menuItem.classList.toggle( 'focus' );
		}
	}
}() );

( function() {
	let initialized = false;
	const activeClass = 'is-active';
	const offsetTop = 95;

	function getHashId( href ) {
		if ( ! href ) {
			return '';
		}

		const hashIndex = href.indexOf( '#' );

		if ( hashIndex === -1 ) {
			return '';
		}

		return decodeURIComponent( href.slice( hashIndex + 1 ) );
	}

	function initLwptocScrollSpy() {
		if ( initialized ) {
			return true;
		}

		const tocRoot = document.querySelector( '.lwptoc.lwptoc' );

		if ( ! tocRoot ) {
			return false;
		}

		const lwptocInner = tocRoot.querySelector( '.lwptoc_i' );
		const lwptocItems = tocRoot.querySelector( '.lwptoc_items' );
		const parentWrap = tocRoot.querySelector( '.lwptoc_items > .lwptoc_itemWrap' );
		const trackContainer = parentWrap || tocRoot.querySelector( '.lwptoc_itemWrap' );

		if ( ! trackContainer ) {
			return false;
		}

		const scrollContainer = lwptocInner || trackContainer;

		if ( lwptocInner ) {
			lwptocInner.style.setProperty( 'overflow-x', 'auto', 'important' );
			lwptocInner.style.setProperty( 'overflow-y', 'hidden', 'important' );
			lwptocInner.style.setProperty( 'width', '100%', 'important' );
			lwptocInner.style.setProperty( 'max-width', '100%', 'important' );
			lwptocInner.style.setProperty( 'min-width', '0', 'important' );
			lwptocInner.style.setProperty( '-webkit-overflow-scrolling', 'touch' );
			lwptocInner.style.setProperty( 'touch-action', 'pan-x', 'important' );
		}

		if ( lwptocItems ) {
			lwptocItems.style.setProperty( 'display', 'block', 'important' );
			lwptocItems.style.setProperty( 'width', '100%', 'important' );
			lwptocItems.style.setProperty( 'max-width', '100%', 'important' );
			lwptocItems.style.setProperty( 'min-width', '0', 'important' );
		}

		trackContainer.style.setProperty( 'display', 'flex', 'important' );
		trackContainer.style.setProperty( 'flex-wrap', 'nowrap', 'important' );
		trackContainer.style.setProperty( 'align-items', 'center', 'important' );
		trackContainer.style.setProperty( 'width', 'max-content', 'important' );
		trackContainer.style.setProperty( 'min-width', 'max-content', 'important' );
		trackContainer.style.setProperty( 'overflow', 'visible', 'important' );

		const items = Array.from( trackContainer.querySelectorAll( ':scope > .lwptoc_item' ) );

		const links = Array.from( tocRoot.querySelectorAll( 'a[href*="#"]' ) );

		if ( ! links.length || ! items.length ) {
			return false;
		}

		const entries = items
			.map( function( item ) {
				const link = item.querySelector( 'a[href*="#"]' );

				if ( ! link ) {
					return null;
				}

				const targetId = getHashId( link.getAttribute( 'href' ) );

				if ( ! targetId ) {
					return null;
				}

				const target = document.getElementById( targetId );

				if ( ! target ) {
					return null;
				}

				item.style.flex = '0 0 auto';
				item.style.whiteSpace = 'nowrap';
				link.style.whiteSpace = 'nowrap';

				return {
					item,
					link,
					target,
					targetId,
				};
			} )
			.filter( Boolean );

		if ( ! entries.length ) {
			return false;
		}

		let pointerActive = false;
		let pointerStartX = 0;
		let startScrollLeft = 0;

		scrollContainer.style.setProperty( 'cursor', 'grab' );

		scrollContainer.addEventListener( 'pointerdown', function( event ) {
			if ( scrollContainer.scrollWidth <= scrollContainer.clientWidth ) {
				return;
			}

			pointerActive = true;
			pointerStartX = event.clientX;
			startScrollLeft = scrollContainer.scrollLeft;
			scrollContainer.style.setProperty( 'cursor', 'grabbing' );
			document.body.style.userSelect = 'none';
		} );

		scrollContainer.addEventListener( 'pointermove', function( event ) {
			if ( ! pointerActive ) {
				return;
			}

			event.preventDefault();
			const deltaX = event.clientX - pointerStartX;
			scrollContainer.scrollLeft = startScrollLeft - deltaX;
		} );

		function stopPointerDrag() {
			if ( ! pointerActive ) {
				return;
			}

			pointerActive = false;
			scrollContainer.style.setProperty( 'cursor', 'grab' );
			document.body.style.userSelect = '';
		}

		scrollContainer.addEventListener( 'pointerup', stopPointerDrag );
		scrollContainer.addEventListener( 'pointerleave', stopPointerDrag );
		scrollContainer.addEventListener( 'pointercancel', stopPointerDrag );

		scrollContainer.addEventListener( 'wheel', function( event ) {
			if ( scrollContainer.scrollWidth <= scrollContainer.clientWidth ) {
				return;
			}

			if ( Math.abs( event.deltaY ) > Math.abs( event.deltaX ) ) {
				event.preventDefault();
				scrollContainer.scrollLeft += event.deltaY;
			}
		}, { passive: false } );

		initialized = true;

		let activeId = '';
		let rafId = 0;

		function centerActiveItem( item ) {
			const containerRect = scrollContainer.getBoundingClientRect();
			const itemRect = item.getBoundingClientRect();
			const itemLeft = itemRect.left - containerRect.left + scrollContainer.scrollLeft;
			const targetLeft = itemLeft - ( scrollContainer.clientWidth / 2 ) + ( itemRect.width / 2 );

			scrollContainer.scrollTo( {
				left: Math.max( 0, targetLeft ),
				behavior: 'smooth',
			} );
		}

		function setActiveById( nextId ) {
			if ( ! nextId || nextId === activeId ) {
				return;
			}

			activeId = nextId;

			for ( const entry of entries ) {
				const isActive = entry.targetId === nextId;

				entry.link.classList.toggle( activeClass, isActive );
				entry.item.classList.toggle( activeClass, isActive );
				entry.link.setAttribute( 'aria-current', isActive ? 'true' : 'false' );

				if ( isActive ) {
					centerActiveItem( entry.item );
				}
			}
		}

		function detectActiveSection() {
			let nextId = entries[ 0 ].targetId;

			for ( const entry of entries ) {
				const targetTop = entry.target.getBoundingClientRect().top;

				if ( targetTop <= offsetTop ) {
					nextId = entry.targetId;
				} else {
					break;
				}
			}

			setActiveById( nextId );
		}

		function onScrollOrResize() {
			if ( rafId ) {
				return;
			}

			rafId = window.requestAnimationFrame( function() {
				rafId = 0;
				detectActiveSection();
			} );
		}

		for ( const entry of entries ) {
			entry.link.addEventListener( 'click', function() {
				setActiveById( entry.targetId );
			} );
		}

		window.addEventListener( 'scroll', onScrollOrResize, { passive: true } );
		window.addEventListener( 'resize', onScrollOrResize );
		detectActiveSection();

		return true;
	}

	if ( initLwptocScrollSpy() ) {
		return;
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initLwptocScrollSpy, { once: true } );
	} else {
		window.addEventListener( 'load', initLwptocScrollSpy, { once: true } );
	}
}() );

