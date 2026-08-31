<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace MEE\Modules\RevsliderModule\RevsliderModuleTrait;

if(!defined('ABSPATH')) exit();

// phpcs:disable ET.Sniffs.ValidVariableName.UsedPropertyNotSnakeCase -- WP uses snakeCase in \WP_Block_Parser_Block

use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;

trait RenderCallbackTrait {
	use ModuleClassnamesTrait;
	use ModuleStylesTrait;
	use ModuleScriptDataTrait;

	/**
	 * Read a nested array value.
	 *
	 * @param mixed $source  Array source.
	 * @param array $path    Nested path segments.
	 * @param mixed $default Default value when the path does not exist.
	 *
	 * @return mixed
	 */
	private static function get_nested_attr_value( $source, array $path, $default = '' ) {
		$value = $source;

		foreach ( $path as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $default;
			}

			$value = $value[ $segment ];
		}

		return $value;
	}

	/**
	 * Resolve a text value from a Divi 5 responsive text attribute.
	 *
	 * @param array  $attrs     Module attributes.
	 * @param string $attr_name Top-level attribute name.
	 *
	 * @return string
	 */
	private static function get_text_attr_value( array $attrs, string $attr_name ): string {
		$value = self::get_nested_attr_value( $attrs, [ $attr_name, 'innerContent', 'desktop', 'value', 'value' ], null );

		if ( is_string( $value ) ) {
			return $value;
		}

		$value = self::get_nested_attr_value( $attrs, [ $attr_name, 'innerContent', 'desktop', 'value' ], '' );

		return is_string( $value ) ? $value : '';
	}

	/**
	 * Build the minimal frontend shortcode from saved attrs.
	 *
	 * @param array $attrs Module attributes.
	 *
	 * @return string
	 */
	private static function build_shortcode( array $attrs ): string {
		$existing_shortcode = trim( self::get_text_attr_value( $attrs, 'shortcode' ) );

		if ( '' !== $existing_shortcode ) {
			return html_entity_decode( $existing_shortcode, ENT_QUOTES, 'UTF-8' );
		}

		$alias = trim( self::get_text_attr_value( $attrs, 'alias' ) );

		if ( '' === $alias ) {
			return '';
		}

		$shortcode_attrs = [
			'alias' => $alias,
		];

		if ( 'on' === self::get_text_attr_value( $attrs, 'modal' ) ) {
			$shortcode_attrs['usage'] = 'modal';

			// TODO: Expand popup-specific shortcode arguments from the saved modal attrs here.
		}

		$parts = [];

		foreach ( $shortcode_attrs as $name => $value ) {
			if ( '' === $value ) {
				continue;
			}

			$parts[] = sprintf(
				'%s="%s"',
				sanitize_key( $name ),
				esc_attr( $value )
			);
		}

		if ( empty( $parts ) ) {
			return '';
		}

		return sprintf( '[sr7 %1$s][/%2$s]', implode( ' ', $parts ), 'sr7' );
	}

	/**
	 * Render the saved/generated shortcode.
	 *
	 * @param string $shortcode Shortcode string.
	 *
	 * @return string
	 */
	private static function render_shortcode_output( string $shortcode ): string {
		if ( '' === $shortcode ) {
			return '';
		}

		$prepared_shortcode = str_replace(
			[ '&#91;', '&#93;' ],
			[ '[', ']' ],
			$shortcode
		);

		if ( function_exists( 'et_pb_fix_shortcodes' ) ) {
			$prepared_shortcode = et_pb_fix_shortcodes( $prepared_shortcode, true );
		}

		return do_shortcode( $prepared_shortcode );
	}

	/**
	 * Build inner revslider wrapper attributes.
	 *
	 * Divi still owns the outer module wrapper via Module::render(); this keeps the
	 * frontend-specific `revslider` hook on the inner container while allowing custom
	 * HTML id/class values to land on the same element.
	 *
	 * @param array $attrs Module attributes.
	 *
	 * @return array<string, string>
	 */
	private static function get_revslider_wrapper_attributes( array $attrs ): array {
		$attributes = [
			'class' => 'revslider',
		];

		$custom_class = trim(
			(string) self::get_nested_attr_value(
				$attrs,
				[ 'module', 'advanced', 'htmlAttributes', 'desktop', 'value', 'class' ],
				self::get_nested_attr_value(
					$attrs,
					[ 'module', 'advanced', 'htmlAttributes', 'desktop', 'class' ],
					''
				)
			)
		);

		if ( '' !== $custom_class ) {
			$attributes['class'] .= ' ' . $custom_class;
		}

		$custom_id = trim(
			(string) self::get_nested_attr_value(
				$attrs,
				[ 'module', 'advanced', 'htmlAttributes', 'desktop', 'value', 'id' ],
				self::get_nested_attr_value(
					$attrs,
					[ 'module', 'advanced', 'htmlAttributes', 'desktop', 'id' ],
					''
				)
			)
		);

		if ( '' !== $custom_id ) {
			$attributes['id'] = $custom_id;
		}

		return $attributes;
	}

	/**
	 * Frontend renderer for RevsliderModule.
	 *
	 * @param array     $attrs    Saved block attributes.
	 * @param string    $content  Block content.
	 * @param \WP_Block $block    Parsed block object.
	 * @param mixed     $elements Module elements helper.
	 *
	 * @return string
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		$background_component = ElementComponents::component(
			[
				'attrs'         => $attrs['module']['decoration'] ?? [],
				'id'            => $block->parsed_block['id'],
				'orderIndex'    => $block->parsed_block['orderIndex'],
				'storeInstance' => $block->parsed_block['storeInstance'],
			]
		);
		$shortcode            = self::build_shortcode( $attrs );
		$shortcode_output     = self::render_shortcode_output( $shortcode );
		$revslider_container  = HTMLUtility::render(
			[
				'tag'               => 'div',
				'attributes'        => self::get_revslider_wrapper_attributes( $attrs ),
				'childrenSanitizer' => 'et_core_esc_previously',
				'children'          => $shortcode_output,
			]
		);

		return Module::render(
			[
				'orderIndex'          => $block->parsed_block['orderIndex'],
				'storeInstance'       => $block->parsed_block['storeInstance'],
				'id'                  => $block->parsed_block['id'],
				'name'                => $block->block_type->name,
				'moduleCategory'      => $block->block_type->category,
				'attrs'               => $attrs,
				'elements'            => $elements,
				'classnamesFunction'  => [ self::class, 'module_classnames' ],
				'stylesComponent'     => [ self::class, 'module_styles' ],
				'scriptDataComponent' => [ self::class, 'module_script_data' ],
				'children'            => [
					$background_component,
					$revslider_container,
				],
			]
		);
	}
}
