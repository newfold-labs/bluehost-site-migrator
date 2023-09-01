/// <reference types="cypress" />

describe('Complete', function () {

	before(() => {
		cy.intercept(
			{
				method: 'GET',
				url: `**${ encodeURIComponent('/bluehost-site-migrator/v1/migration-regions') }*`
			},
			{
				fixture: 'migrationRegions'
			}
		)
			.as('migrationRegions');
		cy.navigateTo('/complete');
	});

	// it('Has "Login to Bluehost" Button', () => {
	// 	cy.wait(500);

	// 	cy.findByRole('link', {name: 'Login to Bluehost'})
	// 		.should('have.attr', 'href')
	// 		.and('include', 'https://my.bluehost.com/cgi/site_migration?migrationId=');
	// });

	it('Has "Create Account" Link', () => {
		cy.wait(500);

		cy.findByRole('link', {name: 'Create account'})
			.should('have.attr', 'href')
			.and('include', 'https://www.bluehost.com/web-hosting/signup?migrationId=');
	});

});
