module.exports = function(grunt) {
	var fs = require('fs'),
		chalk = require('chalk'),
		PACK = grunt.file.readJSON('package.json');

	var gc = {
		versions: `${PACK.version}`,
		default: [
			"less",
			"autoprefixer",
			"replace",
			"cssmin",
			"concat",
			"uglify",
			"lineending",
			"compress"
		]
	};

	require('load-grunt-tasks')(grunt);
	require('time-grunt')(grunt);

	grunt.initConfig({
		globalConfig : gc,
		pkg : PACK,
		less: {
			css: {
				options : {
					compress: false,
					ieCompat: false,
					plugins: []
				},
				files : {
					'assets/modules/food-module/css/main.css' : [
						'bower_components/datatables.net-bs/css/dataTables.bootstrap.css',
						'src/main.less'
					]
				}
			},
		},
		autoprefixer:{
			options: {
				browsers: [
					"last 4 version"
				],
				cascade: true
			},
			css: {
				files: {
					'assets/modules/food-module/css/main.css' : [
						'assets/modules/food-module/css/main.css'
					]
				}
			},
		},
		replace: {
			css: {
				options: {
					patterns: [
						{
							match: /\/\*.+?\*\//gs,
							replacement: ''
						},
						{
							match: /\r?\n\s+\r?\n/g,
							replacement: '\n'
						}
					]
				},
				files: [
					{
						expand: true,
						flatten : true,
						src: [
							'assets/modules/food-module/css/main.css'
						],
						dest: 'assets/modules/food-module/css/',
						filter: 'isFile'
					},
				]
			},
		},
		cssmin: {
			options: {
				mergeIntoShorthands: false,
				roundingPrecision: -1
			},
			minify: {
				files: {
					'assets/modules/food-module/css/main.min.css' : ['assets/modules/food-module/css/main.css'],
				}
			},
		},
		lineending: {
			dist: {
				options: {
					eol: 'lf'
				},
				files: [
					{
						expand: true,
						cwd: 'assets',
						src: ['**/*.{css,js,php,json,html}'],
						dest: 'assets'
					}
				]
			}
		},
		concat: {
			options: {
				separator: "\n",
			},
			appjs: {
				src: [
					'bower_components/js-cookie/src/js.cookie.js',
					'bower_components/pdfmake/build/pdfmake.js',
					'bower_components/jszip/dist/jszip.js',
					'bower_components/pdfmake/build/vfs_fonts.js',
					'bower_components/datatables.net/js/dataTables.js',
					'bower_components/datatables.net-buttons/js/dataTables.buttons.js',
					'bower_components/datatables.net-buttons/js/buttons.html5.js',
					//'bower_components/datatables.net-select/js/dataTables.select.js',
					'bower_components/datatables.net-bs/js/dataTables.bootstrap.js',
					'bower_components/datatables.net-buttons-bs/js/buttons.bootstrap.js',
					'bower_components/datatables.net-select-bs/js/select.bootstrap.js'

				],
				dest: 'assets/modules/food-module/js/app.js'
			},
			main: {
				src: [
					'src/main.js'
				],
				dest: 'assets/modules/food-module/js/main.js'
			},
		},
		uglify: {
			options: {
				sourceMap: false,
				compress: {
					drop_console: false
				},
				output: {
					ascii_only: true
				}
			},
			app: {
				files: [
					{
						expand: true,
						flatten : true,
						src: [
							'assets/modules/food-module/js/main.js',
						],
						dest: 'assets/modules/food-module/js',
						filter: 'isFile',
						rename: function (dst, src) {
							return dst + '/' + src.replace('.js', '.min.js');
						}
					},
					{
						expand: true,
						flatten : true,
						src: [
							'assets/modules/food-module/js/app.js',
						],
						dest: 'assets/modules/food-module/js',
						filter: 'isFile',
						rename: function (dst, src) {
							return dst + '/' + src.replace('.js', '.min.js');
						}
					},
				]
			}
		},
		compress: {
			main: {
				options: {
					archive: 'food-module.zip'
				},
				files: [
					{
						expand: true,
						cwd: 'bower_components/food/',
						src: [
							'icons-full/**',
							'viewer/**',
							'food/**',
							'food-individual/**',
							'icons-full/.*',
							'viewer/.*',
							'food/.*',
							'food-individual/.*'
						],
						dest: 'food-module/'
					},
					{
						expand: true,
						cwd: '.',
						src: [
							'assets/**',
							'install/**',
						],
						dest: 'food-module/'
					}
				],
			},
		},
	});
	grunt.registerTask('default',	gc.default);

	// Удаление прошлого архива
	if(fs.existsSync('food-module.zip')) {
		fs.unlinkSync('food-module.zip');
		console.log('\n' + chalk.bgWhite.red('  Delete archive: ') + chalk.bgWhite.blue('food-module.zip  ') + '\n');
	}
};
