/// <reference types="cypress" />

describe('Incompatible', function () {

	before(() => {
		cy.navigateTo('/incompatible');
	});

	it('Is Compatible', function () {
		cy.contains('p', 'Give us a call');
	});

});
