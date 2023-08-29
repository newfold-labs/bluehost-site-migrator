import { apiCall } from './apiCall';

export async function getGeoLocation() {
	return await apiCall( {
		apiCallFunc: fetch,
		apiCallParams: 'https://hiive.cloud/workers/geolocation/',
	} );
}
