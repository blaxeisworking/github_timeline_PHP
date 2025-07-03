DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

PHP_PATH=$(which php)

CRON_JOB="*/5 * * * * $PHP_PATH $DIR/cron.php > /dev/null 2>&1"

(crontab -l 2>/dev/null | grep -F "$DIR/cron.php") >/dev/null

if [ $? -eq 0 ]; then
    echo "✅ CRON job already exists."
else
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "✅ CRON job added successfully: Runs every 5 minutes."
fi
