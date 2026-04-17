#!/bin/bash
set -e

# Ensure we are in the script's directory
cd "$(dirname "$0")"

composer update --no-scripts --no-interaction

# Ensure .env exists
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

./vendor/bin/sail down
./vendor/bin/sail build && ./vendor/bin/sail up -d

OCTANE_URL="${OCTANE_URL:-http://localhost:80/unlocalized}"
echo "Waiting for Octane to be ready at $OCTANE_URL ..."
timeout=60
current_wait=0
until curl -sf -o /dev/null "$OCTANE_URL" || [ $current_wait -ge $timeout ]; do
    sleep 2
    current_wait=$((current_wait + 2))
done

if [ $current_wait -ge $timeout ]; then
    echo "ERROR: Octane did not become ready within ${timeout}s"
    ./vendor/bin/sail down
    exit 1
fi

echo "Octane is ready."

# 5. Run concurrency bleed test
TOTAL=${1:-20}
CONCURRENCY=${2:-3}
php concurrent_bleedtest.php -t "$TOTAL" -c "$CONCURRENCY"
TEST_EXIT=$?

# 6. Cleanup
rm -f .env
./vendor/bin/sail down

exit $TEST_EXIT
