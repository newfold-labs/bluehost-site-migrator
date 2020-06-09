/// <reference types="cypress" />

describe('Complete', function () {

	before(() => {
		cy.navigateTo('/complete');
	});

	it('Has "Login to Bluehost" Button', () => {
		cy.contains('a', 'Login to Bluehost')
			.should('have.attr', 'href')
			.and('include', 'https://my.bluehost.com/cgi/site_migration/?migrationId=');
	});

	it('Has "Create Account" Link', () => {
		cy.contains('a', 'Create account')
			.should('have.attr', 'href')
			.and('include', 'https://www.bluehost.com/cgi-bin/signup/?migrationId=');
	});

});
