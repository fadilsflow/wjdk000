#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

ENV_FILE="${ENV_FILE:-.env.prod}"
COMPOSE_FILE="${COMPOSE_FILE:-compose.prod.yml}"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Missing $ENV_FILE" >&2
  exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker not installed. Run: curl -fsSL https://get.docker.com | sh" >&2
  exit 1
fi

if docker compose version >/dev/null 2>&1; then
  COMPOSE=(docker compose)
elif command -v docker-compose >/dev/null 2>&1; then
  COMPOSE=(docker-compose)
else
  echo "docker compose plugin not found" >&2
  exit 1
fi

set -a
# shellcheck disable=SC1090
source "$ENV_FILE"
set +a

DOMAIN="${DOMAIN:-smartsprayer.web.id}"
export DOMAIN ACME_EMAIL APP_URL

echo "==> Pull images"
"${COMPOSE[@]}" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" pull

echo "==> Start stack ($DOMAIN)"
"${COMPOSE[@]}" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" up -d

echo "==> Status"
"${COMPOSE[@]}" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" ps

echo ""
echo "Site: https://${DOMAIN}"
echo "Health: https://${DOMAIN}/up"
echo "Admin WhatsApp QR: https://${DOMAIN}/admin/whatsapp"
echo "Logs: ${COMPOSE[*]} --env-file $ENV_FILE -f $COMPOSE_FILE logs -f"
