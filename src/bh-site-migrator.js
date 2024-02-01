import domReady from '@wordpress/dom-ready';
import { createRoot, render } from '@wordpress/element';

import './styles/bh-site-migrator.css';
import App from './app';

const BH_SITE_MIGRATOR_PAGE_ROOT_ELEMENT = 'bh-sm-app';

const RenderBluehostSiteMigrator = () => {
	const DOM_ELEMENT = document.getElementById(
		BH_SITE_MIGRATOR_PAGE_ROOT_ELEMENT
	);

	if ( null !== DOM_ELEMENT ) {
		if ( 'undefined' !== typeof createRoot ) {
			// WP 6.2+ only
			createRoot( DOM_ELEMENT ).render( <App /> );
		} else if ( 'undefined' !== typeof render ) {
			render( <App />, DOM_ELEMENT );
		}
	}
};

domReady( RenderBluehostSiteMigrator );
