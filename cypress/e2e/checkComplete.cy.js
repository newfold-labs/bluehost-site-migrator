import stepResult from '../fixtures/stepResult.json';
import migrationData from '../fixtures/migrationData.json';

const API_BASE = '/bluehost-site-migrator/v1/';
const MIGRATION_CHECK_BASE = API_BASE.concat( 'migration-check' );
const MIGRATION_DATA_BASE = API_BASE.concat( 'migration-data' );

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
					packaged_success: true,
				},
			}
		).as( 'stubStepResult' );
		cy.intercept(
			{
				method: 'GET',
				url: `**${ encodeURIComponent( MIGRATION_DATA_BASE ) }**`,
			},
			{
				statusCode: 200,
				fixture: 'migrationData.json',
			}
		).as( 'stubMigrationData' );
		cy.visit( '/wp-admin/admin.php?page=bluehost-site-migrator' );
		cy.wait( '@stubStepResult' );
		cy.wait( '@stubMigrationData' );
	} );

	it( 'renders complete when successful', () => {
		// The completed page should be shown
		cy.get( '#copy-transfer-key-button' ).should( 'be.visible' );
		cy.get( '#migration-id' ).should( ( $migrationIdHeading ) => {
			expect( $migrationIdHeading ).to.contain(
				migrationData.migrationId
			);
		} );
		cy.get( '#country-select' ).should(
			'have.value',
			migrationData.countryCode
		);
	} );
} );
