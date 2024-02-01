import stepResult from '../fixtures/stepResult.json';

const API_BASE = '/bluehost-site-migrator/v1/';
const MIGRATION_CHECK_BASE = API_BASE.concat( 'migration-check' );

describe( 'migration compatibility check tests', () => {
	// Login to wp admin
	beforeEach( () => {
		cy.login( Cypress.env( 'username' ), Cypress.env( 'password' ) );
		cy.intercept(
			{
				method: 'GET',
				url: `**${ encodeURIComponent(
					MIGRATION_CHECK_BASE.concat( '/step' )
				) }**`,
			},
			{
				statusCode: 200,
				fixture: 'stepResult',
			}
		).as( 'stubStepResult' );
		cy.visit( '/wp-admin/admin.php?page=bluehost-site-migrator' );
		cy.wait( '@stubStepResult' );
	} );

	it( 'compatible check passes', () => {
		cy.intercept(
			{
				method: 'GET',
				url: 'https://hiive.cloud/workers/geolocation/',
			},
			{
				statusCode: 200,
				body: {},
			}
		).as( 'stubGeoResult' );
		cy.intercept(
			{
				method: 'POST',
				url: `**${ encodeURIComponent(
					MIGRATION_CHECK_BASE.concat( '/' )
				) }**`,
			},
			{
				statusCode: 200,
				body: {
					can_migrate: true,
				},
			}
		).as( 'stubMigrationCheck' );
		cy.intercept(
			{
				method: 'GET',
				url: `**${ encodeURIComponent(
					MIGRATION_CHECK_BASE.concat( '/step' )
				) }**`,
			},
			{
				statusCode: 200,
				body: {
					...stepResult,
					compatible: true,
				},
			}
		).as( 'stubStepResult' );
		cy.get( '#check-compatibility-button' ).click();
		cy.wait( '@stubGeoResult' );
		cy.wait( '@stubMigrationCheck' );
		cy.wait( '@stubStepResult' );
		// After redirection verify that we show the begin transfer page
		cy.get( '#begin-transfer-button' ).should( 'be.visible' );
	} );

	it( 'redirects to incompatible if the migration check fails', () => {
		cy.intercept(
			{
				method: 'GET',
				url: 'https://hiive.cloud/workers/geolocation/',
			},
			{
				statusCode: 200,
				body: {},
			}
		).as( 'stubGeoResult' );
		cy.intercept(
			{
				method: 'POST',
				url: `**${ encodeURIComponent(
					MIGRATION_CHECK_BASE.concat( '/' )
				) }**`,
			},
			{
				statusCode: 200,
				body: {
					can_migrate: false,
				},
			}
		).as( 'stubMigrationCheck' );
		cy.get( '#check-compatibility-button' ).click();
		cy.wait( '@stubGeoResult' );
		cy.wait( '@stubMigrationCheck' );
		cy.hash().should( 'eq', '#/incompatible' );
	} );
} );
