# ForgedSEO Core

Custom plugin for forgedseo.com. Holds design tokens, CSS, JS, the `[forgedseo_cta]` shortcode, the vector logo, and the Home layout as block patterns (`patterns/home-*.php`, combined as `forgedseo/home`). The Home page body is only `<!-- wp:pattern {"slug":"forgedseo/home"} /-->`, so the layout ships with the plugin. Saving Home in the block editor expands the pattern into the page and detaches it from plugin updates. Deployed by GitHub Actions rsync on push to `main` (production only).

Do not install this from WordPress.org. After rsync, activate in wp-admin (Plugins → ForgedSEO Core). Pair with the Twenty Twenty-Five theme.
