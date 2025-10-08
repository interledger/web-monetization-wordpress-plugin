export const isColorLight = ( color: string ) => {
	let r, g, b, colorPart;

	// Check the format of the color, HEX or RGB?
	if ( color.match( /^rgb/ ) ) {
		// If RGB --> separate the red, green, blue values in separate variables
		colorPart =
			color.match(
				/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/
			) || [];

		r = colorPart[ 1 ];
		g = colorPart[ 2 ];
		b = colorPart[ 3 ];
	} else {
		// If HEX --> Convert it to decimal values
		if ( color.length < 5 ) {
			colorPart = +( '0x' + color.slice( 1 ).replace( /./g, '$&$&' ) );
		} else {
			colorPart = +( '0x' + color.slice( 1 ) );
		}

		// eslint-disable-next-line no-bitwise
		r = colorPart >> 16;
		// eslint-disable-next-line no-bitwise
		g = ( colorPart >> 8 ) & 255;
		// eslint-disable-next-line no-bitwise
		b = colorPart & 255;
	}

	r = Number( r );
	g = Number( g );
	b = Number( b );
	// HSP equation from http://alienryderflex.com/hsp.html
	const hsp = Math.sqrt(
		0.299 * ( r * r ) + 0.587 * ( g * g ) + 0.114 * ( b * b )
	);

	// Using the HSP value, the color is light or not (dark)
	return hsp > 192;
};
