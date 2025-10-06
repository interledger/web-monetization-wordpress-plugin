=== Web Monetization ===
Contributors: interledger
Tags: web monetization, open-payments, interledger, micropayments, payments, wallet, monetization, revenue, content-monetization, micro-payments, web-standards
Requires at least: 6.8.1
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Lightweight, extensible Web Monetization plugin for WordPress. Add a web monetization-compatible wallet address to monetize your entire site, individual posts, and pages. Receive micro-payments. No tracking.

== Description ==

**Web Monetization for WordPress** adds a `<link rel="monetization">` tag to your site’s `<head>` and lets you define **which wallet gets paid** at the **site-wide**, **author**, or **per-post** level. It’s built to work with Interledger-compatible wallets and is designed for performance, clarity, and extensibility.

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
It’s a proposed web standard that enables **streaming micro-payments** to creators as users browse. This plugin helps you output the right markup and manage which wallet gets paid.

= Privacy =
- No tracking or analytics.
- No external calls are made by default.

= Performance =
- Minimal footprint: a single `<link rel="monetization">` is added to the head.
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
2. Search for **“Web Monetization for WordPress”**.
3. Install and **Activate**.

= Manual Installation =
1. Upload the plugin folder to `/wp-content/plugins/`.
2. Go to **Plugins** and **Activate**.
3. Open **Settings → Web Monetization** to configure your wallet(s).

== Frequently Asked Questions ==

= Do I need an external service? =
No. By default the plugin adds markup only. You’ll configure your **Interledger-compatible wallet address** (often a “payment pointer”).

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

= 1.0.0 =
* Initial release.
* Site-wide, author, and per-post wallet configuration.
* Multi-wallet mode (all vs. top-priority).
* Exclude users from monetization.
* Admin UI status indicator.
* Hooks & filters for developers.
* Translation-ready.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Developer Notes ==

- Built with an **OOP architecture** and Composer autoloading.
- **Hooks & filters** are available for customizing wallet selection and output.
- Designed to work cleanly with themes that call `wp_head()` and with popular caching setups.

== License ==

This plugin is licensed under the **GPLv2 or later**.
