const WEEKDAYS = [ 'Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb' ];
const MONTHS = [
	'Janeiro',
	'Fevereiro',
	'Março',
	'Abril',
	'Maio',
	'Junho',
	'Julho',
	'Agosto',
	'Setembro',
	'Outubro',
	'Novembro',
	'Dezembro',
];
const MONTHS_SHORT = [
	'Jan',
	'Fev',
	'Mar',
	'Abr',
	'Mai',
	'Jun',
	'Jul',
	'Ago',
	'Set',
	'Out',
	'Nov',
	'Dez',
];
const DECADE_SIZE = 10;

function pad( n: number ): string {
	return String( n ).padStart( 2, '0' );
}

function toIso( year: number, month: number, day: number ): string {
	return `${ year }-${ pad( month + 1 ) }-${ pad( day ) }`;
}

function toBr( iso: string ): string {
	const [ year, month, day ] = iso.split( '-' );
	return `${ day }/${ month }/${ year }`;
}

/**
 * Creates a nav/cell button with a click handler that calls `stopPropagation()` before running
 * `onClick`. Needed on every button that re-renders `panel` (replacing its own DOM node): without
 * it, the click would keep bubbling after the re-render, and by the time the document-level
 * "click outside closes the panel" listener (see initDatePickers) runs, the original
 * event.target is already detached, so its `container.contains()` check wrongly reports the
 * click as outside and closes the panel instead of navigating.
 *
 * @param text    Button label.
 * @param onClick Called (with propagation already stopped) when the button is clicked.
 */
function navButton( text: string, onClick: () => void ): HTMLButtonElement {
	const button = document.createElement( 'button' );
	button.type = 'button';
	button.textContent = text;
	button.addEventListener( 'click', ( event ) => {
		event.stopPropagation();
		onClick();
	} );
	return button;
}

/**
 * Renders the day grid for `year`/`month` into `panel`. The month and year labels in the header
 * are themselves buttons that drill up to {@see renderMonthGrid} and {@see renderYearGrid} — an
 * improvement over the real source app's own date picker, which has no such shortcuts.
 *
 * @param panel       Container to render the calendar into.
 * @param year        Four-digit year of the month being shown.
 * @param month       Zero-based month index (0 = January) being shown.
 * @param selectedIso The currently selected date, "YYYY-MM-DD", highlighted if visible.
 * @param onPick      Called with the picked "YYYY-MM-DD" when a day is clicked.
 */
function renderDayGrid(
	panel: HTMLElement,
	year: number,
	month: number,
	selectedIso: string,
	onPick: ( iso: string ) => void
): void {
	panel.innerHTML = '';

	const header = document.createElement( 'div' );
	header.className = 'tanbiuti-datepicker__nav';

	const prev = navButton( '‹', () =>
		renderDayGrid(
			panel,
			month === 0 ? year - 1 : year,
			month === 0 ? 11 : month - 1,
			selectedIso,
			onPick
		)
	);

	const monthLabel = navButton( MONTHS[ month ], () =>
		renderMonthGrid( panel, year, month, selectedIso, onPick )
	);
	monthLabel.className = 'tanbiuti-datepicker__nav-label';

	const yearLabel = navButton( String( year ), () =>
		renderYearGrid( panel, year, month, selectedIso, onPick )
	);
	yearLabel.className = 'tanbiuti-datepicker__nav-label';

	const label = document.createElement( 'span' );
	label.className = 'tanbiuti-datepicker__nav-labels';
	label.append( monthLabel, yearLabel );

	const next = navButton( '›', () =>
		renderDayGrid(
			panel,
			month === 11 ? year + 1 : year,
			month === 11 ? 0 : month + 1,
			selectedIso,
			onPick
		)
	);

	header.append( prev, label, next );
	panel.appendChild( header );

	const grid = document.createElement( 'div' );
	grid.className = 'tanbiuti-datepicker__grid';

	WEEKDAYS.forEach( ( day ) => {
		const cell = document.createElement( 'span' );
		cell.className = 'tanbiuti-datepicker__weekday';
		cell.textContent = day;
		grid.appendChild( cell );
	} );

	const firstOfMonth = new Date( Date.UTC( year, month, 1 ) );
	const startOffset = firstOfMonth.getUTCDay();
	const daysInMonth = new Date( Date.UTC( year, month + 1, 0 ) ).getUTCDate();

	for ( let i = 0; i < startOffset; i++ ) {
		grid.appendChild( document.createElement( 'span' ) );
	}

	for ( let day = 1; day <= daysInMonth; day++ ) {
		const iso = toIso( year, month, day );
		const button = document.createElement( 'button' );
		button.type = 'button';
		button.textContent = String( day );
		button.className = 'tanbiuti-datepicker__day';
		if ( iso === selectedIso ) {
			button.classList.add( 'is-selected' );
		}
		button.addEventListener( 'click', () => onPick( iso ) );
		grid.appendChild( button );
	}

	panel.appendChild( grid );
}

/**
 * Renders a 12-month grid for `year` into `panel`, reached by clicking the day grid's month
 * label. Picking a month drills back down to {@see renderDayGrid} for that month.
 *
 * @param panel         Container to render the grid into.
 * @param year          Four-digit year being shown.
 * @param selectedMonth Zero-based month index to return to the day grid on if none is picked.
 * @param selectedIso   The currently selected date, "YYYY-MM-DD".
 * @param onPick        Called with the picked "YYYY-MM-DD" when a day is eventually clicked.
 */
function renderMonthGrid(
	panel: HTMLElement,
	year: number,
	selectedMonth: number,
	selectedIso: string,
	onPick: ( iso: string ) => void
): void {
	panel.innerHTML = '';

	const header = document.createElement( 'div' );
	header.className = 'tanbiuti-datepicker__nav';

	const prev = navButton( '‹', () =>
		renderMonthGrid( panel, year - 1, selectedMonth, selectedIso, onPick )
	);
	const yearLabel = navButton( String( year ), () =>
		renderYearGrid( panel, year, selectedMonth, selectedIso, onPick )
	);
	yearLabel.className = 'tanbiuti-datepicker__nav-label';
	const next = navButton( '›', () =>
		renderMonthGrid( panel, year + 1, selectedMonth, selectedIso, onPick )
	);

	header.append( prev, yearLabel, next );
	panel.appendChild( header );

	const grid = document.createElement( 'div' );
	grid.className =
		'tanbiuti-datepicker__grid tanbiuti-datepicker__grid--months';

	MONTHS_SHORT.forEach( ( label, index ) => {
		const button = document.createElement( 'button' );
		button.type = 'button';
		button.textContent = label;
		button.className = 'tanbiuti-datepicker__day';
		if (
			index === selectedMonth &&
			String( year ) === selectedIso.split( '-' )[ 0 ]
		) {
			button.classList.add( 'is-selected' );
		}
		button.addEventListener( 'click', ( event ) => {
			event.stopPropagation();
			renderDayGrid( panel, year, index, selectedIso, onPick );
		} );
		grid.appendChild( button );
	} );

	panel.appendChild( grid );
}

/**
 * Renders a 10-year decade grid into `panel`, reached by clicking the day grid's year label (or
 * the month grid's year label). Picking a year drills back down to {@see renderMonthGrid} for
 * that year.
 *
 * @param panel         Container to render the grid into.
 * @param year          A year within the decade to show — the decade itself is derived from it.
 * @param selectedMonth Zero-based month index to carry back down to the month grid.
 * @param selectedIso   The currently selected date, "YYYY-MM-DD".
 * @param onPick        Called with the picked "YYYY-MM-DD" when a day is eventually clicked.
 */
function renderYearGrid(
	panel: HTMLElement,
	year: number,
	selectedMonth: number,
	selectedIso: string,
	onPick: ( iso: string ) => void
): void {
	panel.innerHTML = '';

	const decadeStart = Math.floor( year / DECADE_SIZE ) * DECADE_SIZE;

	const header = document.createElement( 'div' );
	header.className = 'tanbiuti-datepicker__nav';

	const prev = navButton( '‹', () =>
		renderYearGrid(
			panel,
			decadeStart - DECADE_SIZE,
			selectedMonth,
			selectedIso,
			onPick
		)
	);
	const label = document.createElement( 'span' );
	label.className = 'tanbiuti-datepicker__nav-label';
	label.textContent = `${ decadeStart } - ${ decadeStart + DECADE_SIZE - 1 }`;
	const next = navButton( '›', () =>
		renderYearGrid(
			panel,
			decadeStart + DECADE_SIZE,
			selectedMonth,
			selectedIso,
			onPick
		)
	);

	header.append( prev, label, next );
	panel.appendChild( header );

	const grid = document.createElement( 'div' );
	grid.className =
		'tanbiuti-datepicker__grid tanbiuti-datepicker__grid--years';

	for ( let offset = 0; offset < DECADE_SIZE; offset++ ) {
		const cellYear = decadeStart + offset;
		const button = document.createElement( 'button' );
		button.type = 'button';
		button.textContent = String( cellYear );
		button.className = 'tanbiuti-datepicker__day';
		if ( cellYear === Number( selectedIso.split( '-' )[ 0 ] ) ) {
			button.classList.add( 'is-selected' );
		}
		button.addEventListener( 'click', ( event ) => {
			event.stopPropagation();
			renderMonthGrid(
				panel,
				cellYear,
				selectedMonth,
				selectedIso,
				onPick
			);
		} );
		grid.appendChild( button );
	}

	panel.appendChild( grid );
}

// Tracks whichever `.tanbiuti-datepicker__panel` is currently open, across every picker on
// the page, so opening one always closes any other that was left open.
let openPanel: HTMLElement | null = null;

/**
 * Wires up every `[data-tanbiuti-datepicker]` toggle button to open a custom month-grid calendar.
 * With a `data-target` attribute, picking a day fills that hidden input + the picker's own
 * display label instead of navigating (used for the Comissões/Comandas period filters, where
 * both a start and end date need picking before the form is submitted).
 *
 * @param root Element to search within for `[data-tanbiuti-datepicker]` containers. Defaults to `document`.
 */
export function initDatePickers( root: ParentNode = document ): void {
	root.querySelectorAll< HTMLElement >(
		'[data-tanbiuti-datepicker]'
	).forEach( ( container ) => {
		const toggle = container.querySelector< HTMLButtonElement >(
			'.tanbiuti-datepicker__toggle'
		);
		const panel = container.querySelector< HTMLElement >(
			'.tanbiuti-datepicker__panel'
		);
		const targetId = container.dataset.target;
		const target = targetId
			? ( document.getElementById( targetId ) as HTMLInputElement | null )
			: null;
		const baseUrl = container.dataset.baseUrl || '';
		let selectedIso = container.dataset.date || '';

		if ( ! toggle || ! panel || ! selectedIso ) {
			return;
		}

		const display = container.querySelector< HTMLElement >(
			'.tanbiuti-datepicker__display'
		);

		const onPick = ( iso: string ) => {
			selectedIso = iso;
			if ( target ) {
				target.value = iso;
				if ( display ) {
					display.textContent = toBr( iso );
				}
				panel.setAttribute( 'hidden', '' );
			} else {
				window.location.href = `${ baseUrl }?data=${ iso }`;
			}
		};

		toggle.addEventListener( 'click', ( event ) => {
			event.stopPropagation();
			const isHidden = panel.hasAttribute( 'hidden' );
			if ( openPanel && openPanel !== panel ) {
				openPanel.setAttribute( 'hidden', '' );
			}
			if ( isHidden ) {
				const [ year, month ] = selectedIso.split( '-' ).map( Number );
				renderDayGrid( panel, year, month - 1, selectedIso, onPick );
				// `position: fixed`, so this is relative to the viewport — no
				// scroll offset added.
				panel.style.top = `${
					toggle.getBoundingClientRect().bottom
				}px`;
				panel.removeAttribute( 'hidden' );
				openPanel = panel;
			} else {
				panel.setAttribute( 'hidden', '' );
				openPanel = null;
			}
		} );

		document.addEventListener( 'click', ( event ) => {
			if ( ! container.contains( event.target as Node ) ) {
				panel.setAttribute( 'hidden', '' );
				if ( openPanel === panel ) {
					openPanel = null;
				}
			}
		} );
	} );
}
