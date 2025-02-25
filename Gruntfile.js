module.exports = function(grunt) {
	var fs = require('fs'),
		PACK = grunt.file.readJSON('package.json');

	var gc = {
		versions: `${PACK.version}`,
		default: [
			"less",
			"autoprefixer",
			"replace",
			"cssmin",
			"copy",
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
		copy: {
			favicons: {
				expand: true,
				cwd: 'src',
				src: [
					'*.js'
				],
				dest: "assets/modules/food-module/js/",
			},
		},
		compress: {
			main: {
				options: {
					archive: 'food-uploader.zip'
				},
				files: [
					{
						expand: true,
						cwd: '.',
						src: [
							'assets/**',
							'install/**',
						],
						dest: '/food-uploader/'
					},
				],
			},
		},
	});
	grunt.registerTask('default',	gc.default);
};
