import stepResult from '../fixtures/stepResult.json';
import migrationData from '../fixtures/migrationData.json';

const API_BASE = '/bluehost-site-migrator/v1/';
const MIGRATION_CHECK_BASE = API_BASE.concat( 'migration-check' );
const MIGRATION_TASKS_BASE = API_BASE.concat( 'migration-tasks' );

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
				body: {
					...stepResult,
					packaged_failed: true,
				},
			}
		).as( 'stubStepResult' );
		cy.visit( '/wp-admin/admin.php?page=bluehost-site-migrator' );
		cy.wait( '@stubStepResult' );
	} );

	it( 'can retry if failed', () => {
		cy.intercept( {
			method: 'POST',
			url: `**${ encodeURIComponent(
				MIGRATION_TASKS_BASE.concat( '/cancel' )
			) }**`,
		} ).as( 'stubCancelCall' );
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
					checked: true,
				},
			}
		).as( 'stubStepResult' );
		// The completed page should be shown
		cy.get( '#retry-transfer-button' ).click();
		cy.wait( '@stubCancelCall' );
		cy.wait( '@stubStepResult' );
		cy.get( '#begin-transfer-button' ).should( 'be.visible' );
	} );
} );
