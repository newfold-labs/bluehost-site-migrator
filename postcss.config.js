const tailwind = require( './tailwind.config' );
const { resolve } = require( 'path' );

module.exports = () => ( {
	ident: 'postcss',
	plugins: [
		require( 'tailwindcss' )( {
			...tailwind,
			config: resolve( __dirname, 'tailwind.config.js' ),
		} ),
		require( 'autoprefixer' )( { grid: true } ),
	],
} );
