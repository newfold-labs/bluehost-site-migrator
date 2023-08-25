import { apiCall } from './apiCall';

export async function getGeoLocation() {
	return await apiCall( {
		apiCallFunc: fetch,
		errorNavigateTo: '/incompatible',
		apiCallParams: '',
		step: 'geoLocation',
	} );
}
