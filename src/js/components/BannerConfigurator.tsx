import debounce from 'lodash.debounce';

import { cx } from 'class-variance-authority';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';

import { ColorPicker } from './ColorPicker';
import { FontSize } from './FontSize';
import { controlOptions, FontsType } from '../presets';
import eyeSvg from '../../../assets/images/eye.svg';
import { Select } from './Select';

const borderOptions = [
	{ label: 'No border', value: 'none' },
	{ label: 'Light rounding', value: 'rounded' },
	{ label: 'Pill', value: 'pill' },
];

const defaultConfig: BannerConfig = {
	title: 'Support me with Web Monetization!',
	message:
		'With Web Monetization, you can support me by making a one-time contribution or by sending small payments as you spend time on my site.',
	bgColor: '#7f76b2',
	textColor: '#ffffff',
	position: 'bottom',
	animation: true,
	borderStyle: 'rounded',
	font: 'Arial',
	fontSize: 17,
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

export default function BannerConfigurator() {
	const wmBannerConfig = window.intlwemo?.wmBannerConfig
		? JSON.parse( window.intlwemo.wmBannerConfig )
		: { nonce: '', config: undefined };

	const configData = {
		...defaultConfig,
		...( wmBannerConfig?.config || {} ),
	};

	const [ displayedControl, setDisplayedControl ] = useState( 'background' );

	const [ title, setTitle ] = useState( configData.title );
	const [ message, setMessage ] = useState( configData.message );
	const [ bgColor, setBgColor ] = useState( configData.bgColor );
	const [ textColor, setTextColor ] = useState( configData.textColor );
	const [ animation, setAnimation ] = useState( configData.animation );
	const [ position, setPosition ] = useState( configData.position );
	const [ borderStyle, setBorderStyle ] = useState( configData.borderStyle );
	const [ font, setFont ] = useState( configData.font );
	const [ fontSize, setFontSize ] = useState( configData.fontSize );

	const [ triggerAnimation, setTriggerAnimation ] = useState( false );
	const [ publishStatus, setPublishStatus ] = useState<
		'idle' | 'loading' | 'success' | 'error'
	>( 'idle' );
	const [ publishMessage, setPublishMessage ] = useState< string | null >(
		null
	);

	const config: BannerConfig = useMemo(
		() => ( {
			title,
			message,
			bgColor,
			textColor,
			position,
			animation,
			borderStyle,
			font,
			fontSize,
		} ),
		[
			title,
			message,
			bgColor,
			textColor,
			position,
			animation,
			borderStyle,
			font,
			fontSize,
		]
	);

	const lastSaved = useRef< BannerConfig >( config );
	const nonce = wmBannerConfig?.nonce || '';

	const debouncedSave = useMemo(
		() =>
			debounce( ( conf: BannerConfig ) => {
				if (
					JSON.stringify( conf ) ===
					JSON.stringify( lastSaved.current )
				) {
					return;
				}
				fetch( ajaxurl, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams( {
						action: 'intlwemo_save_banner_config',
						config: JSON.stringify( conf ),
						_wpnonce: nonce,
					} ),
				} )
					.then( ( res ) => res.json() )
					.then( ( data ) => {
						if ( data.success ) {
							lastSaved.current = conf;
						}
					} );
			}, 1000 ),
		[ nonce ]
	);

	const publishConfig = async (
		conf: BannerConfig
	): Promise< { success: boolean; message?: string } > => {
		try {
			const res = await fetch( ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams( {
					action: 'intlwemo_publish_banner_config',
					config: JSON.stringify( conf ),
					_wpnonce: nonce,
				} ),
			} );
			const data = await res.json();
			if ( data.success ) {
				lastSaved.current = conf;
				return { success: true };
			}
			return {
				success: false,
				message: data.message || 'Failed to save config',
			};
		} catch ( err ) {
			return { success: false, message: 'Network error' };
		}
	};

	const handleSubmit = async () => {
		setPublishStatus( 'loading' );
		setPublishMessage( null );

		const result = await publishConfig( config );

		if ( result.success ) {
			setPublishStatus( 'success' );
			setPublishMessage( 'Banner configuration published successfully!' );
			setTimeout( () => {
				setPublishStatus( 'idle' );
				setPublishMessage( null );
			}, 2000 ); // Reset after a while
		} else {
			setPublishStatus( 'error' );
			setPublishMessage( result.message || 'Error saving configuration' );
		}
	};

	const resetToDefault = () => {
		setTitle( defaultConfig.title );
		setMessage( defaultConfig.message );
		setBgColor( defaultConfig.bgColor );
		setTextColor( defaultConfig.textColor );
		setPosition( defaultConfig.position );
		setAnimation( defaultConfig.animation );
		setBorderStyle( defaultConfig.borderStyle );
		setFont( defaultConfig.font );
		setFontSize( defaultConfig.fontSize );
	};
	useEffect( () => {
		debouncedSave( config );
	}, [ debouncedSave, config ] ); // track config changes

	const monetizationLinkHref = getWebMonetizationLinkHref();

	let buttonValue = 'Publish Banner Changes';
	if ( publishStatus === 'loading' ) {
		buttonValue = 'Publishing...';
	} else if ( publishStatus === 'success' ) {
		buttonValue = 'Changes Published';
	} else if ( publishStatus === 'error' ) {
		buttonValue = 'Retry Publish';
	}
	return (
		<div
			className="intlwemo-banner-configurator"
			style={ { maxWidth: '720px', marginTop: '2rem' } }
		>
			<h3 style={ { fontSize: '1.5rem', marginBottom: '1.5rem' } }>
				Configure Web Monetization Banner
			</h3>
			<p>
				Let your visitors know they can use Web Monetization to support
				you. The banner only appears to visitors who haven&apos;t
				installed or configured the Web Monetization extension. You can
				disable it anytime by unchecking the “Enable banner” option in
				<a href="/wp-admin/admin.php?page=interledger-web-monetization-settings&tab=general">
					{ ' ' }
					General Settings.
				</a>
			</p>
			<div className="intlwemo-gradient-container">
				{ animation && (
					<div className="intlwemo-banner-preview-button">
						<img
							onMouseEnter={ () => setTriggerAnimation( true ) }
							onMouseLeave={ () => setTriggerAnimation( false ) }
							className="cursor-progress"
							src={ eyeSvg }
							alt="check"
						/>
					</div>
				) }
				<div className={ `intlwemo-banner-container ${ position } ` }>
					<div
						className={ `intlwemo-banner-preview ${ borderStyle } ${
							triggerAnimation ? 'animation' : ''
						}` }
						style={ {
							backgroundColor: bgColor,
							color: textColor,
							fontFamily: font,
							fontSize: fontSize === 'small' ? '0.9rem' : '1rem',
						} }
					>
						<h5
							style={ {
								display: 'block',
								fontSize: fontSize ? `${ fontSize }px` : '16px',
								marginBottom: '0.5rem',
							} }
						>
							{ title }
						</h5>
						<p
							style={ {
								margin: 0,
								fontSize: fontSize ? `${ fontSize }px` : '16px',
							} }
						>
							{ message }
						</p>
						<br />
						<span className="_intlwemo_link">
							<a
								rel="noindex nofollow noreferrer"
								target="_blank"
								href={ monetizationLinkHref }
							>
								Download the Web Monetization extension
							</a>
						</span>
					</div>
				</div>
				<div className={ cx( 'main-controls', bgColor ) }>
					<div className="main-controls__left">
						{ displayedControl === 'background' && (
							<ColorPicker
								label="Background color"
								name="bannerBackgroundColor"
								preset="background"
								value={ config?.bgColor || '' }
								updateColor={ ( value ) => setBgColor( value ) }
								className={ cx(
									displayedControl !== 'background' &&
										'hidden'
								) }
							/>
						) }

						{ displayedControl === 'text' && (
							<ColorPicker
								label="Text color"
								name="textColor"
								preset="text"
								value={ config?.textColor || '' }
								updateColor={ ( value ) => {
									setTextColor( value );
								} }
								className={ cx(
									displayedControl !== 'text' && 'hidden'
								) }
							/>
						) }
					</div>
					<div className="main-controls__right">
						<Select
							placeholder="Background"
							options={ controlOptions }
							defaultValue={ controlOptions.find(
								( opt ) => opt.value === 'background'
							) }
							onChange={ ( value ) =>
								value && setDisplayedControl( value )
							}
						/>
					</div>
				</div>
			</div>

			<div className="intlwemo-form">
				<div className="intlwemo-form-row">
					<label htmlFor="intlwemo-position">
						Position
						<select
							id="intlwemo-position"
							value={ position }
							onChange={ ( e ) =>
								setPosition(
									e.target.value as 'top' | 'bottom'
								)
							}
						>
							<option value="bottom">Bottom</option>
							<option value="top">Top</option>
						</select>
					</label>

					<label htmlFor="intlwemo-animation">
						Animation
						<select
							id="intlwemo-animation"
							value={ animation ? 'yes' : 'no' }
							onChange={ ( e ) =>
								setAnimation( e.target.value === 'yes' )
							}
						>
							<option value="yes">Yes</option>
							<option value="no">No</option>
						</select>
					</label>

					<label htmlFor="borderStyle">
						Border Style
						<select
							id="borderStyle"
							value={ borderStyle }
							onChange={ ( e ) =>
								setBorderStyle(
									e.target.value as
										| 'none'
										| 'rounded'
										| 'pill'
								)
							}
						>
							{ borderOptions.map( ( opt ) => (
								<option key={ opt.value } value={ opt.value }>
									{ opt.label }
								</option>
							) ) }
						</select>
					</label>
				</div>
				<div className="intlwemo-form-row">
					<label htmlFor="intlwemo-font">
						Font
						<select
							id="intlwemo-font"
							value={ font }
							onChange={ ( e ) => setFont( e.target.value ) }
						>
							{ FontsType.map( ( opt ) => (
								<option key={ opt } value={ opt }>
									{ opt }
								</option>
							) ) }
						</select>
					</label>

					<FontSize
						label="Font Size"
						name="fontSize"
						value={ fontSize }
						updateSize={ ( value ) => {
							setFontSize( value );
						} }
					/>

					<label htmlFor="intlwemo-banner-title">
						Title
						<input
							id="intlwemo-banner-title"
							type="text"
							value={ title }
							onChange={ ( e ) => setTitle( e.target.value ) }
							className="regular-text"
						/>
					</label>
				</div>

				<div className="intlwemo-form-row">
					<label htmlFor="intlwemo-banner-message">
						Text
						<textarea
							id="intlwemo-banner-message"
							value={ message }
							onChange={ ( e ) => setMessage( e.target.value ) }
							className="large-text"
							rows={ 4 }
						/>
					</label>
				</div>
				<div className="intlwemo-form-row">
					<p className="submit">
						<input
							type="submit"
							name="submit"
							id="submit"
							className="button-primary"
							value={ buttonValue }
							disabled={ publishStatus === 'loading' }
							onClick={ handleSubmit }
						/>
					</p>
					{ publishMessage && (
						<p
							className={ `submit status-message status-${ publishStatus }` }
						>
							{ publishMessage }
						</p>
					) }

					<p className="reset-button">
						<button
							type="button"
							className="button"
							onClick={ () => {
								resetToDefault();
							} }
						>
							Reset
						</button>
					</p>
				</div>
			</div>
		</div>
	);
}
