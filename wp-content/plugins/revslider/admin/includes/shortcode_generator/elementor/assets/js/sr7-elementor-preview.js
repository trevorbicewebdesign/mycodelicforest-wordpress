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

window.addEventListener('elementor/frontend/init', () => {
	elementorFrontend.hooks.addAction('frontend/element_ready/slider_revolution.default', $element => {
		// Post upgrade force reload preview
		if ($element.find(".sr--block--force--reload").length !== 0) {
			$element.trigger("click");
		}
		elementorFrontend.elementsHandler.addHandler(WidgetHandlerClass, {
			$element,
		});
	});
});