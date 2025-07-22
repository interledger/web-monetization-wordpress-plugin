<?php

declare(strict_types=1);
/**
 * Web Monetization settings rendering helper functions.
 * Creates the basic components that can be used to render elements and tabs in the Web Monetization settings panel
 *
 * @package WebMonetization
 */
namespace WebMonetization\Admin\Rendering;

/**
 * Class FieldRenderer
 *
 * Renders the heading for each section in the settings panel.
 *
 * @return void
 * @param string $heading
 * @param string $description
 */
class FieldRenderer {

	/**
	 * Renders the heading for each section in the settings panel.
	 *
	 * @param string $heading
	 * @param string $description
	 */
	public static function render_section_heading( string $title, string $description = '' ): void {
		echo '<h2>' . esc_html( $title ) . '</h2>';
		if ( $description !== '' ) {
			echo '<p  class="description">' . esc_html( $description ) . '</p>';
		}
	}

	public static function render_text_input( string $id, string $name, string $value, string $placeholder = '' ): void {
		printf(
			'<input type="text" id="%1$s" name="%2$s" value="%3$s" placeholder="%4$s" class="regular-text">',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $placeholder )
		);
	}

	public static function render_radio_switch( string $id, string $name, string $value, array $options ): void {
		echo '<fieldset>';
		foreach ( $options as $option_value => $label ) {
			printf(
				'<label><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label><br>',
				esc_attr( $name ),
				esc_attr( $option_value ),
				checked( $value, $option_value, false ),
				$label
			);
		}
		echo '</fieldset>';
	}

	public static function render_checkbox( $id, $name, $value, $label ): void {
		printf(
			'<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
			esc_attr( $id ),
			esc_attr( $name ),
			checked( $value, 1, false ),
			$label
		);
	}
}
