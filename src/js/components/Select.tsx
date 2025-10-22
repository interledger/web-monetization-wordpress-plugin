import type { ReactNode } from 'react';
import { useEffect, useState } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';

import { Label } from './Label';
import { FieldError } from './FieldError';

export type SelectOption = {
	label: string;
	value: string;
};

type SelectProps = {
	options: SelectOption[];
	placeholder: string;
	name?: string;
	label?: string;
	tooltip?: string;
	disabled?: boolean;
	required?: boolean;
	error?: string | string[];
	value?: SelectOption;
	defaultValue?: SelectOption;
	description?: ReactNode;
	onChange?: ( value: string | null | undefined ) => void;
};

export const Select = ( {
	options,
	name,
	label,
	tooltip,
	error,
	disabled = false,
	required = false,
	defaultValue = { label: '', value: '' },
	value,
	description,
	onChange,
}: SelectProps ) => {
	const [ internalValue, setInternalValue ] = useState<
		string | string[] | number | undefined
	>( defaultValue.value );

	useEffect( () => {
		if ( value?.value ) {
			setInternalValue( value.value );
		}
	}, [ value ] );

	const handleChange = ( newValue: string | null | undefined ) => {
		setInternalValue( newValue ?? '' );
		if ( onChange ) {
			onChange( newValue );
		}
	};
	return (
		<div className="select-control-wrapper">
			{ label && (
				<Label tooltip={ tooltip } className="mb-1 block">
					{ label } { required && '*' }
				</Label>
			) }

			{ name && (
				<input type="hidden" name={ name } value={ internalValue } />
			) }
			<SelectControl
				className="wm-select-control"
				value={ internalValue as string | undefined }
				onChange={ handleChange }
				options={ options.map( ( opt ) => ( {
					label: opt.label,
					value: opt.value ?? '',
				} ) ) }
				__nextHasNoMarginBottom
				__next40pxDefaultSize={ true }
				disabled={ disabled }
			/>

			{ description && <p className="description">{ description }</p> }

			{ error && <FieldError error={ error } /> }
		</div>
	);
};
