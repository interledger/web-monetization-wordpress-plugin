// globals.d.ts
import type { BannerConfig } from './types/banner-config';

declare const ajaxurl: string;
declare const wpApiSettings: { nonce: string }; // example if you use wp_localize_script

interface Window {
  wm: {
    wmBannerConfig: {
      nonce: string;
      config?: BannerConfig;
    };
    wmEnabled?: boolean;
    wmBuildUrl?: string;
  };
}
