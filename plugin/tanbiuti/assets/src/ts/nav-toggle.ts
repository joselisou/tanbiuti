/**
 * Wires up the mobile hamburger button to open/close the off-canvas nav drawer, closing it again
 * on overlay click, nav-link click, or Escape — so the nav works fully without JS too (it's just
 * permanently open off-screen-left on mobile until this progressive enhancement runs).
 *
 * @param root Element to search within for the nav/hamburger/overlay. Defaults to `document`.
 */
export function initNavToggle( root: ParentNode = document ): void {
	const button = root.querySelector< HTMLButtonElement >(
		'.tanbiuti-hamburger'
	);
	const nav = root.querySelector< HTMLElement >( '.tanbiuti-nav' );
	const overlay = root.querySelector< HTMLElement >(
		'.tanbiuti-nav-overlay'
	);

	if ( ! button || ! nav || ! overlay ) {
		return;
	}

	const open = () => {
		nav.classList.add( 'is-open' );
		overlay.hidden = false;
		button.setAttribute( 'aria-expanded', 'true' );
	};

	const close = () => {
		nav.classList.remove( 'is-open' );
		overlay.hidden = true;
		button.setAttribute( 'aria-expanded', 'false' );
	};

	button.addEventListener( 'click', () => {
		if ( nav.classList.contains( 'is-open' ) ) {
			close();
		} else {
			open();
		}
	} );

	overlay.addEventListener( 'click', close );

	nav.querySelectorAll( 'a' ).forEach( ( link ) =>
		link.addEventListener( 'click', close )
	);

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			close();
		}
	} );
}
