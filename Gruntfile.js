module.exports = function( grunt ) {
	'use strict';

	// Load all grunt tasks matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );
	grunt.loadNpmTasks( '@lodder/grunt-postcss' );

	// Show elapsed time
	require( '@lodder/time-grunt' )( grunt );

	// Project configuration
	grunt.initConfig(
		{
			package: grunt.file.readJSON('package.json'),
			dirs: {
				code: 'src/includes',
				assets: 'src/assets',
				lang: 'src/languages',
				templates: 'src/templates',
			},

			glotpress_download: {
				dist: {
					options: {
						domainPath: '<%= dirs.lang %>',
						url: 'https://translate.deep-web-solutions.com/glotpress/',
						slug: 'linked-orders-for-woocommerce',
						textdomain: 'linked-orders-for-woocommerce'
					}
				}
			},
			makepot: {
				dist: {
					options: {
						domainPath: '<%= dirs.languages %>',
						exclude: ['node_modules/.*', 'vendor/.*'],
						potFilename: 'linked-orders-for-woocommerce.pot',
						mainFile: 'bootstrap.php',
						potHeaders: {
							'report-msgid-bugs-to': 'https://github.com/deep-web-solutions/woocommerce-plugins-linked-orders/issues',
							'project-id-version': '<%= package.title %> <%= package.version %>',
							'poedit': true,
							'x-poedit-keywordslist': true,
						},
						processPot: function (pot) {
							delete pot.headers['x-generator'];
							return pot;
						},
						type: 'wp-plugin',
						updateTimestamp: false,
						updatePoFiles: true
					}
				}
			},

			replace: {
				readme_txt: {
					src: ['readme.txt'],
					overwrite: true,
					replacements: [
						{
							from: /Stable tag: (.*)/,
							to: "Stable tag: <%= package.version %>  "
						}
					]
				},
				bootstrap_php: {
					src: ['bootstrap.php'],
					overwrite: true,
					replacements: [
						{
							from: /Version:(\s*)(.*)/,
							to: "Version:$1<%= package.version %>"
						},
						{
							from: /define\( 'DWS_WC_LO_VERSION', '(.*)' \);/,
							to: "define( 'DWS_WC_LO_VERSION', '<%= package.version %>' );"
						}
					]
				}
			}
		}
	);

	grunt.registerTask( 'assets-translations', [ 'makepot', 'glotpress_download' ] );

	grunt.registerTask( 'version_number', [ 'replace:readme_txt', 'replace:bootstrap_php' ] );
	grunt.registerTask( 'build', [ 'assets-translations' ] );
}
