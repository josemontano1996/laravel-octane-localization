#!/bin/bash
set -e

# Ensure we are in the script's directory
cd "$(dirname "$0")"

# 1. Prepare environment
composer update --no-scripts --no-interaction

if [ ! -f ".env" ]; then
    echo "Creating .env from .env.example"
    cp .env.example .env
fi

# Ensure sqlite database file exists and is writable inside the runtime folder
mkdir -p database
if [ ! -f database/database.sqlite ]; then
    echo "Creating database/database.sqlite"
    touch database/database.sqlite
fi
chmod 0666 database/database.sqlite || true

# 2. Define cleanup function
cleanup() {
    echo "--------------------------------------------------"
    echo "Cleaning up..."
    ./vendor/bin/sail down || true
    rm -f .env
}
trap cleanup EXIT

# 3. Start Sail
./vendor/bin/sail build && ./vendor/bin/sail up -d

# 4. Wait for Octane
# OCTANE_URL should be the base URL for the test script.
export OCTANE_URL="${OCTANE_URL:-http://localhost:80}"
HEALTH_CHECK_URL="$OCTANE_URL/unlocalized"

echo "Waiting for Octane to be ready at $HEALTH_CHECK_URL ..."
timeout=60
current_wait=0
until curl -sf -o /dev/null "$HEALTH_CHECK_URL" || [ $current_wait -ge $timeout ]; do
    sleep 2
    current_wait=$((current_wait + 2))
done

if [ $current_wait -ge $timeout ]; then
    echo "ERROR: Octane did not become ready within ${timeout}s"
    exit 1
fi

echo "Octane is ready."

# 5. Run concurrency bleed test
TOTAL=${1:-100}
CONCURRENCY=${2:-3}
php concurrent_bleedtest.php -t "$TOTAL" -c "$CONCURRENCY"
TEST_EXIT=$?

exit $TEST_EXIT
