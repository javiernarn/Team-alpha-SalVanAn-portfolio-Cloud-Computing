# SalValAn Portfolio (Cloud Computing)

**Live site:** https://dev-sal-val-an.pantheonsite.io/

Team:

* Mariel Saldivar, Project Analyst
* Jonee John Antiquina, Developer
* Diane Valle, Technical Writer

Built on WordPress and hosted on Pantheon.

\---

An animated one-page portfolio for **SalValAn**, a three-person BSIT final-project team. The tagline is **Build. Analyze. Document.**, one word for each role.

**Highlights:** long-scroll single page · dark / light mode · scroll-spy navigation · mobile tab bar and drawer · installable PWA with offline page · contact form that opens a pre-filled email · no build step.

Current version: **4.2.1**

\---

## File tree

```text
.
├── README.md
└── salvalan-portfolio/
    ├── salvalan-portfolio.php      ← main PHP logic (settings, routing, PWA files)
    ├── template.php                ← page template
    │
    ├── parts/                      ← shared page parts
    │   ├── header.html             ← logo, navigation, theme toggle, mobile menu
    │   └── footer.html
    │
    ├── sections/                   ← one file per section, in scroll order
    │   ├── home.html               ← hero + team cards
    │   ├── about.html
    │   ├── skills.html
    │   ├── services.html
    │   ├── projects.html
    │   ├── experience.html
    │   └── contact.html
    │
    └── assets/
        ├── css/sva.css             ← all styles
        ├── js/sva.js               ← all behaviour
        ├── fonts/                  ← Space Grotesk (headings), Manrope (body)
        └── img/                    ← team photos and app icons
            ├── jonee.jpg
            ├── diane.jpg
            ├── mariel.jpg
            ├── icon-192.png
            ├── icon-512.png
            ├── icon-maskable-512.png
            └── apple-touch-icon.png
```

\---

## How the page is built

1. `salvalan-portfolio.php` loads on WordPress and creates the **Home** page from the files in `sections/`.
2. `template.php` outputs the header (`parts/header.html`), the sections, and the footer (`parts/footer.html`).
3. Placeholders like `{{assets}}` or `{{project\_name}}` in the HTML files are filled in at render time.
4. The browser loads `assets/css/sva.css` and `assets/js/sva.js`.

\---

## Code guide

|File|What it does|
|-|-|
|`salvalan-portfolio.php`|Settings (email, ATMS link, project name, story, mission), page creation, placeholders, SEO tags, PWA manifest, service worker and offline page|
|`template.php`|Page layout|
|`sections/\*.html`|Content of each section|
|`parts/\*.html`|Header and footer|
|`assets/css/sva.css`|Design tokens, dark/light themes, layout, animations|
|`assets/js/sva.js`|Theme toggle, smooth scroll and scroll-spy, reveal animations, project filters, mobile menu, contact form, PWA install|

### Colours

|Role|Light|Dark|
|-|-|-|
|Build|`#E8431A`|`#FF6A3D`|
|Analyze|`#007F73`|`#2DD4BF`|
|Document|`#5B45E0`|`#9C8BFF`|
|Background|`#F3EEE4`|`#0B0B0F`|

\---

## Team photos

Photos are plain crops of the original studio portraits (no AI generation or retouching), resized to 480×480 px JPEG, and load directly from `assets/img/`:

```text
salvalan-portfolio/assets/img/jonee.jpg
salvalan-portfolio/assets/img/diane.jpg
salvalan-portfolio/assets/img/mariel.jpg
```

To change one, replace the file with a 480×480 JPG of the same name.

\---

## Deploying on Pantheon

1. Copy `salvalan-portfolio/` into `wp-content/plugins/` on the Dev environment (Git or SFTP).
2. Activate it in wp-admin. The Home page is created automatically.
3. Fill in details under **Settings → SalValAn**.
4. Commit in the Pantheon dashboard and deploy Dev → Test → Live.
5. Clear the Pantheon cache after each deploy.

To publish a change, raise the version number in `salvalan-portfolio.php` (both the `Version:` line and `SVA\_VER`). The Home page refreshes itself the next time an admin opens wp-admin.

\---

## References

Pantheon WordPress documentation:

* [Using WordPress with Pantheon](https://docs.pantheon.io/develop-wordpress)
* [Introduction to WordPress on Pantheon](https://docs.pantheon.io/guides/wordpress-pantheon)
* [Create a New WordPress Site](https://docs.pantheon.io/guides/wordpress-pantheon/create-wp-site)
* [Connection Modes (Git and SFTP)](https://docs.pantheon.io/connection-modes)
* [Manage Custom Code for WordPress with Plugins](https://docs.pantheon.io/guides/wordpress-configurations/wordpress-custom-code)
* [Pantheon WordPress Cache Plugin Configuration](https://docs.pantheon.io/guides/wordpress-configurations/wordpress-cache-plugin)
* [WordPress Known Issues](https://docs.pantheon.io/wordpress-known-issues)

\---

## Credits

Built by **SalValAn** as a Cloud Computing final project.

