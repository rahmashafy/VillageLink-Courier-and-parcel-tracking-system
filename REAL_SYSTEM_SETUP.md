# Village Link Real System Setup

Run these after MySQL is started and the `smart_courier_system` database exists:

```bash
php artisan migrate
php artisan storage:link
npm run build
```

## Real Payment Gateway

Set PayHere keys in `.env`:

```env
PAYHERE_ENABLED=true
PAYHERE_SANDBOX=true
PAYHERE_MERCHANT_ID=your_merchant_id
PAYHERE_MERCHANT_SECRET=your_merchant_secret
PAYHERE_CURRENCY=LKR
```

PayHere server notifications are received at:

```text
/payments/payhere/notify
```

## Mail

Forgot password, complaint replies, and parcel status notifications use Laravel mail. For real email delivery, set SMTP credentials:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_smtp_app_password
MAIL_FROM_ADDRESS=no-reply@villagelink.lk
MAIL_FROM_NAME="Village Link"
```

Use your provider's real SMTP values. For Gmail, use an App Password, not the normal login password. After editing `.env`, run `php artisan config:clear`.

## AI Assistant

The assistant works locally with system data by default. To make it live with OpenAI, set:

```env
AI_ASSISTANT_PROVIDER=openai
AI_ASSISTANT_ENDPOINT=https://api.openai.com/v1/responses
AI_ASSISTANT_API_KEY=your_openai_api_key
AI_ASSISTANT_MODEL=gpt-4o-mini
```

You can also set `OPENAI_API_KEY` instead of `AI_ASSISTANT_API_KEY`. The app sends role-aware parcel, payment, complaint, driver, report, live-map, and setup context to the assistant.

## GPS / Delivery Proof

Drivers can start live GPS from the delivery status page. Customers see pickup, drop-off, route line, and vehicle marker on the tracking page. Delivery proof photos and complaint photos require:

```bash
php artisan storage:link
```
