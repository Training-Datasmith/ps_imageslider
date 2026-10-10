#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PHP_IMAGE="php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143"
MYSQL_IMAGE="mysql@sha256:dab0a802b44617303694fb17d166501de279c3031ddeb28c56ecf7fcab5ef0da"
NETWORK="ps-imageslider-net"
MYSQL_NAME="ps-imageslider-mysql"
TEST_IMAGE="ps-imageslider-test:local"
PHPUNIT_PHAR="/opt/phpunit/phpunit-4.8.36.phar"
PHPUNIT_SHA256="ab8bc3017a64cf75a2112f5e089a1d5b0c4cc2116556d60c1af5e3f584e85c5e"
EXPECTED_TEST_FILES=16
EXPECTED_TESTS=97

assert_junit_count() {
  local file="$1"
  local count failures errors
  count="$(grep -o 'tests="[0-9]*"' "$file" | head -1 | sed 's/tests="//;s/"//')"
  failures="$(grep -o 'failures="[0-9]*"' "$file" | head -1 | sed 's/failures="//;s/"//')"
  errors="$(grep -o 'errors="[0-9]*"' "$file" | head -1 | sed 's/errors="//;s/"//')"
  if [ "$count" != "$EXPECTED_TESTS" ] || [ "$failures" != "0" ] || [ "$errors" != "0" ]; then
    echo "JUnit gate failed: tests=${count} failures=${failures} errors=${errors}" >&2
    cat "$file" >&2
    exit 1
  fi
}

mkdir -p "$ROOT/build"
JUNIT="$ROOT/build/junit.xml"

docker build --platform linux/amd64 -f tests/docker/Dockerfile -t "$TEST_IMAGE" "$ROOT"

echo "${PHPUNIT_SHA256}  ${PHPUNIT_PHAR}" | docker run -i --rm --platform linux/amd64 "$TEST_IMAGE" sha256sum -c -

docker network inspect "$NETWORK" >/dev/null 2>&1 || docker network create "$NETWORK"

if ! docker ps --format '{{.Names}}' | grep -qx "$MYSQL_NAME"; then
  docker rm -f "$MYSQL_NAME" >/dev/null 2>&1 || true
  docker run -d --platform linux/amd64 --name "$MYSQL_NAME" --network "$NETWORK" \
    -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=imageslider \
    "$MYSQL_IMAGE" --character-set-server=utf8 --collation-server=utf8_general_ci
fi

TRIES=60
until docker exec "$MYSQL_NAME" mysqladmin --silent -uroot -proot ping >/dev/null 2>&1; do
  TRIES=$((TRIES - 1))
  if [ "$TRIES" -le 0 ]; then
    echo "MySQL did not become ready" >&2
    exit 1
  fi
  sleep 1
done

ACTUAL_FILES="$(find tests/php -maxdepth 1 -name '*Test.php' | wc -l | tr -d ' ')"
if [ "$ACTUAL_FILES" != "$EXPECTED_TEST_FILES" ]; then
  echo "Expected ${EXPECTED_TEST_FILES} test files, found ${ACTUAL_FILES}" >&2
  exit 1
fi

run_phpunit() {
  docker run --rm --platform linux/amd64 --network "container:${MYSQL_NAME}" \
    -v "$ROOT:/src" -w /src \
    -e PS_DB_HOST=127.0.0.1 \
    -e PS_DB_USER=root \
    -e PS_DB_PASSWORD=root \
    -e PS_DB_NAME=imageslider \
    "$TEST_IMAGE" \
    php -d error_reporting=-1 "$PHPUNIT_PHAR" -c /src/phpunit.xml.dist --log-junit /src/build/junit.xml "$@"
}

run_phpunit
assert_junit_count "$JUNIT"

run_phpunit
assert_junit_count "$JUNIT"

ORDER_FILE="$ROOT/build/order.php"
cat > "$ORDER_FILE" <<'PHP'
<?php
error_reporting(-1);
$files = array(
'tests/php/IndexGuardTest.php',
'tests/php/VersionGuardTest.php',
'tests/php/PsHomeSlideDefinitionTest.php',
'tests/php/ValidateUrlTest.php',
'tests/php/SlidesQueryTest.php',
'tests/php/WidgetVariablesTest.php',
'tests/php/InstallConfigTest.php',
'tests/php/PostValidationTest.php',
'tests/php/PostProcessTest.php',
'tests/php/DeleteReorderTest.php',
'tests/php/ShopDuplicationTest.php',
'tests/php/AdminConfigureSlidesControllerTest.php',
'tests/php/HeaderStatusFieldsTest.php',
'tests/php/UpgradeModuleTest.php',
'tests/php/ShopMessagesTest.php',
'tests/php/GetContentBranchTest.php',
);
srand(20261008);
$shuffled = $files;
shuffle($shuffled);
foreach ($shuffled as $file) {
    echo $file, "\n";
}
PHP

mapfile -t SHUFFLED1 < <(docker run --rm --platform linux/amd64 -v "$ROOT:/src" -w /src "$TEST_IMAGE" php -d error_reporting=-1 /src/build/order.php)
run_phpunit "${SHUFFLED1[@]}"
assert_junit_count "$JUNIT"

mapfile -t SHUFFLED2 < <(docker run --rm --platform linux/amd64 -v "$ROOT:/src" -w /src "$TEST_IMAGE" php -d error_reporting=-1 /src/build/order.php)
run_phpunit "${SHUFFLED2[@]}"
assert_junit_count "$JUNIT"

if [ "${RUN_MUTATION:-}" = "1" ]; then
  bash tests/php/mutation-proof.sh
fi

echo "Suite gate passed (${EXPECTED_TESTS} tests)."
