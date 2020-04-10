/// <reference types="cypress" />

describe('Compatible', function () {

	before(() => {
		cy.navigateTo('/compatible');
	});

	it('Can initiate transfer', () => {
		cy.server({delay: 500, status: 200});
		cy.contains('button', 'Start Transfer').click();
		cy.wait(1000);
		cy.hash().should('eq', '#/transfer');
	});

	it('Can cancel transfer', () => {
		cy.contains('button', 'Cancel Transfer').click();
		cy.hash().should('eq', '#/compatible');
	});

});
