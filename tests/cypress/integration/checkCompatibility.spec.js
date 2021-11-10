/// <reference types="cypress" />

describe('Check Compatibility', function () {

	beforeEach(() => {
		cy.navigateTo('/');
	});

	it('Is Compatible', function () {
		cy.intercept(
			{
				method: 'POST',
				url: `**${ encodeURIComponent('/bluehost-site-migrator/v1/can-we-migrate') }*`,
			},
			{
				body: {
					can_migrate: true
				}
			}
		)
			.as('isCompatible');
		cy.contains('button', 'Check Compatibility').click();
		cy.wait('@isCompatible');
		cy.hash().should('eq', '#/compatible');
	});

	it('Is Not Compatible', function () {
		cy.intercept(
			{
				method: 'POST',
				url: `**${ encodeURIComponent('/bluehost-site-migrator/v1/can-we-migrate') }*`,
			},
			{
				body: {
					can_migrate: false
				}
			}
		)
			.as('isNotCompatible');
		cy.contains('button', 'Check Compatibility').click();
		cy.wait('@isNotCompatible');
		cy.hash().should('eq', '#/incompatible');
	});

	it('Is Error', function () {
		cy.intercept(
			{
				method: 'POST',
				url: `**${ encodeURIComponent('/bluehost-site-migrator/v1/can-we-migrate') }*`,
			},
			{
				statusCode: 400,
				body: ''
			}
		)
			.as('isError');
		cy.contains('button', 'Check Compatibility').click();
		cy.wait('@isError');
		cy.hash().should('eq', '#/error');
	});

});
