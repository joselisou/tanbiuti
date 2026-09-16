/**
 * Wires up the show/hide-password eye button next to `[data-tanbiuti-password-toggle]`'s associated
 * input, matching the real source app's login field.
 *
 * @param root Element to search within for `[data-tanbiuti-password-toggle]` buttons. Defaults to `document`.
 */
export function initPasswordToggles( root: ParentNode = document ): void {
	root.querySelectorAll< HTMLButtonElement >(
		'[data-tanbiuti-password-toggle]'
	).forEach( ( button ) => {
		const targetId = button.dataset.tanbiutiPasswordToggle;
		const input = targetId
			? ( document.getElementById( targetId ) as HTMLInputElement | null )
			: null;

		if ( ! input ) {
			return;
		}

		button.addEventListener( 'click', () => {
			const isHidden = input.type === 'password';
			input.type = isHidden ? 'text' : 'password';
			button.classList.toggle( 'is-visible', isHidden );
			button.setAttribute(
				'aria-label',
				isHidden ? 'Ocultar senha' : 'Mostrar senha'
			);
		} );
	} );
}
