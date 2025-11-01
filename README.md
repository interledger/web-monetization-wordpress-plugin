# Interledger Web Monetization Integration for WordPress

**Interledger Web Monetization Integration** is a lightweight and extensible plugin that enables WordPress site owners and content creators to receive micro-payments using the [Web Monetization](https://webmonetization.org/). The plugin is designed to be compatible with Interledger wallets and supports flexible configuration across site, author, and content levels.

![WordPress Tested Up To](https://img.shields.io/badge/WordPress-6.8+-blue?logo=wordpress)  
![License: Apache-2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)  
![Maintained by Interledger Foundation](https://img.shields.io/badge/Maintained%20by-Interledger%20Foundation-0a0a0a)

---

## ✨ Features

- ✅ Add a Web Monetization `<link rel="monetization">` tag in your site `<head>`
- ✅ Support for **site-wide**, **author-specific**, and **post-level** wallet addresses
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

## 🔐 Privacy & Compliance

- No external services are required by default.
- Optional country targeting requires the free and GPL-compatible [GeoIP Detection plugin](https://wordpress.org/plugins/geoip-detect/).
- No tracking or analytics are included.

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
