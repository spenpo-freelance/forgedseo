#!/usr/bin/env bash
# Purge LiteSpeed + Hostinger CDN/server cache for forgedseo.com.
# Used after deploy (GitHub Actions or manual). Prefers Hostinger API; falls back to SSH + WP-CLI.
set -euo pipefail

HOSTINGER_API_BASE="${HOSTINGER_API_BASE:-https://developers.hostinger.com/api}"
HOSTINGER_DOMAIN="${HOSTINGER_DOMAIN:-forgedseo.com}"
HOSTINGER_ACCOUNT_USERNAME="${HOSTINGER_ACCOUNT_USERNAME:-u774337398}"
HOSTINGER_WP_SOFTWARE_ID="${HOSTINGER_WP_SOFTWARE_ID:-27052573}"

purge_via_api() {
  if [[ -z "${HOSTINGER_API_TOKEN:-}" ]]; then
    return 1
  fi

  echo "Purging LiteSpeed cache via Hostinger API (software ${HOSTINGER_WP_SOFTWARE_ID})…"
  curl -fsS -X POST \
    "${HOSTINGER_API_BASE}/hosting/v1/accounts/${HOSTINGER_ACCOUNT_USERNAME}/wordpress/${HOSTINGER_WP_SOFTWARE_ID}/litespeed-cache/purge" \
    -H "Authorization: Bearer ${HOSTINGER_API_TOKEN}" \
    -H "Content-Type: application/json"

  echo "Clearing Hostinger website/CDN cache for ${HOSTINGER_DOMAIN}…"
  curl -fsS -X DELETE \
    "${HOSTINGER_API_BASE}/hosting/v1/accounts/${HOSTINGER_ACCOUNT_USERNAME}/websites/${HOSTINGER_DOMAIN}/cache/clear" \
    -H "Authorization: Bearer ${HOSTINGER_API_TOKEN}"

  echo "Hostinger API cache purge complete."
}

purge_via_ssh() {
  if [[ -z "${HOSTINGER_HOST:-}" || -z "${HOSTINGER_USERNAME:-}" || -z "${HOSTINGER_PRIVATE_KEY:-}" || -z "${FORGEDSEO_PATH:-}" ]]; then
    return 1
  fi

  local port="${HOSTINGER_PORT:-22}"
  local key_file
  key_file="$(mktemp)"
  trap 'rm -f "$key_file"' RETURN
  printf '%s\n' "${HOSTINGER_PRIVATE_KEY}" >"${key_file}"
  chmod 600 "${key_file}"

  echo "Purging LiteSpeed and WordPress object cache via SSH + WP-CLI…"
  ssh -i "${key_file}" \
    -p "${port}" \
    -o StrictHostKeyChecking=no \
    -o ConnectTimeout=60 \
    "${HOSTINGER_USERNAME}@${HOSTINGER_HOST}" \
    "cd '${FORGEDSEO_PATH}' && wp litespeed-purge all && wp cache flush"

  echo "SSH cache purge complete."
}

if purge_via_api; then
  exit 0
fi

if purge_via_ssh; then
  exit 0
fi

echo "No cache purge method available. Set HOSTINGER_API_TOKEN or SSH deploy secrets." >&2
exit 1
