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
				cy.intercept(
					{
						method: 'GET',
						url: `**${ encodeURIComponent('/bluehost-site-migrator/v1/migration-package/') }*%2Fis-valid*`
					},
					{
						body: true,
						delay: 500
					}
				);

				cy.intercept(
					{
						method: 'POST',
						url: `**${ encodeURIComponent('/bluehost-site-migrator/v1/migration-package') }*`
					},
					{
						fixture: 'migrationPackage',
						delay: 500
					}
				);

				cy.intercept(
					{
						method: 'POST',
						url: `**${ encodeURIComponent('/bluehost-site-migrator/v1/manifest/send') }*`
					},
					{
						fixture: 'manifestSend'
					}
				)
					.as('sendManifest');

			}
		);

		it(
			'Can initiate transfer',
			() => {
				cy.contains('button', 'Start Transfer').scrollIntoView().click();
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
