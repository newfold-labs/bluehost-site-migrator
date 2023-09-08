const { defineConfig } = require( 'cypress' );

module.exports = defineConfig( {
	e2e: {
		baseUrl: 'http://localhost:10004',
		requestTimeout: 10000,
	},
	env: {
		username: 'admin',
		password: 'password',
	},
} );
