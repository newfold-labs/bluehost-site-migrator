/// <reference types="cypress" />

describe('Transfer', function () {

	beforeEach(() => {
		cy.navigateTo('/transfer');
	});

	it('Generates Packages & Sends Manifest', function () {

		cy.server({delay: 500, force404: true});

		cy.route('GET', '/wp-json/bluehost-site-migrator/v1/migration-package?*', 'fx:migrationPackages').as('fetchPackages');
		cy.route('GET', '/wp-json/bluehost-site-migrator/v1/migration-package/*/is-valid*', false);
		cy.route('POST', '/wp-json/bluehost-site-migrator/v1/migration-package/database?*', 'fx:migrationPackage').as('database');
		cy.route('POST', '/wp-json/bluehost-site-migrator/v1/migration-package/dropins?*', 'fx:migrationPackage').as('dropins');
		cy.route('POST', '/wp-json/bluehost-site-migrator/v1/migration-package/mu-plugins?*', 'fx:migrationPackage').as('mu-plugins');
		cy.route('POST', '/wp-json/bluehost-site-migrator/v1/migration-package/plugins?*', 'fx:migrationPackage').as('plugins');
		cy.route('POST', '/wp-json/bluehost-site-migrator/v1/migration-package/themes?*', 'fx:migrationPackage').as('themes');
		cy.route('POST', '/wp-json/bluehost-site-migrator/v1/migration-package/uploads?*', 'fx:migrationPackage').as('uploads');
		cy.route('POST', '/wp-json/bluehost-site-migrator/v1/migration-package/root?*', 'fx:migrationPackage').as('root');
		cy.route('POST', '/wp-json/bluehost-site-migrator/v1/manifest/send*', true).as('sendManifest');
		cy.route('GET', '/wp-json/bluehost-site-migrator/v1/migration-id?*', JSON.stringify('5ee2459e0d8066001c252bbd')).as('migrationId');

		cy.contains('p', 'Preparing to generate package files...');

		cy.wait('@database');
		cy.contains('p', 'Packaging database...');

		cy.wait('@dropins');
		cy.contains('p', 'Packaging dropins...');

		cy.wait('@mu-plugins');
		cy.contains('p', 'Packaging mu-plugins...');

		cy.wait('@plugins');
		cy.contains('p', 'Packaging plugins...');

		cy.wait('@themes');
		cy.contains('p', 'Packaging themes...');

		cy.wait('@uploads');
		cy.contains('p', 'Packaging uploads...');

		cy.wait('@root');
		cy.contains('p', 'Packaging root...');

		cy.wait('@sendManifest');
		cy.hash().should('eq', '#/complete');
	});

});
