# forgedseo

Shipping unit for [forgedseo.com](https://forgedseo.com): the **ForgedSEO Core** WordPress plugin (`forgedseo-core`) plus Hostinger **production** deploy.

ForgedSEO is a SaaS Agentic Content Engine — managed SEO service and Enterprise PaaS. Brand, CSS, and marketing templates live in this plugin. Twenty Twenty-Five stays the active theme; this repo does not vendor that theme.

Mirrors the Zoomies stack: plugin-centric shipping under `src/wp-content/plugins/forgedseo-core/`. Deploy is **prod only** (no staging).

## Site setup (Hostinger)

1. In wp-admin → Appearance, **activate Twenty Twenty-Five** (already installed; leave Hostinger Affiliate Theme inactive).
2. Deploy this plugin (push to `main`, or rsync once — see below).
3. In wp-admin → Plugins, **install is just the rsync**; **activate ForgedSEO Core**.
4. Rebuild marketing pages in the Site Editor / block editor. Use `[forgedseo_cta]` for the primary call-to-action if you want a branded button without custom CSS classes.

The plugin adds body class `forgedseo-core`, enqueues Inter, and loads `assets/css/forgedseo.css` so TT5 header, hero, sections, buttons, cards, and footer pick up forge-metal tokens (charcoal, ember/amber, paper whites). It does **not** hard-require Hostinger theme CSS.

## Deploy

Push to `main` when `src/wp-content/plugins/forgedseo-core/`, `.github/workflows/deploy.yml`, or `scripts/hostinger-purge-cache.sh` change.

GitHub Actions (`.github/workflows/deploy.yml`):

1. **Rsync** `src/wp-content/plugins/forgedseo-core/` → `${FORGEDSEO_PATH}/wp-content/plugins/forgedseo-core/`
2. **Cache bust** — `scripts/hostinger-purge-cache.sh` (Hostinger API if `HOSTINGER_API_TOKEN` is set; otherwise SSH + `wp litespeed-purge all` and `wp cache flush`)

Watch the run: `gh run list --workflow=deploy.yml` then `gh run watch`.

First deploy only copies files. Activate the plugin in wp-admin (or WP-CLI) afterward.

### GitHub repository secrets

| Secret | Purpose |
| --- | --- |
| `FORGEDSEO_PATH` | Absolute server path to the **production** site root (e.g. `domains/forgedseo.com/public_html`) |
| `HOSTINGER_HOST` | SSH hostname |
| `HOSTINGER_USERNAME` | SSH user |
| `HOSTINGER_PRIVATE_KEY` | SSH private key (full PEM, including headers) |
| `HOSTINGER_PORT` | SSH port (usually `22` or Hostinger’s custom port) |
| `HOSTINGER_API_TOKEN` | Optional. Hostinger API bearer token — preferred for cache purge when SSH is slow |

Do not put these values in the repo. Site constants (not secrets): Hostinger account `u774337398`, WordPress software id `27052573`, domain `forgedseo.com`.

### Manual cache purge

```bash
export HOSTINGER_API_TOKEN='…'
./scripts/hostinger-purge-cache.sh
```

Or with SSH fallback (`FORGEDSEO_PATH` = production site root):

```bash
export HOSTINGER_HOST='…'
export HOSTINGER_USERNAME='…'
export HOSTINGER_PRIVATE_KEY='…'
export HOSTINGER_PORT='…'
export FORGEDSEO_PATH='…'
./scripts/hostinger-purge-cache.sh
```

Bump `FORGEDSEO_CORE_VERSION` (plugin header + `define`) when CSS/JS change so browsers fetch new assets even before a full cache purge.

## Meow MCP (content only)

The Meow WordPress MCP connection is **content-only**: pages, posts, media, and Site Editor copy. It is **not** the deploy path. Plugin files ship through this repo’s GitHub Actions rsync. Do not use Meow MCP to upload or overwrite `forgedseo-core`.

## Layout

```
src/wp-content/plugins/forgedseo-core/
  forgedseo-core.php    bootstrap
  includes/             enqueue, CTA shortcode, AIOSEO meta→table sync
  assets/css/           design tokens + TT5 base styles
  assets/fonts/         self-hosted Inter variable woff2 (SIL OFL)
  assets/js/            header scroll affordance
scripts/hostinger-purge-cache.sh
.github/workflows/deploy.yml
```

## Changelog

Releases are tracked with [Changie](https://github.com/miniscruff/changie). See `CHANGELOG.md`.
