/// <reference types="cypress" />

describe('Complete', function () {

	before(() => {
		cy.server();
		cy.route('GET', '**/bluehost-site-migrator/v1/migration-id*', 'fx:migrationId').as('migrationId');
		cy.navigateTo('/complete');
	});

	it('Has "Login to Bluehost" Button', () => {
		cy.findByRole('link', {name: 'Login to Bluehost'})
			.should('have.attr', 'href')
			.and('include', 'https://my.bluehost.com/cgi/site_migration/?migrationId=');
	});

	it('Has "Create Account" Link', () => {
		cy.findByRole('link', {name: 'Create account'})
			.should('have.attr', 'href')
			.and('include', 'https://www.bluehost.com/wordpress-site-migration?migrationId=');
	});

});
