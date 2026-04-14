# WP YouTube Latest (WP YTB) 🚀

![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)
![Version](https://img.shields.io/badge/Version-1.0.0-green.svg)
![License](https://img.shields.io/badge/License-GPL_v2_or_later-orange.svg)

**WP YouTube Latest** is a lightweight, modern, and high-performance WordPress plugin that allows you to beautifully display the latest videos from any YouTube channel. Built primarily on the native YouTube RSS feed, the plugin completely eliminates the need for any Google Cloud API keys or complex setups. Simply provide a YouTube handle (e.g., `@username`) or a channel URL, and the plugin will do the heavy lifting.

## ✨ Key Features
- **Zero API Key Requirement:** Unlike many other YouTube plugins, WP-YTB does not require you to configure OAuth or obtain Google API keys. It parses the public RSS XML feed intelligently.
- **Smart ID Extraction:** The plugin automatically extracts the complex alphanumeric Channel ID (e.g., `UC...`) from standard YouTube URLs or `@handles` by parsing the source code, and permanently caches that ID for future use.
- **High Performance & Aggressive Caching:** Implements WordPress Transients API to cache video objects for a configurable number of hours, drastically reducing external HTTP lookups and keeping page loads instant.
- **Beautiful & Modern Grid Interface:** Built with modern CSS Grid architecture, sleek typography, subtle shadows, and neat hover scale effects.
- **Full RTL Support:** Native Right-to-Left (RTL) compatibility for Arabic and Hebrew WordPress themes.
- **Cache Management:** Built-in "Clear Cache" button directly in the WordPress dashboard to instantly fetch new videos without waiting for the transient to expire.
- **Dashboard Widget:** Quick-access usage guideline widget available directly in the WordPress dashboard for admins.

## 🛠️ Technical Details & File Structure
This plugin follows an optimized modular structure, avoiding cluttering the global namespace.
```text
wp-ytb/
├── wp-ytb.php                            # Main plugin bootstrap and dashboard widget implementation
├── includes/
│   ├── class-wp-ytb-settings.php         # OOP class for the Admin Settings page and Tabs
│   ├── class-wp-ytb-feed.php             # Core logic for ID resolution and RSS XML scraping
│   └── class-wp-ytb-shortcode.php        # Registration and HTML rendering of the shortcode
├── assets/
│   └── css/
│       └── wp-ytb-style.css              # Custom CSS rules utilizing BEM methodology
└── docs.md                               # End-user Arabic manual and step-by-step usage guide
```

## 📥 Installation
1. Upload the `wp-ytb` plugin folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the **'Plugins'** menu in WordPress.
3. Access the core settings under **Settings > WP YouTube**.

## 🚀 Usage (Shortcodes)
After configuring your default YouTube channel and cache limit in the settings, you can render the grid using shortcodes.
- **Basic Usage:** `[youtube_latest]` (inherits settings from the dashboard).
- **Advanced Context Usage:** Override the global settings to display specific channels on specific posts.
   `[youtube_latest channel="@MKBHD" limit="3"]`

## 👨‍💻 Developer Customization
The HTML markup relies on the BEM (Block Element Modifier) convention, making it extremely easy to override the layout in your child theme. Customizations can hook into classes like:
- `.wp-ytb-grid` 
- `.wp-ytb-item`
- `.wp-ytb-thumb`
- `.wp-ytb-title`

---
*For detailed user instructions (in Arabic), please refer to the `docs.md` file included in this repository.*
