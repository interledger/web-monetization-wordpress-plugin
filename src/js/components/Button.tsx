import { cx } from 'class-variance-authority';
import { forwardRef } from '@wordpress/element';
import { ButtonOrLink, type ButtonOrLinkProps } from './ButtonOrLink';

type ButtonProps = ButtonOrLinkProps & {
	intent?: 'default' | 'reset' | 'danger' | 'icon' | 'invisible';
	size?: 'sm' | 'md';
	[ 'aria-label' ]: string;
	variant?: string;
};

export const Button = forwardRef< HTMLButtonElement, ButtonProps >(
	(
		{ intent = 'default', size = 'md', children, className, ...props },
		ref
	) => {
		return (
			<ButtonOrLink
				ref={ ref }
				className={ cx(
					'button',
					`button--${ intent }`,
					`button--${ size }`,
					className
				) }
				{ ...props }
			>
				{ children }
			</ButtonOrLink>
		);
	}
);

Button.displayName = 'Button';
