import type { ComponentPropsWithoutRef } from 'react';

import { useMemo, useState, forwardRef } from '@wordpress/element';
import { HexColorPicker, HexColorInput } from 'react-colorful';
import { cx } from 'class-variance-authority';
import ClickAwayListener from 'react-click-away-listener';
import {
	backgroundColorPresets,
	textColorPresets,
	triggerColorPresets,
} from '../presets';
import { isColorLight } from '../utils';
import checkSvg from '../../../assets/images/check.svg';
import colorPickerSvg from '../../../assets/images/color_picker.svg';

type ColorPickerProps = ComponentPropsWithoutRef< 'div' > & {
	label?: string;
	value: string;
	name: string;
	preset: 'text' | 'background' | 'trigger';
	updateColor: ( value: string ) => void;
	error?: string | string[];
	allowCustomColors?: boolean;
};

export const ColorPicker = forwardRef< HTMLDivElement, ColorPickerProps >(
	(
		{
			id,
			value,
			name,
			preset,
			allowCustomColors = true,
			className,
			updateColor,
			...props
		},
		ref
	) => {
		const [ displayColorpicker, setDisplayColorpicker ] = useState( false );

		const generatedId = useMemo(
			() =>
				`wm-input-${
					typeof crypto !== 'undefined' && crypto.randomUUID
						? crypto.randomUUID()
						: Math.random().toString( 36 ).substr( 2, 8 )
				}`,
			[]
		);
		const internalId = id ?? generatedId;

		if ( ! value ) {
			return;
		}

		let colorPresets = backgroundColorPresets;
		if ( preset === 'text' ) {
			colorPresets = textColorPresets;
		} else if ( preset === 'trigger' ) {
			colorPresets = triggerColorPresets;
		}
		const isCustomColor =
			colorPresets.indexOf( value.toLowerCase() ) === -1;

		return (
			<div
				className={ cx( 'color-picker', className ) }
				ref={ ref }
				{ ...props }
			>
				<input
					type="hidden"
					name={ name }
					value={ value ?? '' }
					id={ internalId }
				/>
				<div className="color-picker__presets">
					{ colorPresets.map( ( color ) => (
						<div
							className="color-picker__swatch"
							style={ { backgroundColor: String( color ) } }
							onClick={ () => updateColor( color ) }
							role="presentation"
							key={ color }
						>
							{ value.toLowerCase() === color && (
								<img
									className={ cx(
										'color-picker__check',
										isColorLight( color ) && 'invert'
									) }
									src={ checkSvg }
									alt="check"
								/>
							) }
						</div>
					) ) }

					{ allowCustomColors && (
						<div className="color-picker__custom">
							<div
								className="color-picker__custom-trigger"
								style={ {
									backgroundColor: isCustomColor
										? String( value )
										: 'white',
								} }
								onClick={ ( e ) => {
									e.stopPropagation();
									setDisplayColorpicker( true );
								} }
								role="presentation"
							>
								<img
									className="color-picker__custom-icon"
									src={ colorPickerSvg }
									alt="picker"
								/>
							</div>
							{ displayColorpicker && (
								<ClickAwayListener
									onClickAway={ () => {
										setDisplayColorpicker( false );
									} }
								>
									<div
										className="color-picker__popover show"
										// onClick={(e) => e.stopPropagation()} // prevent closing when interacting inside
									>
										<style>{ `.react-colorful__last-control { border-radius: 0; }` }</style>
										<HexColorPicker
											color={ String( value ) }
											onChange={ ( color ) =>
												updateColor( color )
											}
										/>
										<div className="color-picker__input-wrapper">
											<span className="color-picker__hash">
												#
											</span>
											<HexColorInput
												color={ String( value ) }
												onChange={ ( color ) =>
													updateColor( color )
												}
												className="color-picker__input"
												placeholder="000000"
											/>
										</div>
									</div>
								</ClickAwayListener>
							) }
						</div>
					) }
				</div>
			</div>
		);
	}
);

ColorPicker.displayName = 'ColorPicker';
