import stepResult from '../fixtures/stepResult.json';

const API_BASE = '/bluehost-site-migrator/v1/';
const MIGRATION_CHECK_BASE = API_BASE.concat( 'migration-check' );
const MIGRATION_DATA_BASE = API_BASE.concat( 'migration-data' );

describe( 'migration component render checks', () => {
	// Login to wp admin
	beforeEach( () => {
		cy.login( Cypress.env( 'username' ), Cypress.env( 'password' ) );
	} );

	it( 'loads compatibility check when all uninitialized', () => {
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
		cy.get( '#check-compatibility-button' ).should( 'be.visible' );
	} );

	it( 'loads the succeeded page when migration is a success', () => {
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
					packaged_success: true,
				},
			}
		).as( 'stubStepResult' );
		cy.intercept(
			{
				method: 'GET',
				url: `**${ encodeURIComponent( MIGRATION_DATA_BASE ) }**`,
				ß,
			},
			{
				statusCode: 200,
				fixture: 'migrationData.json',
			}
		);
		cy.visit( '/wp-admin/admin.php?page=bluehost-site-migrator' );
		cy.wait( '@stubStepResult' );
		cy.get( '#copy-transfer-key-button' ).should( 'be.visible' );
	} );

	it( 'redirects to the error page when migration failed', () => {
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
					packaged_failed: true,
				},
			}
		).as( 'stubStepResult' );
		cy.visit( '/wp-admin/admin.php?page=bluehost-site-migrator' );
		cy.wait( '@stubStepResult' );
		cy.hash().should( 'eq', '#/error' );
	} );

	it( 'loads the transfer status page when migration is in progress', () => {
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
					transfer_queued: true,
				},
			}
		).as( 'stubStepResult' );
		cy.visit( '/wp-admin/admin.php?page=bluehost-site-migrator' );
		cy.wait( '@stubStepResult' );
		cy.get( '#transfer-status-heading' ).should( 'be.visible' );
	} );

	it( 'loads begin transfer when compatibility check passes', () => {
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
		cy.visit( '/wp-admin/admin.php?page=bluehost-site-migrator' );
		cy.wait( '@stubStepResult' );
		cy.get( '#begin-transfer-button' ).should( 'be.visible' );
	} );

	it( 'redirect to incompatible page when compatibility check fails', () => {
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
					checked: true,
				},
			}
		).as( 'stubStepResult' );
		cy.visit( '/wp-admin/admin.php?page=bluehost-site-migrator' );
		cy.wait( '@stubStepResult' );
		cy.hash().should( 'eq', '#/incompatible' );
	} );
} );
