<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace MEE\Modules\RevsliderModule\RevsliderModuleTrait;

if(!defined('ABSPATH')) exit();

use ET\Builder\FrontEnd\Module\Style;

trait ModuleStylesTrait {

	/**
	 * Minimal style registration for RevsliderModule.
	 *
	 * This is the PHP equivalent of src/components/revslider-module/styles.tsx.
	 *
	 * @param array $args Style arguments passed by the Divi renderer.
	 * @return void
	 */
	public static function module_styles( $args ) {
		$elements = $args['elements'];
		$settings = $args['settings'] ?? [];

		Style::add(
			[
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => [
					$elements->style(
						[
							'attrName'   => 'module',
							'styleProps' => [
								'disabledOn' => [
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								],
							],
						]
					),
					$elements->style(
						[
							'attrName' => 'headline',
						]
					),
					$elements->style(
						[
							'attrName' => 'sliderAlias',
						]
					),
					$elements->style(
						[
							'attrName' => 'content',
						]
					),
				],
			]
		);
	}
}
