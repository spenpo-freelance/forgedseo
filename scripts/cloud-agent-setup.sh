#!/usr/bin/env bash
#
# Idempotent Cloud Agent install step for the forgedseo-core Playground site.
#
# Installs @wp-playground/cli (the supported replacement for deprecated wp-now),
# caches a few public production images for the seed step, and warms WordPress so
# the first agent boot is not a cold PHP.wasm download.
#
# Safe to run repeatedly. Does not write to production.
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PREFIX="${FORGEDSEO_PLAYGROUND_PREFIX:-$HOME/.forgedseo-playground}"
STATE_DIR="${FORGEDSEO_PLAYGROUND_STATE:-$HOME/forgedseo-wp-dev}"
MEDIA_DIR="$STATE_DIR/media-cache"
CLI_VERSION="${FORGEDSEO_PLAYGROUND_CLI_VERSION:-3.1.55}"
LIVE_ORIGIN="https://forgedseo.com"

log() { printf '\n\033[1;36m==> %s\033[0m\n' "$1"; }

need_node() {
  if ! command -v node >/dev/null 2>&1; then
    echo "Node.js 20.18+ is required for @wp-playground/cli." >&2
    exit 1
  fi
  local major
  major="$(node -p "parseInt(process.versions.node, 10)")"
  if [ "$major" -lt 20 ]; then
    echo "Node.js $(node -v) is too old; Playground CLI needs 20.18+." >&2
    exit 1
  fi
}

download_media() {
  local url="$1"
  local dest="$2"
  if [ -s "$dest" ]; then
    return 0
  fi
  curl -fsSL --retry 3 --retry-delay 2 "$url" -o "$dest.partial"
  mv "$dest.partial" "$dest"
}

need_node
mkdir -p "$PREFIX" "$MEDIA_DIR"

if [ ! -x "$PREFIX/node_modules/.bin/wp-playground-cli" ]; then
  log "Installing @wp-playground/cli@$CLI_VERSION"
  npm install --prefix "$PREFIX" --no-fund --no-audit "@wp-playground/cli@$CLI_VERSION"
else
  log "@wp-playground/cli already installed; skipping npm"
fi

log "Caching public production media for the seed step (read-only)"
download_media "$LIVE_ORIGIN/wp-content/uploads/2026/03/cropped-forged-seo-app-logo.png" "$MEDIA_DIR/header-lockup.png"
download_media "$LIVE_ORIGIN/wp-content/uploads/2026/03/forged-seo-s-logo-dim-1.png" "$MEDIA_DIR/org-logo.png"
download_media "$LIVE_ORIGIN/wp-content/uploads/2026/03/cropped-forged-seo-favicon-192x192.png" "$MEDIA_DIR/favicon.png"

if [ "${FORGEDSEO_PLAYGROUND_SKIP_WARMUP:-}" = "1" ]; then
  log "Setup complete (warmup skipped)."
  exit 0
fi

if ls "$HOME/.wordpress-playground/sites/"*/wordpress/wp-load.php >/dev/null 2>&1; then
  log "Persisted WordPress already present; skipping warmup boot"
else
  log "Warming Playground (downloads WordPress + PHP.wasm, then stops)"
  bash "$REPO_DIR/scripts/cloud-agent-start.sh" --warmup
fi

log "Setup complete. Start the site with: bash scripts/cloud-agent-start.sh"
log "Site: http://127.0.0.1:8080   Admin: http://127.0.0.1:8080/wp-admin  (admin / admin)"
