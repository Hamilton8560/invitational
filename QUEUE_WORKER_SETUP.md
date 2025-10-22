# Queue Worker Setup Guide

This application uses Laravel queues to send emails and process background jobs. For emails to be sent successfully, you need to have a queue worker running.

## Prerequisites

1. **Resend API Key**: Ensure your `.env` file has a valid `RESEND_KEY`
   ```env
   RESEND_KEY=re_your_actual_key_here
   ```

2. **Queue Connection**: The application uses the `database` queue driver (already configured)
   ```env
   QUEUE_CONNECTION=database
   ```

## Starting the Queue Worker

### Option 1: Local Development (Artisan)

Run this command in a separate terminal window:

```bash
php artisan queue:work --tries=3
```

**Keep this running while testing!** The queue worker must be active for emails to send.

### Option 2: Using Laravel Herd (macOS)

If you're using Laravel Herd, the queue worker should start automatically. If not:

1. Open Herd settings
2. Navigate to your site
3. Enable "Queue Worker" for the site

### Option 3: Production (Supervisor)

For production environments, use Supervisor to keep the queue worker running:

1. Install Supervisor:
   ```bash
   sudo apt-get install supervisor
   ```

2. Create a configuration file `/etc/supervisor/conf.d/laravel-worker.conf`:
   ```ini
   [program:laravel-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /path/to/your/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
   autostart=true
   autorestart=true
   stopasgroup=true
   killasgroup=true
   user=your-user
   numprocs=1
   redirect_stderr=true
   stdout_logfile=/path/to/your/app/storage/logs/worker.log
   stopwaitsecs=3600
   ```

3. Start Supervisor:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start laravel-worker:*
   ```

## Verifying Queue Worker is Running

### Check if jobs are being processed:
```bash
php artisan queue:monitor
```

### View failed jobs:
```bash
php artisan queue:failed
```

### Retry failed jobs:
```bash
php artisan queue:retry all
```

## Testing Email Delivery

### 1. Check Queue is Working
```bash
# Watch the queue in real-time
php artisan queue:work --verbose
```

### 2. Test Resend Configuration

Create a test route or run tinker:
```bash
php artisan tinker
```

Then test:
```php
use App\Notifications\SponsorshipPurchaseConfirmation;
use App\Models\{User, Sale};

$user = User::first();
$sale = Sale::where('sponsorship_id', '!=', null)->first();

$user->notify(new SponsorshipPurchaseConfirmation($sale, true));
```

Check your queue worker terminal to see if the job processes.

## Troubleshooting

### Emails Not Sending

1. **Queue worker not running**: Start `php artisan queue:work`
2. **Invalid Resend key**: Check `.env` for `RESEND_KEY`
3. **Failed jobs**: Check `php artisan queue:failed` for errors
4. **Transaction errors**: Fixed in latest code (removed nested transactions)

### Check Resend Dashboard

Visit https://resend.com/emails to see:
- Emails sent
- Delivery status
- Error messages
- API usage

### Common Issues

**"There is already an active transaction"**
- Fixed: Removed nested `DB::transaction()` in sponsorship purchase flow

**Emails queued but not sending**
- Solution: Ensure queue worker is running with `php artisan queue:work`

**No RESEND_KEY error**
- Solution: Add your Resend API key to `.env`

## Development vs Production

### Development
```bash
# Simple queue worker for testing
php artisan queue:work --verbose
```

### Production
- Use Supervisor (recommended)
- Or use Laravel Horizon for advanced queue monitoring
- Set up proper logging and monitoring

## Important Notes

- **Queue worker must be running** for ANY emails to send
- Restart queue worker after code changes: `php artisan queue:restart`
- Monitor failed jobs regularly
- Set up alerts for queue failures in production
