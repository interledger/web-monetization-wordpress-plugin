<?php
/**
 * Interledger Web Monetization settings rendering helper functions.
 * Creates the basic components that can be used to render elements and tabs in the Interledger Web Monetization settings panel
 *
 * @package Interledger\WebMonetization
 */

declare(strict_types=1);
namespace Interledger\WebMonetization\Admin\Rendering;

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
	 * @param string $title The title of the section.
	 * @param string $description The description of the section.
	 */
	public static function render_section_heading( string $title, string $description = '' ): void {
		echo '<h2>' . esc_html( $title ) . '</h2>';
		if ( '' !== $description ) {
			echo '<p  class="description">' . esc_html( $description ) . '</p>';
		}
	}
	/**
	 * Renders a text input field.
	 *
	 * @param string $id          The ID of the input field.
	 * @param string $name        The name of the input field.
	 * @param string $value       The value of the input field.
	 * @param string $placeholder The placeholder text for the input field.
	 * @param bool   $read_only   Whether the input field is read-only.
	 */
	public static function render_text_input( string $id, string $name, string $value, string $placeholder = '', $read_only = false ): void {
		printf(
			'<input type="text" id="%1$s" name="%2$s" value="%3$s" placeholder="%4$s" class="regular-text" %5$s>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $placeholder ),
			$read_only ? 'readonly' : ''
		);
	}

	/**
	 * Renders a hidden input field.
	 *
	 * @param string $id          The ID of the input field.
	 * @param string $name        The name of the input field.
	 * @param string $value       The value of the input field.
	 */
	public static function render_hidden_input( string $id, string $name, string $value ): void {
		printf(
			'<input type="hidden" id="%1$s" name="%2$s" value="%3$s">',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	/**
	 * Renders a radio switch input.
	 *
	 * @param string $id          The ID of the input field.
	 * @param string $name        The name of the input field.
	 * @param string $value       The value of the input field.
	 * @param array  $options    The options for the radio switch.
	 */
	public static function render_radio_switch( string $id, string $name, string $value, array $options ): void {
		echo '<fieldset>';
		foreach ( $options as $option_value => $label ) {
			printf(
				'<label><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label><br>',
				esc_attr( $name ),
				esc_attr( $option_value ),
				checked( $value, $option_value, false ),
				wp_kses_post( $label )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * Renders a checkbox input.
	 *
	 * @param string $id          The ID of the checkbox field.
	 * @param string $name        The name of the checkbox field.
	 * @param int    $value       The value of the checkbox field.
	 * @param string $label       The label for the checkbox field.
	 */
	public static function render_checkbox( $id, $name, $value, $label ): void {
		printf(
			'<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
			esc_attr( $id ),
			esc_attr( $name ),
			checked( $value, 1, false ),
			wp_kses_post( $label )
		);
	}
}
