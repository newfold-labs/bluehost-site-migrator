/// <reference types="cypress" />

describe('Error', function () {

	beforeEach(() => {
		cy.navigateTo('/error');
	});

	it('Can Try Again', () => {
		cy.contains('button', 'Try Again').click();
		cy.hash().should('eq', '#/compatible');
	});

	it('Has Correct Phone Number', () => {
		cy.contains('p', '888-401-4678');
	});

});
