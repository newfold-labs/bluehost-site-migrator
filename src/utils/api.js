import apiFetch from '@wordpress/api-fetch';
import { getGeoLocation } from './geo';

const API_BASE = '/bluehost-site-migrator/v1/';
const MIGRATION_CHECK_BASE = API_BASE.concat( 'migration-check' );
const MIGRATION_TASKS_BASE = API_BASE.concat( 'migration-tasks' );

export const SiteMigratorAPIs = () => {
	return {
		migrationCheck: {
			checkCompatibility: async () => {
				return await apiFetch( {
					path: MIGRATION_CHECK_BASE.concat( '/compatible' ),
					method: 'GET',
				} );
			},
			runMigrationChecks: async () => {
				const geoLocationData = await getGeoLocation();
				return await apiFetch( {
					path: MIGRATION_CHECK_BASE.concat( '/' ),
					method: 'POST',
					data: geoLocationData.data,
				} );
			},
			getCurrentStep: async () => {
				return await apiFetch( {
					path: MIGRATION_CHECK_BASE.concat( '/step' ),
					method: 'GET',
				} );
			},
		},
		migrationTasks: {
			queueMigrationTasks: async () => {
				return await apiFetch( {
					path: MIGRATION_TASKS_BASE.concat( '/' ),
					method: 'POST',
				} );
			},
			getTransferStatus: async () => {
				return await apiFetch( {
					path: MIGRATION_TASKS_BASE.concat( '/status' ),
					method: 'GET',
				} );
			},
		},
	};
};
