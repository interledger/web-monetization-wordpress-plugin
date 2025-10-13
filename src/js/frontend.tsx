import '../scss/frontend.scss';
const config = window.wm?.wmBannerConfig
	? JSON.parse( window.wm.wmBannerConfig ) ?? {}
	: {};
const wmEnabled = window.wm.wmEnabled || false;
const wmBuildUrl = window.wm.wmBuildUrl || '';

if ( wmEnabled ) {
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initWMBanner );
	} else {
		initWMBanner();
	}
}

function initWMBanner() {
	const { shadowHost, shadowRoot } = createShadowDOM();

	const css = getCSSFile( 'bannerStyle.css' );
	const banner = drawBanner( config );

	const font = getFontFamily( config?.font, 'banner' );
	shadowHost.style.setProperty(
		'--wmt-banner-font',
		font?.selectedFont ? font.selectedFont : 'inherit'
	);
	shadowHost.style.setProperty( '--wmt-banner-font-size', config?.fontSize );
	if ( font?.fontFamily ) {
		document.body.appendChild( font.fontFamily );
	}

	if ( banner ) {
		shadowRoot.appendChild( css );
		shadowRoot.appendChild( banner );
	}
	if ( document.monetization ) {
		if ( document.monetization.state === 'started' ) {
			// console.log(
			// 	'Monetization already started (user is an active subscriber)'
			// );
		} else {
			// console.log(
			// 	'Monetization not started yet, setting initial message'
			// );
			// Listen for the monetization start event to update the banner
			document.monetization.addEventListener( 'monetizationstart', () => {
				//console.log( 'Monetization started' );
			} );
		}
	}

	document.body.appendChild( shadowHost );
}

const createShadowDOM = () => {
	const shadowHost = document.createElement( 'div' );
	const shadowRoot = shadowHost.attachShadow( { mode: 'open' } );

	return { shadowHost, shadowRoot };
};

function drawBanner( conf: BannerConfig ) {
	const closedByUser = sessionStorage.getItem( '_wm_tools_closed_by_user' );

	const monetizationLinks = document.querySelector< HTMLLinkElement >(
		'link[rel=monetization]'
	);
	if (
		( monetizationLinks &&
			monetizationLinks.relList.supports( 'monetization' ) ) ||
		closedByUser ||
		monetizationLinks === null
	) {
		return null;
	}

	const banner = document.createElement( 'div' );
	banner.id = 'wm-banner';
	banner.className = '_wm_tools_banner';

	const position = conf.position ? conf.position.toLowerCase() : 'bottom';
	banner.classList.add( `_wm_tools_banner_${ position }` );

	if ( conf.animation ) {
		const animation = conf.animation && position === 'top' ? 'down' : 'up';
		banner.classList.add( `_wm_tools_banner_${ animation }` );
	}

	// custom styles for the element
	banner.style.color = conf.textColor ?? '#000';
	banner.style.backgroundColor = conf.bgColor ?? '#fff7e6';

	let bannerBorder = '0';
	if ( conf.borderStyle === 'rounded' ) {
		bannerBorder = '0.375rem';
	} else if ( conf.borderStyle === 'pill' ) {
		bannerBorder = '1rem';
	}

	banner.style.borderRadius = bannerBorder;

	const bannerHeader = document.createElement( 'div' );
	bannerHeader.className = '_wm_tools_banner_header';

	if ( config.title ) {
		const title = document.createElement( 'h5' );
		const titleText = document.createTextNode( config.title );
		title.appendChild( titleText );
		bannerHeader.appendChild( title );
	} else {
		const emptySpan = document.createElement( 'span' );
		bannerHeader.appendChild( emptySpan );
	}

	const closeButton = document.createElement( 'span' );
	const closeText = document.createTextNode( 'x' );
	closeButton.appendChild( closeText );
	closeButton.addEventListener( 'click', () => {
		sessionStorage.setItem( '_wm_tools_closed_by_user', 'true' );
		banner.classList.add( '_wm_tools_hidden' );
	} );
	bannerHeader.appendChild( closeButton );
	banner.appendChild( bannerHeader );

	// description text
	const descriptionSpan = document.createElement( 'span' );
	const descriptionText = document.createTextNode( conf.message || '' );
	descriptionSpan.appendChild( descriptionText );
	banner.appendChild( descriptionSpan );

	// WebMonetization link
	const linkSpan = document.createElement( 'span' );
	linkSpan.className = '_wm_link';

	const linkElement = document.createElement( 'a' );
	linkElement.rel = 'noindex nofollow';
	linkElement.target = '_blank';
	linkElement.href = getWebMonetizationLinkHref();
	const linkText = document.createTextNode( getWebMonetizationLinkText() );
	linkElement.appendChild( linkText );
	linkSpan.appendChild( linkElement );
	banner.appendChild( linkSpan );

	return banner;
}

const allowedFonts = [
	'Cookie',
	'Roboto',
	'Open Sans',
	'Titillium Web',
	`Arial`,
];

const getFontFamily = ( family: string, forElement: string = 'banner' ) => {
	// if exists remove it
	const currentFontFamily = document.getElementById(
		`wmt-font-family-${ forElement }`
	) as HTMLLinkElement;
	if ( currentFontFamily ) {
		currentFontFamily.remove();
	}

	let selectedFont = 'inherit';
	if ( allowedFonts.indexOf( family ) !== -1 ) {
		selectedFont = family;
	}

	// skip injecting of font if set to inherit
	if ( selectedFont === 'inherit' ) {
		return;
	}

	const styleObj = document.createElement( 'link' ) as HTMLLinkElement;
	styleObj.id = `wmt-font-family-${ forElement }`;
	styleObj.rel = 'stylesheet';
	styleObj.type = 'text/css';

	switch ( selectedFont ) {
		case 'Open Sans':
			styleObj.href = `https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap`;
			break;
		case 'Cookie':
			styleObj.href = `https://fonts.googleapis.com/css2?family=Cookie&display=swap`;
			break;
		case 'Roboto':
			styleObj.href = `https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap`;
			break;
		default:
			styleObj.href = `https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap`;
	}

	return { fontFamily: styleObj, selectedFont };
};

const getWebMonetizationLinkHref = () => {
	const userAgent = navigator.userAgent;
	if ( userAgent.includes( 'Firefox' ) ) {
		return 'https://addons.mozilla.org/en-US/firefox/addon/web-monetization-extension/';
	} else if (
		userAgent.includes( 'Chrome' ) &&
		! userAgent.includes( 'Edg' ) &&
		! userAgent.includes( 'OPR' )
	) {
		return 'https://chromewebstore.google.com/detail/web-monetization/oiabcfomehhigdepbbclppomkhlknpii';
	} else if ( userAgent.includes( 'Edg' ) ) {
		return 'https://microsoftedge.microsoft.com/addons/detail/web-monetization/imjgemgmeoioefpmfefmffbboogighjl';
	}
	return 'https://webmonetization.org/';
};

const getWebMonetizationLinkText = () => {
	const href = getWebMonetizationLinkHref();
	return href === 'https://webmonetization.org/'
		? 'Learn more'
		: 'Download the Web Monetization extension';
};

const getCSSFile = ( url: string ) => {
	const link = document.createElement( 'link' );

	link.rel = 'stylesheet';
	link.type = 'text/css';
	link.href = wmBuildUrl.concat( `${ url }` );

	return link;
};
