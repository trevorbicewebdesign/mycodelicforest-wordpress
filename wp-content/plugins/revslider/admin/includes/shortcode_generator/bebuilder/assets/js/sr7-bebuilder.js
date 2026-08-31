/**
 * BeBuilder controller for Slider Revolution.
 */
(function() {
	"use strict";

	window.SR7 ??= {};
	window.SR7.E ??= {};
	window.SR7.B ??= {};
	window._tpt ??= {};

	const MODULE_SLUG = "slider_plugin";

	const MODULE_TITLE = "Slider Revolution";

    const SR7_ICON = '<svg width="36" height="36" viewBox="0 0 36 36"><g id="NEW_SR_LOGO" data-name="NEW SR LOGO" transform="translate(8 7.493)"><rect id="Rectangle_16" data-name="Rectangle 16" width="36" height="36" rx="5" transform="translate(-8 -7.493)" fill="#5c24ff"/><path id="Path_1" data-name="Path 1" d="M46.311,18.755l2.807,2.81a.2.2,0,0,1-.14.337H40.089a.121.121,0,0,1-.137-.137l-.006-8.912a.188.188,0,0,1,.322-.134l2.938,2.933a.109.109,0,0,0,.185-.006A6.5,6.5,0,0,0,43.9,7.7a6.271,6.271,0,0,0-5.255-2.944q-.123.009-.123-.117l0-3.991a.121.121,0,0,1,.148-.137c5.283.394,9.36,3.746,10.293,8.929a10.885,10.885,0,0,1-2.231,8.781.516.516,0,0,1-.168.145l-.211.114Q46.146,18.589,46.311,18.755Z" transform="translate(-28.331 -0.8)" fill="#fff"/><path id="Path_2" data-name="Path 2" d="M0,1.745V1.46l8.975.014a.124.124,0,0,1,.14.14l0,8.627a.251.251,0,0,1-.431.177L5.974,7.688a.133.133,0,0,0-.225.009C2.037,12.144,5,18.126,10.521,18.714a.168.168,0,0,1,.148.168v3.766a.123.123,0,0,1-.145.14A10.328,10.328,0,0,1,3.94,20.36Q-1.717,15.456.79,7.893A9.566,9.566,0,0,1,2.844,4.8a.151.151,0,0,0-.006-.234Z" transform="translate(-0.8 -1.481)" fill="#fff"/></g></svg>';
    const SELECT_ICON = '<svg width="20" height="14.884" viewBox="0 0 20 14.884"><path d="M81.86-785.116a1.791,1.791,0,0,1-1.314-.547A1.791,1.791,0,0,1,80-786.977V-798.14a1.792,1.792,0,0,1,.547-1.314A1.792,1.792,0,0,1,81.86-800h5.581l1.86,1.86h7.442a1.792,1.792,0,0,1,1.314.547,1.792,1.792,0,0,1,.547,1.314H88.535l-1.86-1.86H81.86v11.163l2.233-7.442H100l-2.4,7.977a1.814,1.814,0,0,1-.686.965,1.846,1.846,0,0,1-1.1.36Zm1.953-1.86h12l1.674-5.581h-12Zm0,0,1.674-5.581Zm-1.953-9.3v0Z" transform="translate(-80 800)"></path></svg>';
    const EDIT_ICON = '<svg width="24" height="16.076" viewBox="0 0 24 16.076"><path d="M70.12-722.121l9.609-9.609a.257.257,0,0,0,.078-.189.257.257,0,0,0-.078-.189L78.6-733.234a.257.257,0,0,0-.189-.078.257.257,0,0,0-.189.078l-9.609,9.609Zm-7.258,2.093a6.921,6.921,0,0,1-3.875-1.148,3.381,3.381,0,0,1-1.295-2.838,3.39,3.39,0,0,1,1.475-2.85,7.982,7.982,0,0,1,4.1-1.325,4.944,4.944,0,0,0,1.892-.456,1.063,1.063,0,0,0,.631-.961,1.408,1.408,0,0,0-.853-1.3,8.2,8.2,0,0,0-2.8-.644l.158-1.724a8.373,8.373,0,0,1,3.947,1.15,2.9,2.9,0,0,1,1.28,2.518,2.6,2.6,0,0,1-1.058,2.183,5.794,5.794,0,0,1-3.071.969,6.949,6.949,0,0,0-2.976.774,1.869,1.869,0,0,0-.992,1.666,1.771,1.771,0,0,0,.826,1.6,5.9,5.9,0,0,0,2.679.646Zm7.529.091L66.432-723.9l10.8-10.79a1.618,1.618,0,0,1,1.2-.506,1.676,1.676,0,0,1,1.2.506l1.557,1.557a1.644,1.644,0,0,1,.512,1.2,1.644,1.644,0,0,1-.512,1.2Zm-3.864.8a.694.694,0,0,1-.69-.2.694.694,0,0,1-.2-.689l.8-3.864,3.959,3.959Z" transform="translate(-57.693 735.192)"></path></svg>';

	const escapeHtml = value => String(value ?? '')
		.replace(/&/g, '&amp;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#039;');

	const escapeAttr = value => escapeHtml(value).replace(/`/g, '&#96;');

	SR7.B.beBuilderShortcode = {
		inited: false,
		cache: {},
		init() {
			if (this.inited || !document.body) return;
			this.inited = true;
			this.observeSliderSelector();
		},
		observeSliderSelector() {
			setInterval(() => {
				const sliderSelector = document.querySelector(".mfn-form-row.mfn-field-select.slider_plugin.rev");
				if (sliderSelector && !sliderSelector.classList.contains("sr7--bebuilder--enhanced")) {
					this.enhanceSliderSelector(sliderSelector);
				}
			}, 100);
		},
		enhanceSliderSelector(sliderSelector) {
			sliderSelector.classList.add("sr7--bebuilder--enhanced");
			sliderSelector.insertAdjacentHTML("beforeend", this.getEnhancedMarkup());

			const select = sliderSelector.querySelector("select.mfn-field-value");
			const selectButton = sliderSelector.querySelector("button[data-sr7-bebuilder-action='select']");

			if (select.value != "0") {
				this.loadDetails(sliderSelector, select.value);
			}

			selectButton.addEventListener("click", e => {
				SR7.B.shortcode.openSelectModule(data => {
					if (!data?.alias) return;
					select.value = data.alias;
					select.dispatchEvent(new Event("change", {bubbles: true}));
					this.loadDetails(sliderSelector, select.value);
					selectButton.disabled = false;
				});
			});
		},
		injectIntoItemsList(sourceItem) {
			const newItem = sourceItem.cloneNode(true);
			newItem.dataset.title = MODULE_TITLE;
			newItem.dataset.type = MODULE_SLUG;
			newItem.classList.add("mfn-item-slider_revolution");
			newItem.classList.remove("mfn-item-slider_plugin");
			newItem.querySelector("span.title").textContent = MODULE_TITLE;
			sourceItem.parentNode.insertBefore(newItem, sourceItem);
		},
		getEnhancedMarkup() {
			let content = '';
			content += '<div class="sr--bebuilder--module--info">';
			content += '<div class="sr--module--info--logo"></div>';
			content += '<div class="sr--module--info--details"></div>';
			content += '<div class="sr--bebuilder--module--info--buttons">';
			content += '<button class="sr--module--info--button sr--module--info--primary mfn-btn mfn-btn-green btn-copy-text" type="button" data-sr7-bebuilder-action="select">' + SELECT_ICON + "Select Module</button>";
			content += '<button class="sr--module--info--button mfn-btn" type="button" data-sr7-bebuilder-action="edit">' + EDIT_ICON + "Edit</button>";
			content += '</div>';
			content += '</div>';
			return content;
		},
		loadDetails(sliderSelector, alias) {
			if (this.cache[alias]) {
				this.updateDetails(sliderSelector, this.cache[alias]);
			} else {
				SR7.B.shortcode.checkDepsLoaded()
					.then(() => SR7.B.shortcode.loadModule(alias))
					.then(data => {
						this.cache[alias] = data;
						this.updateDetails(sliderSelector, data);
					});
			}
		},
		updateDetails(sliderSelector, data) {
			const info = sliderSelector.querySelector(".sr--module--info--details");
			info.innerHTML = this.getDetailsMarkup(data);

			const element = sliderSelector.closest(".mfn-element-fields-wrapper").dataset.element;
			this.observeWidget(element, data);

			const editButton = sliderSelector.querySelector("button[data-sr7-bebuilder-action='edit']");
			editButton.style.display = "inline-block";
			editButton.onclick = () => SR7.B.shortcode.editModule(data.id);
		},
		getDetailsMarkup(data) {
			const backgroundImage = data.cover?.image ? data.cover.image : SR7.E.plugin_url + "admin/assets/images/sr7placeholder.webp";
			let content = '';
			content += '<div class="sr--module--info--thumb" style="background-image: url(\'' + escapeAttr(backgroundImage) + '\')"></div>';
			content += '<div class="sr--module--info--title"><h6>' + escapeHtml(data.title) + '</h6></div>';
			content += '<div class="sr--module--info--title"><label>Alias:</label> ' + escapeHtml(data.alias) + '</div>';
			content += '<div class="sr--module--info--title"><label>Type:</label> ' + escapeHtml(data.moduleType) + '</div>';
			return content;
		},
		observeWidget(element, data) {
			const iframe = document.getElementById('mfn-vb-ifr');
			const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
			if (!iframeDocument.body.classList.contains("sr7--bebuilder--widget--enhanced")) {
				iframeDocument.body.classList.add("sr7--bebuilder--widget--enhanced");
				const previewStyle = document.getElementById('sr7-bebuilder-css-css');
				if (previewStyle) {
					iframeDocument.head.appendChild(previewStyle.cloneNode(true));
				}
			}
			let h = setInterval(() => {
				const widget = iframeDocument.querySelector(`.${element}`);
				if (widget) {
					clearInterval(h);
					this.enhanceWidget(widget, data);
				}
			}, 100);
		},
		enhanceWidget(widget, data) {
			const widgetInner = widget.querySelector(".mcb-column-inner");
			const widgetReplacedSlider = widgetInner.querySelector(".mfn-rev-slider");
			let shouldInsert = true;
			if (widgetReplacedSlider && widgetReplacedSlider.querySelector('sr7-module').dataset.alias == data.alias) {
				shouldInsert = false;
				if (widgetReplacedSlider.style.display == "none") widgetReplacedSlider.style.display = "block";
			}
			if (shouldInsert) {
				widgetInner.insertAdjacentHTML("beforeend", this.getWidgetMarkup(data));
				if (widgetReplacedSlider) widgetReplacedSlider.style.display = "none";
			}
		},
		getWidgetMarkup(data) {
			let coverStyle = '';
			coverStyle += 'background-image: url(' + (data.cover?.image ? escapeAttr(data.cover.image) : SR7.E.plugin_url + "admin/assets/images/sr7placeholder.webp") + ');';
			coverStyle += 'background-color: ' + (data.cover?.color ? escapeAttr(data.cover.color) : 'inherit') + ';';

			let content = '';
			content += '<div class="sr--block--wrap">';
			content += '	<div class="sr--block--head">';
			content += '		<div class="sr--block--logo">' + SR7_ICON + '</div>';
			content += '		<div class="sr--block--title">' + escapeHtml(data.title) + '</div>';
			content += '	</div>';
			content += '	<div class="revslider sr--block--preview" style="' + coverStyle + '">';
			content += '		<div class="sr--block--thumb" style="' + coverStyle + '"></div>';
			content += '	</div>';
			content += '</div>';
			return content;
		}
	};

	if (document.readyState === "loading") {
		document.addEventListener("readystatechange", function() {
			if (document.readyState === "interactive" || document.readyState === "complete") {
				SR7.B.beBuilderShortcode.init();
			}
		});
	} else {
		SR7.B.beBuilderShortcode.init();
	}

	_tpt.R ??= {};
	_tpt.R.beBuilderShortcode = _tpt.extend ? _tpt.extend(_tpt.R.beBuilderShortcode, {status: 2, version: '1.0'}) : {status: 2, version: '1.0'};
})();
