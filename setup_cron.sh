#!/bin/bash

# Setup script for testimonials sync cron job
# This script helps set up the cron job for automatic testimonials syncing

PROJECT_ROOT=$(pwd)
CRON_SCRIPT="$PROJECT_ROOT/system/cron/sync_testimonials.php"
LOG_DIR="$PROJECT_ROOT/system/logs"

echo "Setting up testimonials sync cron job..."

# Create logs directory if it doesn't exist
if [ ! -d "$LOG_DIR" ]; then
    mkdir -p "$LOG_DIR"
    echo "Created logs directory: $LOG_DIR"
fi

# Make the cron script executable
chmod +x "$CRON_SCRIPT"
echo "Made cron script executable"

# Create the cron job entry
CRON_ENTRY="0 2 * * * /usr/bin/php $CRON_SCRIPT >> $LOG_DIR/testimonials_cron.log 2>&1"

echo ""
echo "Cron job entry to add:"
echo "$CRON_ENTRY"
echo ""

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -q "sync_testimonials.php"; then
    echo "Testimonials sync cron job already exists!"
    echo "Current crontab entries containing 'sync_testimonials.php':"
    crontab -l 2>/dev/null | grep "sync_testimonials.php"
else
    echo "Adding cron job..."
    
    # Add the cron job
    (crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -
    
    if [ $? -eq 0 ]; then
        echo "✅ Cron job added successfully!"
        echo "The testimonials will be synced every day at 2:00 AM"
    else
        echo "❌ Failed to add cron job. Please add it manually:"
        echo "$CRON_ENTRY"
    fi
fi

echo ""
echo "Current crontab:"
crontab -l 2>/dev/null || echo "No crontab entries found"

echo ""
echo "You can also manually trigger the sync by running:"
echo "php $CRON_SCRIPT"
echo ""
echo "Or via the web API:"
echo "curl -X POST http://yoursite.com/api/sync-testimonials"
echo ""
echo "To view logs:"
echo "tail -f $LOG_DIR/testimonials_cron.log"
