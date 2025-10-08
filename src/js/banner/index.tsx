import { render } from '@wordpress/element';
import BannerConfigurator from '../components/BannerConfigurator';
import './styles.scss';

const container = document.getElementById( 'wm-banner-app' );

if ( container ) {
	render( <BannerConfigurator />, container );
}
