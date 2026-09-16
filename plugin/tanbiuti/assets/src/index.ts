import './style.scss';
import { initListFilters } from './ts/list-filter';
import { initNavToggle } from './ts/nav-toggle';
import { initDatePickers } from './ts/date-picker';
import { initQuerySelects } from './ts/query-select';
import { initPasswordToggles } from './ts/password-toggle';
import { initPanelToggles } from './ts/panel-toggle';

document.addEventListener( 'DOMContentLoaded', () => {
	initListFilters();
	initNavToggle();
	initDatePickers();
	initQuerySelects();
	initPasswordToggles();
	initPanelToggles();
} );
