import apiFetch from '@wordpress/api-fetch';
import { getGeoLocation } from './geo';

const API_BASE = '/bluehost-site-migrator/v1/';
const MIGRATION_CHECK_BASE = API_BASE.concat( 'migration-check' );
const MIGRATION_TASKS_BASE = API_BASE.concat( 'migration-tasks' );
const MIGRATION_DATA_BASE = API_BASE.concat( 'migration-data' );

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
				let geoLocationData = await getGeoLocation();
				geoLocationData = await geoLocationData.json();
				return await apiFetch( {
					path: MIGRATION_CHECK_BASE.concat( '/' ),
					method: 'POST',
					data: geoLocationData,
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
			sendPackagedFilesDetails: async () => {
				return await apiFetch( {
					path: MIGRATION_TASKS_BASE.concat( '/send-files' ),
					method: 'POST',
				} );
			},
			reportFailed: async () => {
				return await apiFetch( {
					path: MIGRATION_TASKS_BASE.concat( '/report-errors' ),
					method: 'POST',
				} );
			},
			cancelTransfer: async () => {
				return await apiFetch( {
					path: MIGRATION_TASKS_BASE.concat( '/cancel' ),
					method: 'POST',
				} );
			},
		},
		migrationData: {
			getMigrationData: async () => {
				return await apiFetch( {
					path: MIGRATION_DATA_BASE,
					method: 'GET',
				} );
			},
		},
	};
};
