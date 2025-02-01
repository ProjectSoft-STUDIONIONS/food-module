
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
			return get(key, false /* read as raw */);
		};
		api.getJSON = function (key) {
			return get(key, true /* read as json */);
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
	if(typeof $.fancybox == 'object') {
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
		$(document).on('click', 'a[data-file]', e => {
			let base = window.location.origin,
				element = e.target,
				href = element.getAttribute('data-file'),
				url = base + href,
				arr = href.split('.'),
				ext = arr.at(-1).toLowerCase(),
				go, 
				options = {
					src: go,
					opts : {
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
		$(document).on('click', 'a[data-mod]', e => {
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
					const [...segments] = file.split('.');
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

	setTimeout(() => {
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
}(jQuery));