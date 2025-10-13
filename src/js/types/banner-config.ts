export interface BannerConfig {
	title?: string;
	message?: string;
	bgColor?: string;
	textColor?: string;
	animation?: boolean;
	position?: 'top' | 'bottom';
	borderStyle?: 'none' | 'rounded' | 'pill';
	font?: string;
	fontSize?: number;
}
