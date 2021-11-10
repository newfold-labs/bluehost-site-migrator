'use strict';

const autoprefixer = require('autoprefixer');
const browsers = require('@wordpress/browserslist-config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');
const {VueLoaderPlugin} = require('vue-loader');

module.exports = function (env, options) {

	const entry = {
		app: [
			'./source/js/app.js',
			'./source/scss/app.scss'
		]
	};

	const paths = {
		css: 'assets/css/',
		img: 'assets/img/',
		font: 'assets/font/',
		js: 'assets/js/',
		lang: 'languages/',
	};

	const mode = options.mode || 'development';

	const extPrefix = mode === 'production' ? '.min' : '';

	const loaders = {
		css: {
			loader: 'css-loader',
			options: {
				sourceMap: true,
			}
		},
		postCss: {
			loader: 'postcss-loader',
			options: {
				postcssOptions: {
					plugins: [
						autoprefixer({
							overrideBrowserslist: browsers,
							flexbox: 'no-2009',
						}),
					],
					sourceMap: true,
				}
			},
		},
		sass: {
			loader: 'sass-loader',
			options: {
				sourceMap: true,
			},
		},
	};

	return {
		mode,
		entry,
		output: {
			path: path.join(__dirname, '/'),
			filename: `${ paths.js }[name]${ extPrefix }.js`,
		},
		resolve: {
			alias: {
				'@': path.resolve(__dirname, 'source'),
				'vue$': 'development' === mode ? 'vue/dist/vue.runtime.js' : 'vue/dist/vue.runtime.min.js'
			}
		},
		module: {
			rules: [
				{
					test: /\.js|.es6/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: [
								[
									'@babel/preset-env',
									{
										targets: browsers,
									}
								],
							],
							plugins: [
								'transform-class-properties',
							],
						}
					},
					exclude: /(node_modules|bower_components)/,
				},
				{
					test: /\.css$/,
					use: [
						MiniCssExtractPlugin.loader,
						loaders.css,
						loaders.postCss,
					],
					exclude: /(node_modules|bower_components)/,
				},
				{
					test: /\.scss$/,
					use: [
						MiniCssExtractPlugin.loader,
						loaders.css,
						loaders.postCss,
						loaders.sass,
					],
					exclude: /(node_modules|bower_components)/,
				},
				{
					test: /\.(png|jpg|svg)$/,
					type: 'asset/resource',
					generator: {
						filename: 'assets/img/[name][ext]'
					}
				},
				{
					test: /\.vue$/,
					use: 'vue-loader'
				},
				{
					test: /\.html$/,
					type: 'asset/resource'
				},
			]
		},
		plugins: [
			new MiniCssExtractPlugin({
				filename: `${ paths.css }[name]${ extPrefix }.css`,
			}),
			new VueLoaderPlugin(),
		],
		devtool: 'source-map',
	};

};
