// globals.d.ts
interface BannerConfig {
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

declare const ajaxurl: string;
declare const wpApiSettings: { nonce: string }; // example if you use wp_localize_script
// TypeScript declaration for importing SVGs as modules
declare module '*.svg' {
  const content: string;
  export default content;
}
interface Window {
  wm: {
    wmBannerConfig: string;
    wmEnabled?: boolean;
    wmBuildUrl?: string;
  };
}
interface Document {
  /** Present when the Web Monetization polyfill/extension is active */
  monetization?: Monetization;
}
