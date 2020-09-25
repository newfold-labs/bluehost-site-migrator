/// <reference types="cypress" />

describe(
	'Compatible',
	function () {

		before(
			() => {
				cy.navigateTo('/compatible');
			}
		);

		beforeEach(
			() => {
				cy.server({delay: 1000, status: 200});
				cy.route('POST', '**/bluehost-site-migrator/v1/migration-package/*', 'fx:migrationPackage');
			}
		);

		it(
			'Can initiate transfer',
			() => {
				cy.contains('button', 'Start Transfer').scrollIntoView().click();
				cy.wait(500);
				cy.hash().should('eq', '#/transfer');
			}
		);

		it('Can cancel transfer',
			() => {
				cy.contains('button', 'Cancel Transfer').scrollIntoView().click();
				cy.hash().should('eq', '#/compatible');
			}
		);

	}
);
