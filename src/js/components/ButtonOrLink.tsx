import { forwardRef } from '@wordpress/element';
import { Link, type LinkProps } from './Link';
import { type ComponentProps } from 'react';

type AnchorOrLinkProps = ComponentProps< 'a' > & Partial< LinkProps >;

const AnchorOrLink = forwardRef< HTMLAnchorElement, AnchorOrLinkProps >(
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	( { children, href, to, ...props }, ref: any ) => {
		const isAnchor = typeof href !== 'undefined';

		if ( isAnchor ) {
			return (
				<a ref={ ref } rel="noreferrer" href={ href } { ...props }>
					{ children }
				</a>
			);
		}

		return (
			<Link to={ to ?? '/' } ref={ ref } { ...props }>
				{ children }
			</Link>
		);
	}
);
AnchorOrLink.displayName = 'AnchorOrLink';

export type ButtonOrLinkProps = Omit<
	ComponentProps< 'button' > & AnchorOrLinkProps,
	'ref'
> &
	(
		| { to: LinkProps[ 'to' ]; href?: never }
		| { to?: never; href: string }
		| { to?: never; href?: never }
	);

export const ButtonOrLink = forwardRef<
	HTMLButtonElement | HTMLAnchorElement,
	ButtonOrLinkProps
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
>( ( { to, href, ...props }, ref: any ) => {
	const isLink = typeof to !== 'undefined' || typeof href !== 'undefined';

	if ( isLink ) {
		return (
			<AnchorOrLink href={ href } to={ to } ref={ ref } { ...props } />
		);
	}

	return <button ref={ ref } { ...props } type={ props.type ?? 'button' } />;
} );
ButtonOrLink.displayName = 'ButtonOrLink';
