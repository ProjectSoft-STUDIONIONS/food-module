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
			"copy",
			"compress",
			"pug",
		],
		src: [
			'bower_components/js-cookie/src/js.cookie.js',
			'bower_components/pdfmake/build/pdfmake.js',
			'bower_components/jszip/dist/jszip.js',
			'bower_components/pdfmake/build/vfs_fonts.js',
			'bower_components/datatables.net/js/dataTables.js',
			'bower_components/datatables.net-buttons/js/dataTables.buttons.js',
			'bower_components/datatables.net-buttons/js/buttons.html5.js',
			'bower_components/datatables.net-buttons/js/buttons.print.js',
			'bower_components/datatables.net-buttons/js/buttons.colVis.js',
			'bower_components/datatables.net-select/js/dataTables.select.js',
			'bower_components/datatables.net-bs/js/dataTables.bootstrap.js',
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
						'bower_components/datatables.net-buttons-bs/css/buttons.bootstrap.css',
						'bower_components/webfont-food/dest/css/foodIcon.css',
						'src/main.less'
					],
					'src/css/main.css': [
						'src/css/main.less'
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
					],
					'src/css/main.css': [
						'src/css/main.css'
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
					{
						expand: true,
						flatten : true,
						src: [
							'src/css/main.css'
						],
						dest: 'src/css/',
						filter: 'isFile'
					},
				]
			},
			module: {
				options: {
					patterns: [
						{
							match: /\/\*.*\*\//gs,
							replacement: `/**
 * FoodModuleMenu
 *
 * Модуль для загрузки файлов ежедневного питания школы.
 *
 * @category     module
 * @version      ${gc.versions}
 * @internal     @properties &folders=Директории для загрузки;text;food &autodelete=Автоудаление;list;true,false;true &autodelete_year=Удалять файлы старше лет;list;1,2,3,4,5;2
 * @internal     @modx_category Manager and Admin
 * @homepage     https://github.com/ProjectSoft-STUDIONIONS/food-module#readme
 * @license      https://github.com/ProjectSoft-STUDIONIONS/food-module/blob/master/LICENSE MIT License (MIT)
 * @reportissues https://github.com/ProjectSoft-STUDIONIONS/food-module/issues
 * @author       Чернышёв Андрей aka ProjectSoft <projectsoft2009@yandex.ru>
 * @lastupdate   ${grunt.template.today('yyyy-mm-dd')}
 */

/**
 * FoodModuleMenu
 *
 * Модуль для загрузки файлов ежедневного питания школы.
 *
 * @category     module
 * @version      ${gc.versions}
 * @internal     @properties &folders=Директории для загрузки;text;food &autodelete=Автоудаление;list;true,false;true &autodelete_year=Удалять файлы старше лет;list;1,2,3,4,5;2
 * @internal     @modx_category Manager and Admin
 * @homepage     https://github.com/ProjectSoft-STUDIONIONS/food-module#readme
 * @license      https://github.com/ProjectSoft-STUDIONIONS/food-module/blob/master/LICENSE MIT License (MIT)
 * @reportissues https://github.com/ProjectSoft-STUDIONIONS/food-module/issues
 * @author       Чернышёв Андрей aka ProjectSoft <projectsoft2009@yandex.ru>
 * @lastupdate   ${grunt.template.today('yyyy-mm-dd')}
 */`
						}
					]
				},
				files: [
					{
						expand: true,
						flatten : true,
						src: [
							'install/assets/modules/foodmodule.tpl'
						],
						dest: 'install/assets/modules/',
						filter: 'isFile'
					},
				]
			},
			plugin: {
				options: {
					patterns: [
						{
							match: /\/\*.*\*\//gs,
							replacement: `/**
 * FoodModuleMenu
 *
 * Плагин встраивания пункта меню для FoodModule.
 *
 * @category     plugin
 * @version      ${gc.versions}
 * @package      evo
 * @internal     @events OnManagerMenuPrerender,OnManagerMainFrameHeaderHTMLBlock
 * @internal     @modx_category Manager and Admin
 * @internal     @properties &id_module=ID модуля FoodModule;int;0;0 &title=Заголовок пункта меню;text;;; &sort=Позиция пункта;int;0;0;0
 * @internal     @installset base
 * @internal     @disabled 0
 * @homepage     https://github.com/ProjectSoft-STUDIONIONS/food-module#readme
 * @license      https://github.com/ProjectSoft-STUDIONIONS/food-module/blob/master/LICENSE MIT License (MIT)
 * @reportissues https://github.com/ProjectSoft-STUDIONIONS/food-module/issues
 * @author       Чернышёв Андрей aka ProjectSoft <projectsoft2009@yandex.ru>
 * @lastupdate   ${grunt.template.today('yyyy-mm-dd')}
 */

/**
 * FoodModuleMenu
 *
 * Плагин встраивания пункта меню для FoodModule.
 *
 * @category     plugin
 * @version      ${gc.versions}
 * @package      evo
 * @internal     @events OnManagerMenuPrerender,OnManagerMainFrameHeaderHTMLBlock
 * @internal     @modx_category Manager and Admin
 * @internal     @properties &id_module=ID модуля FoodModule;int;0;0 &title=Заголовок пункта меню;text;;; &sort=Позиция пункта;int;0;0;0
 * @internal     @installset base
 * @internal     @disabled 0
 * @homepage     https://github.com/ProjectSoft-STUDIONIONS/food-module#readme
 * @license      https://github.com/ProjectSoft-STUDIONIONS/food-module/blob/master/LICENSE MIT License (MIT)
 * @reportissues https://github.com/ProjectSoft-STUDIONIONS/food-module/issues
 * @author       Чернышёв Андрей aka ProjectSoft <projectsoft2009@yandex.ru>
 * @lastupdate   ${grunt.template.today('yyyy-mm-dd')}
 */`
						}
					]
				},
				files: [
					{
						expand: true,
						flatten : true,
						src: [
							'install/assets/plugins/foodplugin.tpl'
						],
						dest: 'install/assets/plugins/',
						filter: 'isFile'
					},
				]
			}
		},
		cssmin: {
			options: {
				mergeIntoShorthands: false,
				roundingPrecision: -1
			},
			minify: {
				files: {
					'assets/modules/food-module/css/main.min.css' : ['assets/modules/food-module/css/main.css'],
					'src/css/main.css': ['src/css/main.css'],
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
			appjs: {
				options: {
					separator: "\n",
					banner: `/**
 * ` + gc.src.join(`\n * `) + `
 *
 * Last Update: ${grunt.template.today('yyyy-mm-dd HH:MM:ss Z')}
 */
 `,
				},
				files: {
					'assets/modules/food-module/js/app.js': gc.src,
				},
				//src: gc.src,
				//dest: 'assets/modules/food-module/js/app.js'
			},
			main: {
				options: {
					separator: "\n",
					banner: `/**
 * Скрипт модуля FoodModuleMenu v${gc.versions} для Evolution CMS
 * ${PACK.description}
 * Актуально для сайтов школ России
 * Автор: Чернышёв Андрей aka ProjectSoft <projectsoft2009@yandex.ru>
 * GitHub: ${PACK.homepage}
 * Last Update: ${grunt.template.today('yyyy-mm-dd HH:MM:ss Z')}
 */
`,
				},
				files: {
					'assets/modules/food-module/js/main.js': ['src/main.js'],
				},
				//src: ['src/main.js'],
				//dest: 'assets/modules/food-module/js/main.js'
			},
		},
		uglify: {
			app: {
				options: {
					sourceMap: false,
					compress: {
						drop_console: false
					},
					output: {
						ascii_only: true,
						preamble: `/**
 * ` + gc.src.join(`\r\n * `) + `
 *
 * Last Update: ${grunt.template.today('yyyy-mm-dd HH:MM:ss Z')}
 */`
					},
				},
				files: [
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
			},
			main: {
				options: {
					sourceMap: false,
					compress: {
						drop_console: false
					},
					output: {
						ascii_only: true,
						preamble: `/**
 * Скрипт модуля FoodModuleMenu v${gc.versions} для Evolution CMS
 * Актуально для сайтов школ России
 * Автор: Чернышёв Андрей aka ProjectSoft <projectsoft2009@yandex.ru>
 * GitHub: ${PACK.homepage}
 * Last Update: ${grunt.template.today('yyyy-mm-dd HH:MM:ss Z')}
 */`
					},
				},
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
				]
			}
		},
		copy: {
			main: {
				files: [
					{
						expand: true,
						cwd: 'bower_components/webfont-food/dest/fonts',
						src: ['*.*'],
						dest: 'assets/modules/food-module/fonts/',
					},
				],
			},
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
							'icons-full/.*',
							'viewer/.*',
							'food/.*',
						],
						dest: 'food-module/'
					},
					{
						expand: true,
						cwd: '.',
						src: [
							'assets/**',
							'assets/**/.*',
							'install/**',
						],
						dest: 'food-module/'
					}
				],
			},
		},
		pug: {
			docs: {
				options: {
					doctype: 'html',
					client: false,
					pretty: '',
					separator:  '',
					data: function(dest, src) {
						return {};
					}
				},
				files: [
					{
						expand: true,
						cwd: __dirname + '/src/pug/',
						src: [ 'index.pug' ],
						dest: __dirname + '/docs/',
						ext: '.html'
					},
				]
			}
		},
	});
	grunt.registerTask('default',	gc.default);

	// Удаление прошлого архива
	if(fs.existsSync('food-module.zip')) {
		fs.unlinkSync('food-module.zip');
		console.log('\n' + chalk.bgWhite.red('  Delete archive: ') + chalk.bgWhite.blue('food-module.zip  ') + '\n');
	}
};
