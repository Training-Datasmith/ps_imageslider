#!/usr/bin/env bash
set -euo pipefail

if [ "${RUN_MUTATION:-}" != "1" ]; then
  exit 0
fi

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
BACKUP_DIR="$(mktemp -d /tmp/ps-imageslider-mut-backup.XXXXXX)"
TARGET="$ROOT/classes/Ps_HomeSlide.php"
BACKUP="$BACKUP_DIR/Ps_HomeSlide.php"
TEST_IMAGE="ps-imageslider-test:local"
NETWORK="ps-imageslider-net"
MYSQL_NAME="ps-imageslider-mysql"

cp "$TARGET" "$BACKUP"
restore() {
  cp "$BACKUP" "$TARGET"
  rm -rf "$BACKUP_DIR"
}
trap restore EXIT INT TERM

export MUTATION_ROOT="$ROOT"
python3 - <<'PY'
from pathlib import Path
import os
root = os.environ.get("MUTATION_ROOT", ".")
path = Path(root) / "classes/Ps_HomeSlide.php"
text = path.read_text()
needle = "    public function reOrderPositions()\n    {\n        $context = Context::getContext();"
insert = "    public function reOrderPositions()\n    {\n        return true;\n        $context = Context::getContext();"
if needle not in text:
    raise SystemExit("mutation anchor not found")
path.write_text(text.replace(needle, insert, 1))
PY

set +e
docker run --rm --platform linux/amd64 --network "container:${MYSQL_NAME}" \
  -v "$ROOT:/src" -w /src \
  -e PS_DB_HOST=127.0.0.1 \
  -e PS_DB_USER=root \
  -e PS_DB_PASSWORD=root \
  -e PS_DB_NAME=imageslider \
  "$TEST_IMAGE" \
  php -d error_reporting=-1 /opt/phpunit/phpunit-4.8.36.phar -c /src/phpunit.xml.dist \
  --filter testDeletingSlideIdOneCompactsHigherPositions \
  tests/php/DeleteReorderTest.php > /tmp/mutation-out.txt 2>&1
status=$?
set -e

if [ "$status" -eq 0 ]; then
  echo "Mutation proof failed: targeted test still passed" >&2
  cat /tmp/mutation-out.txt >&2
  exit 1
fi

if ! grep -q 'testDeletingSlideIdOneCompactsHigherPositions' /tmp/mutation-out.txt; then
  echo "Mutation proof failed: targeted test did not run" >&2
  cat /tmp/mutation-out.txt >&2
  exit 1
fi

echo "Mutation proof ok"
