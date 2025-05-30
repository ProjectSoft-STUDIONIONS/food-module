
(function (factory) {
	var registeredInModuleLoader;
	if (typeof define === 'function' && define.amd) {
		define(factory);
		registeredInModuleLoader = true;
	}
	if (typeof exports === 'object') {
		module.exports = factory();
		registeredInModuleLoader = true;
	}
	if (!registeredInModuleLoader) {
		var OldCookies = window.Cookies;
		var api = window.Cookies = factory();
		api.noConflict = function () {
			window.Cookies = OldCookies;
			return api;
		};
	}
}(function () {
	function extend () {
		var i = 0;
		var result = {};
		for (; i < arguments.length; i++) {
			var attributes = arguments[ i ];
			for (var key in attributes) {
				result[key] = attributes[key];
			}
		}
		return result;
	}
	function decode (s) {
		return s.replace(/(%[0-9A-Z]{2})+/g, decodeURIComponent);
	}
	function init (converter) {
		function api() {}
		function set (key, value, attributes) {
			if (typeof document === 'undefined') {
				return;
			}
			attributes = extend({
				path: '/'
			}, api.defaults, attributes);
			if (typeof attributes.expires === 'number') {
				attributes.expires = new Date(new Date() * 1 + attributes.expires * 864e+5);
			}
			attributes.expires = attributes.expires ? attributes.expires.toUTCString() : '';
			try {
				var result = JSON.stringify(value);
				if (/^[\{\[]/.test(result)) {
					value = result;
				}
			} catch (e) {}
			value = converter.write ?
				converter.write(value, key) :
				encodeURIComponent(String(value))
					.replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
			key = encodeURIComponent(String(key))
				.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent)
				.replace(/[\(\)]/g, escape);
			var stringifiedAttributes = '';
			for (var attributeName in attributes) {
				if (!attributes[attributeName]) {
					continue;
				}
				stringifiedAttributes += '; ' + attributeName;
				if (attributes[attributeName] === true) {
					continue;
				}
				stringifiedAttributes += '=' + attributes[attributeName].split(';')[0];
			}
			return (document.cookie = key + '=' + value + stringifiedAttributes);
		}
		function get (key, json) {
			if (typeof document === 'undefined') {
				return;
			}
			var jar = {};
			var cookies = document.cookie ? document.cookie.split('; ') : [];
			var i = 0;
			for (; i < cookies.length; i++) {
				var parts = cookies[i].split('=');
				var cookie = parts.slice(1).join('=');
				if (!json && cookie.charAt(0) === '"') {
					cookie = cookie.slice(1, -1);
				}
				try {
					var name = decode(parts[0]);
					cookie = (converter.read || converter)(cookie, name) ||
						decode(cookie);
					if (json) {
						try {
							cookie = JSON.parse(cookie);
						} catch (e) {}
					}
					jar[name] = cookie;
					if (key === name) {
						break;
					}
				} catch (e) {}
			}
			return key ? jar[key] : jar;
		}
		api.set = set;
		api.get = function (key) {
			return get(key, false);
		};
		api.getJSON = function (key) {
			return get(key, true);
		};
		api.remove = function (key, attributes) {
			set(key, '', extend(attributes, {
				expires: -1
			}));
		};
		api.defaults = {};
		api.withConverter = init;
		return api;
	}
	return init(function () {});
}));

(function($) {
	const getDateTime = function(timestamp = 0) {
		let time = new Date(timestamp),
			date = time.getDate(),
			month = time.getMonth() + 1,
			year = time.getFullYear(),
			hour = time.getHours(),
			minute = time.getMinutes(),
			second = time.getSeconds(),
			arrDate = [
				leftPad(date,  2, '0'),
				leftPad(month, 2, '0'),
				String(year)
			],
			arrTime = [
				leftPad(hour,   2, '0'),
				leftPad(minute, 2, '0'),
				leftPad(second, 2, '0')
			];
		return arrDate.join('-') + ' ' + arrTime.join(':');

	},
	leftPad = function (str, len, ch) {
		str = String(str);
		let i = -1;
		if (!ch && ch !== 0) ch = ' ';
		len = len - str.length;
		while (++i < len) {
			str = ch + str;
		}
		return str;
	},
	componentName = `Модуль питания для Evolution CMS`,
	userName = `ProjectSoft`;

	if('object' == typeof $.fancybox) {
		$.fancybox.defaults.transitionEffect = "circular";
		$.fancybox.defaults.transitionDuration = 500;
		$.fancybox.defaults.lang = "ru";
		$.fancybox.defaults.i18n.ru = {
			CLOSE: "Закрыть",
			NEXT: "Следующий",
			PREV: "Предыдущий",
			ERROR: "Запрошенный контент не может быть загружен.<br/>Повторите попытку позже.",
			PLAY_START: "Начать слайдшоу",
			PLAY_STOP: "Остановить слайдшоу",
			FULL_SCREEN: "Полный экран",
			THUMBS: "Миниатюры",
			DOWNLOAD: "Скачать",
			SHARE: "Поделиться",
			ZOOM: "Увеличить"
		};
		$(document).on('click', 'a[data-file]', function(e) {
			let base = window.location.origin,
				element = e.target,
				href = element.getAttribute('data-file'),
				url = base + href,
				arr = href.split('.'),
				ext = arr.at(-1).toLowerCase(),
				go, 
				options = {
					afterShow : function( instance, current ) {
						$(".fancybox-content").css({
							height: '100% !important',
							overflow: 'hidden'
						}).addClass(`${ext}_viewer`);
					},
					afterLoad : function( instance, current ) {
						$(".fancybox-content").css({
							height: '100% !important',
							overflow: 'hidden'
						}).addClass(`${ext}_viewer`);
					},
					afterClose: function() {
						Cookies.remove('pdfjs.history', { path: '' });
						window.localStorage.removeItem('pdfjs.history');
					}
				};
			switch (ext){
				case "pdf":
					go = window.location.origin + '/viewer/pdf_viewer/?file=' + href;
					options = {
						src: go,
						opts : options
					};
					e.preventDefault();
					$.fancybox.open(options);
					return !1;
					break;
				case "xlsx":
					go = window.location.origin + '/viewer/xlsx_viewer/?file=' + href;
					options = {
						src: go,
						type: 'iframe',
						opts : options
					};
					e.preventDefault();
					$.fancybox.open(options);
					return !1;
					break;
			}
			return !1;
		});
		$(document).on('click', 'a[data-mod]', function(e) {
			let element = e.target,
				form_mode = document.querySelector('[name=modifed] [name=mode]'),
				form_file = document.querySelector('[name=modifed] [name=file]'),
				form_newfile = document.querySelector('[name=modifed] [name=newfile]'),
				file = element.getAttribute('data-mod'),
				newfile = element.getAttribute('data-newfile'),
				mode = element.getAttribute('data-mode');
			switch(mode) {
				case "delete":
					e.preventDefault();
					form_mode.value = mode;
					form_file.value = file;
					form_newfile.value = "";
					if(confirm(`Удалить файл?\n\n${file}`)) {
						document.modifed.submit();
					}else{
						form_mode.value = "";
						form_file.value = "";
						form_newfile.value = "";
					}
					return !1;
					break;
				case "rename":
					e.preventDefault();
					// На переименование вывести только имя файла
					let fname = file.split("/").pop();
					const segments = file.split('.');
					const fileExtension = segments.pop();
					let fileName = segments.join('.');
					nwfile = prompt("Укажите новое имя для файла:", fileName);
					if(!nwfile) {
						return !1
					}
					form_mode.value = mode;
					form_file.value = file;
					form_newfile.value = nwfile + `.${fileExtension}`;
					if(form_file.value != form_newfile.value){
						document.modifed.submit();
					}else{
						form_mode.value = "";
						form_file.value = "";
						form_newfile.value = "";
						document.modifed.reset();
					}
					return !1;
					break;
			}
		});
	}

	window.uploadFiles = function(el) {
		let p = $("#p_uploads"),
			files = [...el.files],
			out = [], str = "";
		for (let a of files){
			const regex = /[^.]+$/;
			let m;
			if ((m = regex.exec(a.name)) !== null) {
				let ex = m[0].toLowerCase();
				if(ex == "xlsx" || ex == "pdf"){
					out.push(a.name);
				}else{
					p.html("");
					alert("Нельзя загрузить данный тип файла!\n" + a.type);
					document.upload.reset();
					return !1;
				}
			}
		}
		p.html(out.join("<br>"));
		return !1;
	}

	// Если есть dir, значит список файлов
	if(FOOD_FILE_PATH) {
		const url = `${location.origin}/${FOOD_FILE_PATH}/`;
		//${FOOD_FILE_PATH}
		let table = new DataTable(`#table`, {
			// Колонки
			columns: [
				{ name: 'file' },
				{ name: 'permission' },
				{ name: 'date' },
				{ name: 'size' },
				{ name: 'actions' }
			],
			// Настройки по колонкам
			columnDefs : [
				// Разрешено для первой колонки поиск, сортировка
				{ 
					'searchable'    : !0, 
					'targets'       : [0],
					'orderable'     : !0
				},
				// Запрещено для последующих колонок поиск, сортировка
				{ 
					'searchable'    : !1, 
					'targets'       : [1,2,3,4],
					'orderable'     : !1
				},
			],
			// Разрешена сортировка
			ordering: !0,
			// Разрешаем запоминание всех свойств
			stateSave: !0,
			stateSaveCallback: function (settings, data) {
				localStorage.setItem(
					'DataTables_' + settings.sInstance + '_' + FOOD_FILE_PATH,
					JSON.stringify(data)
				);
			},
			stateLoadCallback: function (settings) {
				return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance + '_' + FOOD_FILE_PATH));
			},
			lengthMenu: [
				[10, 25, 50, 100, -1],
				['по 10', 'по 25', 'по 50', 'по 100', 'Все']
			],
			layout: {
				topStart: [
					'pageLength',
					'search'
				],
				topEnd: {
					buttons: [
						// Кнопка экспорта XLSX
						{
							extend: 'excel',
							text: 'Экспорт в XLSX',
							download: '',
							filename: `Экспорт ${FOOD_FILE_PATH} в XLSX`,
							title: `Директория ${url}`,
							sheetName: `${FOOD_FILE_PATH}`,
							customize: function (xlsx) {
								let date = new Date();
								let dateISO = date.toISOString();
								// Создаём xml файлы для свойств документа (метатеги)
								xlsx["_rels"] = {};
								xlsx["_rels"][".rels"] = $.parseXML(`<?xml version="1.0" encoding="UTF-8" standalone="yes"?>` +
									`<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">` +
										`<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>` +
										`<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>` +
										`<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>` +
									`</Relationships>`);
								xlsx["docProps"] = {};
								xlsx["docProps"]["core.xml"] = $.parseXML(`<?xml version="1.0" encoding="UTF-8" standalone="yes"?>` +
									`<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">` +
										// Заголовок
										`<dc:title>Директория ${url}</dc:title>` +
										// Тема
										`<dc:subject>Директория ${url}</dc:subject>` +
										// Создатель
										`<dc:creator>${componentName}</dc:creator>` +
										// Теги
										`<cp:keywords />` +
										// Описание
										`<dc:description>${componentName}</dc:description>` +
										// Последнее изменение
										`<cp:lastModifiedBy>${componentName}</cp:lastModifiedBy>` +
										// Дата создания - время создания
										`<dcterms:created xsi:type="dcterms:W3CDTF">${dateISO}</dcterms:created>` +
										// Дата изменеия - время создания
										`<dcterms:modified xsi:type="dcterms:W3CDTF">${dateISO}</dcterms:modified>` +
										// Категория
										`<cp:category>${FOOD_FILE_PATH}</cp:category>` +
									`</cp:coreProperties>`);
								xlsx["docProps"]["app.xml"] = $.parseXML(
									`<?xml version="1.0" encoding="UTF-8" standalone="yes"?>` +
									`<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">` +
										`<Application>Microsoft Excel</Application>` +
										`<DocSecurity>0</DocSecurity>` +
										`<ScaleCrop>false</ScaleCrop>` +
										`<HeadingPairs>` +
											`<vt:vector size="2" baseType="variant">` +
												`<vt:variant>` +
													`<vt:lpstr>Листы</vt:lpstr>` +
												`</vt:variant>` +
												`<vt:variant>` +
													`<vt:i4>1</vt:i4>` +
												`</vt:variant>` +
											`</vt:vector>` +
										`</HeadingPairs>` +
										`<TitlesOfParts>` +
											`<vt:vector size="1" baseType="lpstr">` +
												`<vt:lpstr>${FOOD_FILE_PATH}</vt:lpstr>` +
											`</vt:vector>` +
										`</TitlesOfParts>` +
										// Руководитель - автор компонента
										`<Manager>${userName}</Manager>` +
										// Организация - автор компонента
										`<Company>${userName}</Company>` +
										`<LinksUpToDate>false</LinksUpToDate>` +
										`<SharedDoc>false</SharedDoc>` +
										`<HyperlinkBase>${url}</HyperlinkBase>` +
										`<HyperlinksChanged>false</HyperlinksChanged>` +
										`<AppVersion>16.0300</AppVersion>` +
									`</Properties>`
								);
								let contentType = xlsx["[Content_Types].xml"];
								let Types = contentType.querySelector('Types');

								let Core = contentType.createElement('Override');
								Core.setAttribute("PartName", "/docProps/core.xml");
								Core.setAttribute("ContentType", "application/vnd.openxmlformats-package.core-properties+xml");
								Types.append(Core);

								let App = contentType.createElement('Override');
								App.setAttribute("PartName", "/docProps/app.xml");
								App.setAttribute("ContentType", "application/vnd.openxmlformats-officedocument.extended-properties+xml");
								Types.append(App);

								xlsx["[Content_Types].xml"] = contentType;
							},
							action: function (e, dt, node, config, cb) {
								DataTable.ext.buttons.excelHtml5.action.call(
									this,
									e,
									dt,
									node,
									config,
									cb
								);
							}
						},
						// Кнопка экспорта PDF
						{
							extend: 'pdf',
							text: 'Экспорт в PDF',
							download: '',
							filename: `Экспорт ${FOOD_FILE_PATH} в PDF`,
							title: `Директория ${url}`,
							// Кастомизируем вывод
							customize: function (doc) {
								let date = new Date();
								let dateISO = date.toISOString();
								let title = [
									`Меню ежедневного питания.`,
									`Директория ${url}`
								];
								// Используемый язык экспорта
								doc.language = 'ru-RU';
								// Метатеги экспорта
								doc.info = {
									title: title.join(' '),
									author: componentName,
									subject: title.join(' '),
									keywords: title.join(' '),
									creator: `${componentName}`,
									producer: `${userName}`,
									modDate: `${dateISO}`
								};
								// Колонтитулы
								// Верхний
								doc.header = {
									columns: [
										{
											text: `${url}`,
											margin: [15, 15, 15, 15],
											alignment: 'left'
										},
										{
											text: getDateTime((new Date()).getTime()),
											margin: [15, 15, 15, 15],
											alignment: 'right'
										}
									]
								};
								// Нижний
								doc.footer = function(currentPage, pageCount) {
									return [
										{
											text: currentPage.toString() + ' из ' + pageCount,
											margin: [15, 15, 15, 15],
											alignment: 'center'
										}
									];
								};
								// Текст контента.
								doc.content[0].text = title.join('\r\n');
							},
							action: function (e, dt, node, config, cb) {
								DataTable.ext.buttons.pdfHtml5.action.call(
									this,
									e,
									dt,
									node,
									config,
									cb
								);
							}
						}
					]
				}
			},
			language: {
				url: `${FOOD_MOD_PATH}js/ru_RU.json`,
			}
		});
		setTimeout(() => {
			$('button.buttons-html5').each(function(){
				$(this).removeClass('btn-default').addClass('btn-secondary');
			})
		}, 100);
		
	}

	setTimeout(function() {
		let p = $("#ManageFiles > .alert");
		p.animate({
			height: 0
		},
		{
			duration: 300,
			easing: "linear",
			complete: function(){
				p.html("");
				p.removeAttr("style");
			},
			queue: false
		});
	}, 5000);
})(jQuery);