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

# 1. Start Sail
./vendor/bin/sail down
./vendor/bin/sail build && ./vendor/bin/sail up -d

# 2. Ensure Octane is installed
./vendor/bin/sail artisan octane:install --server=frankenphp --no-interaction

# Generate application key (if missing) and run migrations before clearing caches
./vendor/bin/sail artisan key:generate --force || true
./vendor/bin/sail artisan migrate --force || true

./vendor/bin/sail artisan optimize:clear

# 3. Start Octane inside the container (background)
./vendor/bin/sail artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000 &
OCTANE_PID=$!

# 4. Wait for Octane to be ready (curl from the host, mapped port must match docker-compose)
# Adjust the host port (default Sail maps 80 → container 80, but Octane runs on 8000 inside).
# If your docker-compose forwards 8000:8000 use port 8000; if it forwards 80:8000 use 80.
OCTANE_URL="${OCTANE_URL:-http://localhost:8000}"
echo "Waiting for Octane to be ready at $OCTANE_URL ..."
timeout=60
current_wait=0
until curl -sf -o /dev/null "$OCTANE_URL" || [ $current_wait -ge $timeout ]; do
    sleep 2
    current_wait=$((current_wait + 2))
done

if [ $current_wait -ge $timeout ]; then
    echo "ERROR: Octane did not become ready within ${timeout}s"
    kill $OCTANE_PID 2>/dev/null || true
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
kill $OCTANE_PID 2>/dev/null || true
./vendor/bin/sail down

exit $TEST_EXIT
