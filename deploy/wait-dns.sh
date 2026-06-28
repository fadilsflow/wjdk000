#!/usr/bin/env bash
# Tunggu DNS A record aktif, lalu restart Caddy agar SSL diterbitkan ulang.
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"
source .env.prod
EXPECTED_IP="${EXPECTED_IP:-43.134.191.125}"
DOMAIN="${DOMAIN:-smartsprayer.web.id}"
echo "Menunggu $DOMAIN -> $EXPECTED_IP ..."
for i in $(seq 1 60); do
  IP=$(dig +short "$DOMAIN" A @1.1.1.1 | tail -n1 || true)
  if [[ "$IP" == "$EXPECTED_IP" ]]; then
    echo "DNS OK ($IP)"
    sudo docker compose --env-file .env.prod -f compose.prod.yml restart caddy
    echo "Caddy di-restart. Cek: https://$DOMAIN/up"
    exit 0
  fi
  echo "[$i/60] belum: ${IP:-kosong}"
  sleep 30
done
echo "DNS belum mengarah ke VPS setelah 30 menit. Pastikan record A sudah disimpan di panel." >&2
exit 1
