# Interledger Web Monetization Integration for WordPress

**Interledger Web Monetization Integration** is a lightweight and extensible plugin that enables WordPress site owners and content creators to receive micro-payments using the [Web Monetization](https://webmonetization.org/). The plugin is designed to be compatible with Interledger wallets and supports flexible configuration across site, author, and content levels.

![WordPress Tested Up To](https://img.shields.io/badge/WordPress-6.8+-blue?logo=wordpress)  
![License: Apache-2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)  
![Maintained by Interledger Foundation](https://img.shields.io/badge/Maintained%20by-Interledger%20Foundation-0a0a0a)

---

## ✨ Features

- ✅ Add a Web Monetization `<link rel="monetization">` tag in your site `<head>`
- ✅ Support for **site-wide**, **author-specific**, and **post-level** wallet addresses
- ✅ **ActivityPub integration**: automatically includes monetization data in ActivityPub profiles and posts
- ✅ **RSS/Atom feed support**: adds monetization links to feed items
- ✅ Visual indicator in admin UI for wallet connection status
- ✅ Multi-wallet mode: show all configured wallet addresses or just the top-priority one
- ✅ Country-based wallet overrides (via optional GeoIP plugin)
- ✅ Exclude specific users from monetization
- ✅ Developer-friendly hooks and filters
- ✅ Fully translatable (`.pot` file included)
- ✅ Designed for performance and compatibility

---

## 🚀 Getting Started

1. Install the plugin via the WordPress admin dashboard or upload it manually.
2. Go to **Settings → Web Monetization** to configure your wallet address(es).
3. Optionally, enable:
   - Author-level monetization
   - Per-post monetization
   - Country-specific overrides
     - (requires [GeoIP Detection plugin](https://wordpress.org/plugins/geoip-detect/))
     - or a site running behind Cloudflare

---

## 🛠 Configuration Options

| Level         | Description                                                            |
| ------------- | ---------------------------------------------------------------------- |
| **Site-wide** | Default wallet for all pages                                           |
| **Author**    | Each author can set their own wallet (if allowed)                      |
| **Post/Page** | Override wallet per individual post                                    |
| **Country**   | Show different wallets for visitors from specific countries (optional) |

---

## 🌐 ActivityPub & Feed Integration

This plugin seamlessly integrates Web Monetization with the **ActivityPub** protocol and **RSS/Atom feeds**:

### ActivityPub Support
- Automatically adds the Web Monetization namespace to ActivityPub JSON-LD contexts
- Includes `monetization` property in ActivityPub actor profiles and post objects
- Supports multiple wallet addresses per profile/post
- Works with the [ActivityPub plugin](https://wordpress.org/plugins/activitypub/)

### Feed Support
- Adds `<link rel="monetization">` tags to RSS 2.0 feeds (using `atom:link` format)
- Includes monetization links in Atom feeds
- Per-item monetization based on post/author wallet configuration
- Site-wide monetization in feed headers

---

## 🔐 Privacy & Compliance

- No external services are required by default.
- Optional country targeting requires the free and GPL-compatible [GeoIP Detection plugin](https://wordpress.org/plugins/geoip-detect/).
- No user data is collected or stored by this plugin.
- Extension download links include analytics parameters (UTM tags) to track aggregate click sources for measuring plugin effectiveness. No personal information is transmitted.

---

## 📦 Installation

### From WordPress Dashboard

1. Go to **Plugins → Add New**
2. Search for “Web Monetization”
3. Install and activate the plugin

### Manual Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate via **Plugins** menu in WordPress

---

## 🧩 Developer Info

- **Hooks:** Filters and actions available for customizing wallet logic and monetization output.
- **Extensible:** Easily integrate with custom post types or external wallet services.
- **OOP-based architecture** using autoloading via Composer.

Want to contribute? See [`CONTRIBUTING.md`](CONTRIBUTING.md) for guidelines.

---

## 🧠 Requirements

- WordPress 6.8 or higher
- PHP 7.4 or higher

---

## 📝 License

This plugin is licensed under the [Apache-2.0](http://www.apache.org/licenses/LICENSE-2.0.txt).

© Interledger Foundation

---

## 📋 Changelog

### 1.0.2
* Fixed ActivityPub integration to properly handle multiple space-separated wallet addresses in post meta.
* Removed incorrect site wallet fallback when post ID cannot be resolved in ActivityPub contexts.
* Improved wallet normalization for better multi-wallet support.
* Fixed post type settings field name to use proper plugin prefix (intlwemo_post_type_settings).
* Improved consistency with plugin naming conventions.

### 1.0.1
* Improved banner close button accessibility with semantic button element and SVG icon.
* Added Safari browser support for Web Monetization extension download links.
* Added UTM tracking parameters to extension download links for analytics.

### 1.0.0
* Initial release.
* Site-wide, author, and per-post wallet configuration.
* Multi-wallet mode (all vs. top-priority).
* Exclude users from monetization.
* Admin UI status indicator.
* Hooks & filters for developers.
* Translation-ready.
