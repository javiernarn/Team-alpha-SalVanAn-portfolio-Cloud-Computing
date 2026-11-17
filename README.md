# SalValAn Portfolio (Cloud Computing)

**Live site:** https://dev-sal-val-an.pantheonsite.io/

Team:
- Mariel Saldivar, Project Analyst
- Jonee John Antiquina, Developer
- Diane Valle, Technical Writer

Built on WordPress and hosted on Pantheon.

---

An animated one-page portfolio for **SalValAn**, a three-person BSIT final-project team:

| Role | Colour | Person |
|------|--------|--------|
| **Build** | orange | Jonee John Antiquina, Programmer, Designer & Developer |
| **Analyze** | teal | Mariel Saldivar, Project Analyst |
| **Document** | violet | Diane Valle, Technical Writer |

It is a **WordPress plugin** (not a theme). It runs on a WordPress site hosted on **Pantheon**, takes over the front page, and renders its own HTML so the active theme's header and footer are bypassed.

**Highlights:** long-scroll single page · dark / light mode · scroll-spy navigation · mobile tab bar and drawer · installable PWA with offline page · contact form that opens a pre-filled `mailto:` · no build step, no dependencies.

---

## File tree

```text
.
├── README.md
└── salvalan-portfolio/                 ← the plugin folder (goes in wp-content/plugins/)
    ├── salvalan-portfolio.php          ← plugin entry point: all PHP logic
    ├── template.php                    ← page template (replaces the theme's)
    │
    ├── parts/                          ← shared page chrome
    │   ├── header.html                 ← skip link, logo, nav, theme toggle, drawer, mobile tab bar
    │   └── footer.html                 ← footer
    │
    ├── sections/                       ← one file per section, in scroll order
    │   ├── home.html                   ← hero (Build. Analyze. Document.) + team cards + skills marquee
    │   ├── about.html                  ← team story and mission
    │   ├── skills.html
    │   ├── services.html               ← services grid
    │   ├── projects.html               ← featured team project + filterable project cards
    │   ├── experience.html
    │   └── contact.html                ← email rows, form, "Message …" targets
    │
    └── assets/
        ├── css/
        │   └── sva.css                 ← all styles (themes, layout, animations)
        ├── js/
        │   └── sva.js                  ← all behaviour (nav, theme, reveal, form, PWA)
        ├── fonts/
        │   ├── space-grotesk-latin-wght-normal.woff2   ← headings
        │   └── manrope-latin-wght-normal.woff2         ← body
        └── img/
            ├── jonee.jpg               ← team photo, 480×480
            ├── diane.jpg               ← team photo, 480×480
            ├── mariel.jpg              ← team photo, 480×480
            ├── icon-192.png            ← PWA icon
            ├── icon-512.png            ← PWA icon
            ├── icon-maskable-512.png   ← PWA maskable icon
            └── apple-touch-icon.png    ← iOS home-screen icon (180×180)
```

---

## How it works

```text
Visitor opens the home page
        │
        ▼
WordPress  ──template_include──▶  template.php
        │                              │
        │                              ├─ wp_head()
        │                              ├─ sva_part('header')    ← parts/header.html
        │                              ├─ sva_tokens(content)   ← sections/*.html (stored in the Home page)
        │                              ├─ sva_part('footer')    ← parts/footer.html
        │                              └─ wp_footer()
        ▼
Browser loads assets/css/sva.css + assets/js/sva.js
```

1. **Activation / version change.** `sva_seed()` creates or updates a page called **Home** (slug `home`), sets it as the static front page, and stores `sections/*.html` inside it as one `wp:html` block. It also switches permalinks from *Plain* to *Post name* if needed.
2. **Rendering.** `sva_is_page()` recognises that page, and `template_include` swaps in `template.php`.
3. **Tokens.** HTML files contain placeholders such as `{{assets}}` or `{{project_name}}`. `sva_tokens()` replaces them at render time with the plugin URL or the values saved in **Settings → SalValAn**.
4. **Old URLs.** `/team/`, `/about/`, `/skills/` and similar legacy pages 301-redirect to the matching `#section` of the home page.

---

## Code guide

### `salvalan-portfolio.php`

| Area | Function / hook | What it does |
|------|-----------------|--------------|
| Constants | `SVA_VER`, `SVA_DIR`, `SVA_URL`, `SVA_SECTIONS`, `SVA_LEGACY` | Version, paths, section order, legacy slugs |
| Settings schema | `sva_fields()`, `sva_opt()` | Editable fields (email, ATMS link, project name, story, mission, form shortcode) and their defaults |
| Tokens | `sva_tokens()` | Replaces `{{…}}` placeholders and builds the email rows / contact dropdown |
| Page build | `sva_build_home()`, `sva_seed()` | Concatenates `sections/*.html` and writes the Home page |
| Routing | `template_redirect`, `template_include` | Legacy redirects, custom template |
| Head | `wp_enqueue_scripts`, `wp_head` | Enqueues CSS/JS (cache-busted by file mtime), meta tags, manifest link, theme-colour, font preload |
| PWA | `init` hook | Serves `/salvalan-manifest`, `/salvalan-sw` (service worker) and `/salvalan-offline` |
| Admin | `sva_settings()` | Settings page under **Settings → SalValAn**, media picker, "Rebuild Home page" button |

### Front-end

| File | Contents |
|------|----------|
| `assets/css/sva.css` | Design tokens (`--build`, `--analyze`, `--doc`), dark/light themes via `html[data-theme]`, then sections in order: grain, header, buttons, hero, crew, marquee, services, projects, experience, contact, footer, drawer, reveal, mobile fixes |
| `assets/js/sva.js` | Theme toggle (saved in `localStorage`), eased scroll and scroll-spy, progress bar, reveal-on-scroll, hero spotlight, magnetic buttons, team-card tap-to-open, mobile drawer, project filters, copy-to-clipboard, contact form → `mailto:`, back-to-top, PWA install prompt, service-worker registration |

### Brand colours

| Token | Light | Dark |
|-------|-------|------|
| `--build` | `#E8431A` | `#FF6A3D` |
| `--analyze` | `#007F73` | `#2DD4BF` |
| `--doc` | `#5B45E0` | `#9C8BFF` |
| `--bg` | `#F3EEE4` | `#0B0B0F` |

---

## Settings (WordPress admin → Settings → SalValAn)

| Field | Used for |
|-------|----------|
| Jonee's email | Contact section (hidden if empty) |
| ATMS live link / year | Every ATMS button in Projects |
| Team project name | Projects section |
| Team story / Mission | About section |
| Contact form shortcode | Optional, replaces the built-in email form (e.g. a SureForms shortcode) |

---

## Team photos

All three photos, including Mariel's, load directly from `assets/img/` (not from the WordPress Media Library or dashboard uploads). Photos are plain crops of the original studio portraits (no AI generation or retouching), resized to **480×480 px** JPEG. To change one, replace the file with a new 480×480 JPG of the same name:

```text
salvalan-portfolio/assets/img/jonee.jpg
salvalan-portfolio/assets/img/diane.jpg
salvalan-portfolio/assets/img/mariel.jpg
```

Photos display with `object-fit: cover` and `object-position: 50% 18%`, so keep the face near the top-centre of the square.

---

## Deploying on Pantheon

1. Put the `salvalan-portfolio/` folder in `wp-content/plugins/` (Git or SFTP on the **Dev** environment).
2. In wp-admin, **activate** *SalValAn Portfolio*. The Home page is created and set as the front page automatically.
3. Go to **Settings → SalValAn** and fill in your details.
4. Commit from the Pantheon dashboard, then deploy Dev → Test → Live.
5. **Clear the Pantheon cache** (Dashboard → Clear Caches) after every deploy, otherwise old photos, CSS or JS may keep showing.

> Pantheon's Test and Live environments are read-only for code. Make file changes in Dev (or a multidev) and deploy upward.

### Updating after a code change

- Bump the version in **both** places in `salvalan-portfolio.php` (`Version:` header and `SVA_VER`, currently **4.2.1**). The Home page refreshes itself the next time an admin opens wp-admin.
- Or use **Settings → SalValAn → Rebuild Home page** to re-import `sections/*.html` manually (this overwrites manual edits to the Home page).

---

## Local development

No build tooling is needed. Edit the `.html`, `.css` and `.js` files directly, then reload. Any WordPress install works (for example [LocalWP](https://localwp.com/) or `wp-env`): copy the plugin into `wp-content/plugins/` and activate it.

---

## Credits

Built by **SalValAn** as a Cloud Computing final project.
