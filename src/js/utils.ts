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

type BrowserId = NonNullable<ReturnType<typeof getBrowserSupportForExtension>>;
const URL_MAP: Record<BrowserId, string> = {
  chrome: `https://chromewebstore.google.com/detail/web-monetization/oiabcfomehhigdepbbclppomkhlknpii`,
  chromium: `https://chromewebstore.google.com/detail/web-monetization/oiabcfomehhigdepbbclppomkhlknpii`,
  edge: `https://microsoftedge.microsoft.com/addons/detail/web-monetization/imjgemgmeoioefpmfefmffbboogighjl`,
  firefox: `https://addons.mozilla.org/en-US/firefox/addon/web-monetization-extension/`,
  safari: `https://apps.apple.com/app/web-monetization/id6754325288`,
};

function getBrowserSupportForExtension(ua: string, vendor = '') {
  const isMobile =
    /Mobi|Android|iPhone|iPad|iPod/i.test(ua) ||
    (navigator.maxTouchPoints > 0 && /Macintosh/.test(ua));
  const isMacOS = /Macintosh/i.test(ua) && !isMobile;

  // Firefox (Desktop & Android supported, iOS excluded)
  if (/Firefox/i.test(ua) && !/FxiOS/i.test(ua)) {
    return 'firefox'; // Both Desktop and Android
  }

  // Safari (macOS supported, iOS/iPadOS excluded)
  if (
    /Safari/i.test(ua) &&
    /Apple Computer/i.test(vendor) &&
    !/Chrome|CriOS|Android/i.test(ua)
  ) {
    if (isMacOS) {
      return 'safari';
    } else {
      return null; // Identified as Safari, but on mobile/iPad
    }
  }

  // Chromium-based Browsers
  // Chromium Rule: Supported on Desktop Only
  // (Excludes Chrome Android, Chrome iOS, Edge Mobile, etc.)
  if (/Chrome|CriOS|Edg|Vivaldi|Opr|Brave/i.test(ua)) {
    // Determine the specific flavor
    if (/Edg/i.test(ua)) {
      return isMobile ? null : 'edge';
    } else if (/Vivaldi/i.test(ua) || /Opr/i.test(ua) || /Brave/i.test(ua)) {
      return isMobile ? null : 'chromium';
    } else {
      return isMobile ? null : 'chrome';
    }
  }

  return null;
}

function getExtensionUrl(
  browserId: BrowserId,
  utm?: Record<string, string>,
): URL {
  const url = URL_MAP[browserId];
  return urlWithParams(url, utm || {});
}

// Based on https://github.com/interledger/publisher-tools/blob/aaaee82592bd2073e4fb4a9c35b2015c3ca4fa2f/shared/utils/extension.ts#L60
export function getExtensionHref(utm?: Record<string, string>): string {
  utm = {
    utm_source: window.location.hostname,
    ...utm,
  };
  const fallbackUrl = 'https://webmonetization.org';

  const browserId = getBrowserSupportForExtension(
    navigator.userAgent,
    navigator.vendor,
  );
  if (!browserId) {
    console.warn('Using on browser that does not have WM extension support');
    return urlWithParams(fallbackUrl, utm).href;
  }
  return getExtensionUrl(browserId, utm).href;
}

export function urlWithParams(
  url: string | URL,
  params: Record<string, string>,
): URL {
  const result = new URL(url);
  const searchParams = new URLSearchParams(params);
  for (const [key, val] of searchParams.entries()) {
    result.searchParams.set(key, val);
  }
  return result;
}
