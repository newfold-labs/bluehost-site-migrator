import stepResult from '../fixtures/stepResult.json';
import transferStatus from '../fixtures/transferStatus.json';

const API_BASE = '/bluehost-site-migrator/v1/';
const MIGRATION_CHECK_BASE = API_BASE.concat( 'migration-check' );
const MIGRATION_TASKS_BASE = API_BASE.concat( 'migration-tasks' );
const MIGRATION_DATA_BASE = API_BASE.concat( 'migration-data' );

describe( 'migration compatibility check tests', () => {
	// Login to wp admin
	beforeEach( () => {
		cy.login( Cypress.env( 'username' ), Cypress.env( 'password' ) );
		cy.intercept(
			{
				method: 'GET',
				url: `**${ MIGRATION_CHECK_BASE.concat( '/step' ) }**`,
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
	} );

	it( 'renders complete when successful', () => {
		cy.intercept(
			{
				method: 'GET',
				url: `**${ MIGRATION_TASKS_BASE.concat( '/status' ) }**`,
			},
			{
				statusCode: 200,
				fixture: 'transferStatus',
			}
		).as( 'stubTransferStatusCheck' );
		cy.intercept(
			{
				method: 'POST',
				url: `**${ MIGRATION_TASKS_BASE.concat( '/send-files' ) }**`,
			},
			{
				statusCode: 200,
				body: {
					success: true,
				},
			}
		).as( 'stubSendFiles' );
		cy.intercept(
			{
				method: 'GET',
				url: `**${ MIGRATION_CHECK_BASE.concat( '/step' ) }**`,
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
				url: `**${ MIGRATION_DATA_BASE }**`,
			},
			{
				statusCode: 200,
				fixture: 'migrationData.json',
			}
		).as( 'stubMigrationData' );
		cy.wait( '@stubTransferStatusCheck' );
		cy.wait( '@stubSendFiles' );
		cy.wait( '@stubMigrationData' );
		cy.wait( '@stubStepResult' );
		// After redirection verify that we show the completed page
		cy.get( '#copy-transfer-key-button' ).should( 'be.visible' );
	} );

	it( 'renders the error page if file update fails', () => {
		cy.intercept(
			{
				method: 'GET',
				url: `**${ MIGRATION_TASKS_BASE.concat( '/status' ) }**`,
			},
			{
				statusCode: 200,
				fixture: 'transferStatus',
			}
		).as( 'stubTransferStatusCheck' );
		cy.intercept(
			{
				method: 'POST',
				url: `**${ MIGRATION_TASKS_BASE.concat( '/send-files' ) }**`,
			},
			{
				statusCode: 200,
				body: {
					success: false,
				},
			}
		).as( 'stubSendFiles' );
		cy.wait( '@stubTransferStatusCheck' );
		cy.wait( '@stubSendFiles' );
		cy.hash().should( 'eq', '#/error' );
	} );

	it( 'moves to error page if migration fails', () => {
		// Not stubbing report-errors because even if that call fails, we should still
		// redirect to the error page
		cy.intercept(
			{
				method: 'GET',
				url: `**${ MIGRATION_TASKS_BASE.concat( '/status' ) }**`,
			},
			{
				statusCode: 200,
				body: {
					...transferStatus,
					packaged_success: false,
					packaged_failed: true,
				},
			}
		).as( 'stubTransferStatusCheck' );
		cy.wait( '@stubTransferStatusCheck' );
		cy.hash().should( 'eq', '#/error' );
	} );

	it( 'can cancel transfer', () => {
		cy.intercept(
			{
				method: 'POST',
				url: `**${ MIGRATION_TASKS_BASE.concat( '/cancel' ) }**`,
			},
			{
				statusCode: 200,
				body: {},
			}
		).as( 'stubCancelRequest' );
		cy.intercept(
			{
				method: 'GET',
				url: `**${ MIGRATION_CHECK_BASE.concat( '/step' ) }**`,
			},
			{
				statusCode: 200,
				body: {
					...stepResult,
					compatible: true,
				},
			}
		).as( 'stubStepResult' );
		cy.get( '#cancel-transfer-button' ).click();
		cy.wait( '@stubCancelRequest' );
		// Should reload and redirect
		cy.wait( '@stubStepResult' );
		cy.get( '#begin-transfer-button' ).should( 'be.visible' );
	} );
} );
