=== Interledger Web Monetization Integration ===
Contributors: interledger
Tags: web monetization, open-payments, interledger, micropayments, payments
Requires at least: 6.8
Tested up to: 6.8
Stable tag: 1.0.2
Requires PHP: 7.4
License: Apache-2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0.txt
Add a web monetization-compatible wallet address to monetize your site, posts, and pages. Receiving micropayments. No tracking.

== Description ==

**Web Monetization** connects you with your audience, turning engagement into revenue. No friction, no barriers, no limits. **Interledger Web Monetization Integration** adds a `<link rel="monetization">` tag to your site’s `<head>` - or more- and lets you define **which wallet gets paid** at the **site-wide**, **author**, or **per-post** level. It’s built to work with Interledger-compatible wallets and is designed for performance, clarity, and extensibility.

**Highlights**
- Add a Web Monetization tag to your pages automatically
- Configure a **default site wallet**, allow **author wallets**, and **override per post/page**
- **Multi-wallet mode**: output all configured wallets or only the top-priority one
- Visual admin indicator for wallet connection/availability
- Exclude specific users from being monetized
- Developer-friendly hooks & filters
- Fully translatable (`.pot` included)
- No tracking, no external requests by default

**What is Web Monetization?**  
Web Monetization is an open web standard. It connects publishers with their audiences and enables continuous payments to their websites.

= Privacy =
- No user data is collected or stored by this plugin.
- No cookies are set.
- Extension download links include analytics parameters (UTM tags) to track aggregate click sources. No personal information is transmitted.
- Optional country targeting requires the GeoIP Detection plugin.

= Performance =
- Minimal footprint: a single or multiple  `<link rel="monetization">` tags are added to the head.
- Built to play nicely with caching and modern themes.

== Features ==

- ✅ Site-wide wallet (global default)
- ✅ Author-specific wallets (optional)
- ✅ Per-post/per-page wallet override
- ✅ Multi-wallet output (all vs. highest-priority)
- ✅ Exclude selected users from monetization
- ✅ Admin UI status indicator for wallet connection
- ✅ Hooks & filters for developers
- ✅ Translation-ready

== Installation ==

= From your WordPress Dashboard =
1. Go to **Plugins → Add New**.
2. Search for **“Interledger Web Monetization Integration”**.
3. Install and **Activate**.

= Manual Installation =
1. Upload the plugin folder to `/wp-content/plugins/`.
2. Go to **Plugins** and **Activate**.
3. Open **Settings → Web Monetization** to configure your wallet(s).

== Frequently Asked Questions ==

= Do I need an external service? =
No. By default the plugin adds markup only. You’ll configure your **Interledger-compatible wallet address** (often named a “payment pointer”).

= Where do I configure wallets? =
Go to **Settings → Web Monetization**. You can set a site-wide wallet, allow authors to set their own, and override per post/page.

= What is multi-wallet mode? =
When enabled, the plugin can output **all configured wallets** (e.g., site + author + post) or **only the top-priority** wallet, depending on your preference.

= Can I exclude certain users from monetization? =
Yes. There’s an option to **exclude specific users**; their content won’t output monetization tags.

= Does this work with custom post types? =
Yes. The plugin is designed to be extensible; custom post types that support meta boxes can be integrated.

= Does the plugin track users or send data to third parties? =
No. There’s **no tracking** and **no external requests** by default.

= Is it translation-ready? =
Yes. A `.pot` file is included.

== Screenshots ==

1. Settings screen: site-wide wallet and author/post-level options.
2. Per-post wallet override meta box.
3. Banner configuration page.
4. Example `<link rel="monetization">` rendered in the page head.

== Changelog ==

= 1.0.2 =
* Fixed ActivityPub integration to properly handle multiple space-separated wallet addresses in post meta.
* Removed incorrect site wallet fallback when post ID cannot be resolved in ActivityPub contexts.
* Improved wallet normalization for better multi-wallet support.

= 1.0.1 =
* Improved banner close button accessibility with semantic button element and SVG icon.
* Added Safari browser support for Web Monetization extension download links.
* Added UTM tracking parameters to extension download links for analytics.

= 1.0.0 =
* Initial release.
* Site-wide, author, and per-post wallet configuration.
* Multi-wallet mode (all vs. top-priority).
* Exclude users from monetization.
* Admin UI status indicator.
* Hooks & filters for developers.
* Translation-ready.

== Upgrade Notice ==

= 1.0.2 =
Bug fixes for ActivityPub integration and improved multi-wallet handling.

= 1.0.1 =
Bug fixes and improvements including better accessibility and Safari browser support for web monetization extension.

= 1.0.0 =
Initial release.

== Developer Notes ==

- Built with an **OOP architecture** and Composer autoloading.
- **Hooks & filters** are available for customizing wallet selection and output.
- Designed to work cleanly with themes that call `wp_head()` and with popular caching setups.

== License ==

This plugin is licensed under the **Apache-2.0**.
