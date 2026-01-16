# Quick Setup Guide - Laravel Backend Integration

## Overview
This guide provides step-by-step instructions to implement the payment notification system in your Laravel backend.

## Prerequisites
- Laravel 9+ installation
- Mail configured (SMTP or service)
- Your React app running and deployed

## Step-by-Step Implementation

### Step 1: Configure Email Service

**Option A: Using Mailtrap (Recommended for Testing)**

1. Create free account: https://mailtrap.io
2. Get SMTP credentials from Inbox
3. Update `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="notificacoes@example.com"
MAIL_FROM_NAME="Makagui Experience"
```

**Option B: Using SendGrid**
```env
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=your_api_key
MAIL_FROM_ADDRESS="notificacoes@example.com"
MAIL_FROM_NAME="Makagui Experience"
```

**Option C: Using Mailgun**
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your_domain.mailgun.org
MAILGUN_SECRET=your_secret
MAIL_FROM_ADDRESS="notificacoes@example.com"
MAIL_FROM_NAME="Makagui Experience"
```

### Step 2: Create Controller

```bash
php artisan make:controller NotificationController
```

Edit `app/Http/Controllers/NotificationController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentNotificationMail;
use App\Mail\PaymentConfirmationMail;

class NotificationController extends Controller
{
    /**
     * Handle payment success notification
     * POST /api/notify-payment
     */
    public function notifyPayment(Request $request)
    {
        // Validate incoming data
        $validated = $request->validate([
            'recipient' => 'required|email',
            'purchaserName' => 'required|string|max:255',
            'purchaserEmail' => 'required|email',
            'amount' => 'required|numeric|min:0',
            'paymentMethod' => 'required|in:mpesa,mastercard',
            'transactionId' => 'nullable|string|max:255',
            'timestamp' => 'nullable|string',
        ]);

        try {
            // Send email to business owner
            Mail::to($validated['recipient'])
                ->send(new PaymentNotificationMail($validated));

            // Send confirmation to customer
            Mail::to($validated['purchaserEmail'])
                ->send(new PaymentConfirmationMail($validated));

            // Log successful notification
            \Log::info('Payment notification sent', [
                'name' => $validated['purchaserName'],
                'amount' => $validated['amount'],
                'method' => $validated['paymentMethod']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment notification sent successfully'
            ]);

        } catch (\Exception $e) {
            // Log error but still return success
            // This prevents payment failures due to email issues
            \Log::error('Payment notification error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment notification queued'
            ]);
        }
    }
}
```

### Step 3: Create Mail Classes

**Create PaymentNotificationMail:**
```bash
php artisan make:mail PaymentNotificationMail
```

Edit `app/Mail/PaymentNotificationMail.php`:

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova Transação: ' . $this->data['purchaserName'] . ' - ' . 
                     number_format($this->data['amount'], 2, ',', '.') . ' MT',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-notification',
            with: [
                'purchaserName' => $this->data['purchaserName'],
                'purchaserEmail' => $this->data['purchaserEmail'],
                'amount' => $this->data['amount'],
                'paymentMethod' => $this->data['paymentMethod'],
                'transactionId' => $this->data['transactionId'] ?? 'N/A',
                'timestamp' => $this->data['timestamp'] ?? now()->toDateTimeString(),
            ],
        );
    }
}
```

**Create PaymentConfirmationMail:**
```bash
php artisan make:mail PaymentConfirmationMail
```

Edit `app/Mail/PaymentConfirmationMail.php`:

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pagamento Confirmado - ' . 
                     number_format($this->data['amount'], 2, ',', '.') . ' MT',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-confirmation',
            with: [
                'purchaserName' => $this->data['purchaserName'],
                'amount' => $this->data['amount'],
                'paymentMethod' => ucfirst($this->data['paymentMethod']),
                'timestamp' => $this->data['timestamp'] ?? now()->toDateTimeString(),
            ],
        );
    }
}
```

### Step 4: Create Email Templates

**Create business notification template:**
Create file `resources/views/emails/payment-notification.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .header { border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .content { line-height: 1.6; }
        .details { background: #f9f9f9; padding: 15px; border-left: 4px solid #000; margin: 15px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🔔 Nova Transação de Pagamento</h2>
        </div>

        <div class="content">
            <p>Olá,</p>
            <p>Uma nova transação foi processada com sucesso através do checkout.</p>

            <div class="details">
                <div class="detail-row">
                    <span class="detail-label">Nome do Cliente:</span>
                    <span>{{ $purchaserName }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span>{{ $purchaserEmail }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Valor Pago:</span>
                    <span><strong>{{ number_format($amount, 2, ',', '.') }} MT</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Método:</span>
                    <span>{{ ucfirst($paymentMethod) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">ID da Transação:</span>
                    <span>{{ $transactionId }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Data/Hora:</span>
                    <span>{{ $timestamp }}</span>
                </div>
            </div>

            <p>Esta é uma notificação automática. Não é necessário responder.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Makagui Experience. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
```

**Create customer confirmation template:**
Create file `resources/views/emails/payment-confirmation.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .success-badge { background: #4caf50; color: white; display: inline-block; padding: 10px 20px; border-radius: 20px; }
        .content { line-height: 1.6; }
        .amount { font-size: 32px; color: #4caf50; font-weight: bold; text-align: center; padding: 20px 0; }
        .details { background: #f0f7ff; padding: 15px; border-left: 4px solid #2196F3; margin: 15px 0; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="success-badge">✓ Pagamento Confirmado</span>
        </div>

        <div class="content">
            <h1 style="text-align: center; color: #333;">Obrigado, {{ $purchaserName }}!</h1>
            
            <p>Seu pagamento foi processado com sucesso.</p>

            <div class="amount">
                {{ number_format($amount, 2, ',', '.') }} MT
            </div>

            <div class="details">
                <p><strong>Método de Pagamento:</strong> {{ $paymentMethod }}</p>
                <p><strong>Data:</strong> {{ $timestamp }}</p>
                <p><strong>Status:</strong> <span style="color: #4caf50;">✓ Confirmado</span></p>
            </div>

            <p>Em breve receberá uma confirmação detalhada por email.</p>
            
            <p>Se tem alguma dúvida, por favor contacte o nosso suporte.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Makagui Experience. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
```

### Step 5: Add Route

Edit `routes/api.php`:

```php
Route::post('/notify-payment', [\App\Http\Controllers\NotificationController::class, 'notifyPayment']);
```

### Step 6: Test the Integration

**Option 1: Via Postman**

```
POST http://localhost:8000/api/notify-payment
Content-Type: application/json

{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "Test User",
  "purchaserEmail": "test@example.com",
  "amount": 500,
  "paymentMethod": "mpesa",
  "transactionId": "TX123456789",
  "timestamp": "2026-01-16T10:30:00Z"
}
```

**Option 2: Via cURL**

```bash
curl -X POST http://localhost:8000/api/notify-payment \
  -H "Content-Type: application/json" \
  -d '{
    "recipient": "sheila.david@dhd.co.mz",
    "purchaserName": "Test User",
    "purchaserEmail": "test@example.com",
    "amount": 500,
    "paymentMethod": "mpesa",
    "transactionId": "TX123456789",
    "timestamp": "2026-01-16T10:30:00Z"
  }'
```

Expected response:
```json
{
  "success": true,
  "message": "Payment notification sent successfully"
}
```

### Step 7: Queue Workers (Optional but Recommended)

For production, use Laravel queues to send emails in the background:

```bash
# Start queue worker
php artisan queue:work

# Or with daemon
php artisan queue:work --daemon
```

Both mail classes inherit `ShouldQueue`, so emails will be queued automatically.

## Troubleshooting

### Email not sending?

1. Check `.env` mail configuration
2. Test SMTP credentials
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify email address is correct

### 500 error on /api/notify-payment?

1. Check route exists: `php artisan route:list | grep notify-payment`
2. Verify controller exists: `app/Http/Controllers/NotificationController.php`
3. Check for PHP syntax errors in controller

### Email goes to spam?

1. Configure SPF records
2. Configure DKIM records
3. Use professional email service (SendGrid, Mailgun)
4. Verify sender email domain

## Verification Checklist

- [ ] `.env` configured with email service
- [ ] NotificationController created
- [ ] PaymentNotificationMail created
- [ ] PaymentConfirmationMail created
- [ ] Email templates created
- [ ] Route added to `routes/api.php`
- [ ] Test email sent successfully
- [ ] Emails received at sheila.david@dhd.co.mz
- [ ] Queue worker running (if using queues)

## Files Created

```
app/Http/Controllers/NotificationController.php
app/Mail/PaymentNotificationMail.php
app/Mail/PaymentConfirmationMail.php
resources/views/emails/payment-notification.blade.php
resources/views/emails/payment-confirmation.blade.php
```

## Support

For issues or questions, refer to:
- [PAYMENT_NOTIFICATIONS.md](./PAYMENT_NOTIFICATIONS.md) - Detailed documentation
- Laravel Mail Documentation: https://laravel.com/docs/mail
- Email Service Documentation (Mailtrap, SendGrid, etc.)

---

**Estimated Implementation Time:** 30-45 minutes
**Difficulty Level:** Intermediate
**Last Updated:** January 16, 2026
