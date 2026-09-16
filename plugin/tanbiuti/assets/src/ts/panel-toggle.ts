/**
 * Wires up `[data-tanbiuti-panel-toggle]` panels — clicking the header expands/collapses the panel's
 * `.tanbiuti-panel__collapse` region, matching the real Comissões report: clicking "Serviços"
 * reveals its day-by-day breakdown, clicking "Descontos e Bônus" reveals its own detail.
 *
 * @param root Element to search within for `[data-tanbiuti-panel-toggle]` panels. Defaults to `document`.
 */
export function initPanelToggles( root: ParentNode = document ): void {
	root.querySelectorAll< HTMLElement >(
		'[data-tanbiuti-panel-toggle]'
	).forEach( ( panel ) => {
		const header = panel.querySelector< HTMLElement >(
			'.tanbiuti-panel__header'
		);
		const collapse = panel.querySelector< HTMLElement >(
			'.tanbiuti-panel__collapse'
		);

		if ( ! header || ! collapse ) {
			return;
		}

		header.setAttribute( 'role', 'button' );
		header.setAttribute( 'tabindex', '0' );
		header.setAttribute( 'aria-expanded', 'false' );

		const toggle = () => {
			const isOpen = panel.classList.toggle( 'is-open' );
			collapse.hidden = ! isOpen;
			header.setAttribute( 'aria-expanded', String( isOpen ) );
		};

		header.addEventListener( 'click', toggle );
		header.addEventListener( 'keydown', ( event ) => {
			if ( event.key === 'Enter' || event.key === ' ' ) {
				event.preventDefault();
				toggle();
			}
		} );
	} );
}
