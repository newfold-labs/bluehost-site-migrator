// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
Cypress.Commands.add( 'login', ( username, password ) => {
	cy.getCookies().then( ( cookies ) => {
		let hasMatch = false;
		cookies.forEach( ( cookie ) => {
			if ( cookie.name.substring( 0, 20 ) === 'wordpress_logged_in_' ) {
				hasMatch = true;
			}
		} );
		if ( ! hasMatch ) {
			cy.visit( '/wp-login.php' ).wait( 1000 );
			cy.get( '#user_login' ).type( username );
			cy.get( '#user_pass' ).type( `${ password }{enter}` );
		}
	} );
} );
