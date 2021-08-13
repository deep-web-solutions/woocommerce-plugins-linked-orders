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

			clean: {
				css: ['<%= dirs.assets %>/dist/css/**/*.css', '<%= dirs.assets %>/dist/css/**/*.map'],
				js: ['<%= dirs.assets %>/dist/js/**/*.js', '<%= dirs.assets %>/dist/js/**/*.map']
			},
			watch: {
				scripts: {
					files: ['<%= dirs.assets %>/dev/**/*.ts'],
					tasks: ['assets-typescript'],
					options: {
						interrupt: true,
					}
				},
				styles: {
					files: ['<%= dirs.assets %>/dev/**/*.scss'],
					tasks: ['assets-scss'],
					options: {
						interrupt: true,
					}
				}
			},

			babel: {
				options: {
					sourceMap: true
				},
				dist: {
					files: [{
						expand: true,
						cwd: '<%= dirs.assets %>/dev/ts/',
						src: ['**/*.ts'],
						dest: '<%= dirs.assets %>/dist/js/',
						ext: '.js'
					}]
				}
			},
			sass: {
				dist: {
					files: [{
						expand: true,
						cwd: '<%= dirs.assets %>/dev/scss/',
						src: ['**/*.scss'],
						dest: '<%= dirs.assets %>/dist/css/',
						ext: '.css'
					}]
				}
			},

			postcss: {
				options: {
					map: {
						inline: false
					},
					processors: [
						require('autoprefixer')({overrideBrowserslist: ['last 2 versions', '> 1%']}),
						require('cssnano')()
					]
				},
				dist: {
					files: [{
						expand: true,
						cwd: '<%= dirs.assets %>/dist/css/',
						src: ['**/*.css'],
						dest: '<%= dirs.assets %>/dist/css/',
						ext: '.min.css'
					}]
				}
			},
			uglify: {
				options: {
					mangle: {
						reserved: ['jQuery']
					},
					output: {
						comments: /\<\/?fs_premium_only\>/i,
					},
					extractComments: true,
					sourceMap: true
				},
				dist: {
					files: [{
						expand: true,
						cwd: '<%= dirs.assets %>/dist/',
						src: ['**/*.js'],
						dest: '<%= dirs.assets %>/dist/',
						ext: '.min.js'
					}]
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

	grunt.registerTask( 'assets-typescript', [ 'clean:js', 'babel', 'uglify' ] );
	grunt.registerTask( 'assets-scss', [ 'clean:css', 'sass', 'postcss' ] );
	grunt.registerTask( 'assets-translations', [ 'makepot', 'glotpress_download' ] );

	grunt.registerTask( 'version_number', [ 'replace:readme_txt', 'replace:bootstrap_php' ] );
	grunt.registerTask( 'build', [ 'assets-typescript', 'assets-scss', 'assets-translations' ] );
}
