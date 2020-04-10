/// <reference types="cypress" />

describe('Error', function () {

	beforeEach(() => {
		cy.navigateTo('/error');
	});

	it('Can Try Again', () => {
		cy.contains('button', 'Try Again').click();
		cy.hash().should('eq', '#/compatible');
	});

});
