#!/usr/bin/env bash
set -euo pipefail

DB_PATH=${1:-/workspace/PANEL/data/panel.sqlite}
SCHEMA_PATH=${2:-/workspace/PANEL/data/schema.sql}

mkdir -p "$(dirname "$DB_PATH")"
sqlite3 "$DB_PATH" < "$SCHEMA_PATH"

printf "Databáze byla inicializována: %s\n" "$DB_PATH"
