<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace MEE\Modules\RevsliderModule\RevsliderModuleTrait;

if(!defined('ABSPATH')) exit();

use ET\Builder\Packages\Module\Options\Element\ElementScriptData;

trait ModuleScriptDataTrait {

	/**
	 * Pass module option script data to Divi.
	 *
	 * @param array $args Script-data arguments passed by the Divi renderer.
	 * @return void
	 */
	public static function module_script_data( $args ) {
		$id               = $args['id'] ?? '';
		$selector         = $args['selector'] ?? '';
		$attrs            = $args['attrs'] ?? [];
		$store_instance   = $args['storeInstance'] ?? null;
		$decoration_attrs = $attrs['module']['decoration'] ?? [];

		ElementScriptData::set(
			[
				'id'            => $id,
				'selector'      => $selector,
				'attrs'         => $decoration_attrs,
				'storeInstance' => $store_instance,
			]
		);
	}
}
