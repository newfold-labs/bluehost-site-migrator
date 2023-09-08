// A utility function to call an API and handle all errors

export const apiCall = async ( { apiCallFunc, apiCallParams } ) => {
	try {
		return await apiCallFunc( apiCallParams );
	} catch ( error ) {
		return { error, failed: true };
	}
};
