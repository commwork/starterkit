/**
 * Animate process module clock hands when each item enters the viewport.
 */
(function () {
	function activateProcessItem( item ) {
		item.classList.add( 'is-in-view' );
	}

	function initProcessClocks() {
		var module = document.querySelector( '.module-process' );
		if ( ! module ) {
			return;
		}

		var items = module.querySelectorAll( '.process-item' );
		var clockItems = [];

		items.forEach( function ( item ) {
			if ( item.querySelector( '.process-clock' ) ) {
				clockItems.push( item );
			}
		} );

		if ( ! clockItems.length ) {
			return;
		}

		var prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( prefersReducedMotion || ! ( 'IntersectionObserver' in window ) ) {
			clockItems.forEach( activateProcessItem );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries, obs ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}

					activateProcessItem( entry.target );
					obs.unobserve( entry.target );
				} );
			},
			{
				threshold: 0.2,
				rootMargin: '0px 0px -5% 0px',
			}
		);

		clockItems.forEach( function ( item ) {
			observer.observe( item );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initProcessClocks );
	} else {
		initProcessClocks();
	}
} )();
