# ForgedSEO Core

Custom plugin for forgedseo.com. Holds design tokens, CSS, JS, the `[forgedseo_cta]` shortcode, the vector logo, and the site layouts as block patterns. Deployed by GitHub Actions rsync on push to `main` (production only).

Layouts are built from shared section builders in `includes/sections.php`:

- Home: `patterns/home-*.php`, combined as `forgedseo/home`.
- Managed Service, Enterprise, About, Privacy Policy and Terms: `patterns/page-*.php` (`forgedseo/page-*`). Each page body is only `<!-- wp:pattern {"slug":"forgedseo/page-…"} /-->`, set by the `forgedseo_core_pages` migration in `includes/pages.php`.
- Blog index, single post and 404: `patterns/template-*.php`, referenced from the Twenty Twenty-Five `home`, `single` and `404` templates in the database by the same migration.

Saving a page or template in the editor expands the pattern and detaches it from plugin updates. To re-attach, delete the `forgedseo_core_pages` (or `forgedseo_core_home`) option and load any page.

Do not install this from WordPress.org. After rsync, activate in wp-admin (Plugins → ForgedSEO Core). Pair with the Twenty Twenty-Five theme.
