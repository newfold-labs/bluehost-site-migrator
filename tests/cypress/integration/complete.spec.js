/// <reference types="cypress" />

describe('Complete', function () {

	before(() => {
		cy.navigateTo('/complete');
	});

	it('Has "Login to Bluehost" Button', () => {
		cy.contains('a', 'Login to Bluehost')
			.should('have.attr', 'href')
			.and('eq', 'https://my.bluehost.com/web-hosting/cplogin');
	});

	it('Has "Create Account" Link', () => {
		cy.contains('a', 'Create account')
			.should('have.attr', 'href')
			.and('eq', 'https://www.bluehost.com/cgi-bin/signup');
	});

});
