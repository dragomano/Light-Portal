class YandexTranslate {
	constructor(params) {
		this.baseLang = params.baseLang ?? 'en'
		this.init()
	}

	init() {
		let script = document.createElement('script');
		script.src = `https://translate.yandex.net/website-widget/v1/widget.js?widgetId=ytWidget&pageLang=${this.baseLang}&widgetTheme=light&autoMode=false`;
		document.getElementsByTagName('head')[0].appendChild(script);

		let code = this.getCode();

		this.handleHtml(code);

		this.handleEvent('click', '[data-ya-lang]', (el) => {
			this.setLang(el.getAttribute('data-ya-lang'));

			window.location.reload();
		})
	}

	setLang(lang) {
		localStorage.setItem('yt-widget', JSON.stringify({
			'lang': lang,
			'active': true
		}));
	}

	getCode() {
		return (localStorage["yt-widget"] !== undefined && JSON.parse(localStorage["yt-widget"]).lang !== undefined) ? JSON.parse(localStorage["yt-widget"]).lang : this.baseLang;
	}

	handleHtml(code) {
		document.querySelector('[data-lang-active]').innerHTML = `<div class="lang__code lang_${code}"></div>`;
		document.querySelector(`[data-ya-lang="${code}"]`)?.remove();
	}

	handleEvent(event, selector, handler) {
		document.addEventListener(event, function (e) {
			let el = e.target.closest(selector);
			if (el) handler(el);
		});
	}
}
