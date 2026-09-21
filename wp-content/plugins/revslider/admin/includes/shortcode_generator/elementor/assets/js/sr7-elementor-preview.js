/**
 * Elementor Preview Controller
 */
class WidgetHandlerClass extends elementorModules.frontend.handlers.Base {
	getDefaultSettings() {
		return {
			selectors: {
				selectButtonSelector: '.sr--block--preview--select',
				editButtonSelector: '.sr--block--preview--edit'
			},
		};
	}
	getDefaultElements() {
		const selectors = this.getSettings('selectors');
		return {
			$selectButtonSelector: this.$element.find(selectors.selectButtonSelector),
			$editButtonSelector: this.$element.find(selectors.editButtonSelector),
		};
	}
	bindEvents() {
		this.elements.$selectButtonSelector.on('click', this.onSelectButtonClick.bind(this));
		this.elements.$editButtonSelector.on('click', this.onEditButtonClick.bind(this));
	}
	onSelectButtonClick(event) {
		event.preventDefault();
		setTimeout(() => window.parent.document.querySelector('#elementor-controls button[data-event="sr7.selectModule"]').click(), 1000);
	}
	onEditButtonClick(event) {
		event.preventDefault();
		setTimeout(() => window.parent.document.querySelector('#elementor-controls button[data-event="sr7.editModule"]').click(), 1000);
	}
}

function checkElementorModule($element) {
	const id = $element.attr('data-id');
	if (!elementorFrontend.isEditMode() || !id || window.parent === window) return;

	const parent = window.parent;
	parent.SR7 ??= {};
	parent.SR7.E ??= {};
	const controller = parent.SR7.B?.elementorShortcode;
	if (controller?.checkModule) {
		controller.checkModule(id);
	} else {
		parent.SR7.E.elementorChecks ??= [];
		if (!parent.SR7.E.elementorChecks.includes(id)) parent.SR7.E.elementorChecks.push(id);
	}
}

window.addEventListener('elementor/frontend/init', () => {
	elementorFrontend.hooks.addAction('frontend/element_ready/slider_revolution.default', $element => {
		checkElementorModule($element);

		// Post upgrade force reload preview
		if ($element.find(".sr--block--force--reload").length !== 0) {
			$element.trigger("click");
		}
		elementorFrontend.elementsHandler.addHandler(WidgetHandlerClass, {
			$element,
		});
	});
});
