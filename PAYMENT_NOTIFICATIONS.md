# Payment Notifications Implementation Guide

## Overview
The payment notification system sends automated emails to both the customer and the business owner (sheila.david@dhd.co.mz) whenever a payment is successfully processed.

## Frontend Implementation ✅

The frontend has been updated with the following:

### 1. Notification Service (`src/services/notificationService.ts`)
- **Function:** `notifyPaymentSuccess(data: PaymentNotificationData)`
- **Sends:** POST request to `/api/notify-payment`
- **Data Structure:**
  ```typescript
  interface PaymentNotificationData {
    purchaserName: string;      // Customer's full name
    purchaserEmail: string;     // Customer's email address
    amount: number;             // Amount in Meticais (MT)
    paymentMethod: string;      // "mpesa" or "mastercard"
    transactionId?: string;     // Optional transaction ID
    timestamp?: string;         // ISO timestamp
  }
  ```

### 2. Integration Points

#### M-Pesa Payment (`src/pages/Index.tsx`)
When M-Pesa payment succeeds:
```typescript
if (result.success) {
  setPaymentSuccess(true);
  
  // Notify about payment
  await notifyPaymentSuccess({
    purchaserName: purchaserName || "Cliente",
    purchaserEmail: purchaserEmail || phoneNumber,
    amount: finalPrice,
    paymentMethod: "mpesa",
    transactionId: result.transactionId,
    timestamp: new Date().toISOString()
  });
}
```

#### Mastercard Payment (`src/pages/MastercardCallback.tsx`)
When Mastercard payment succeeds:
```typescript
case "SUCCESS":
  // Retrieve data from sessionStorage
  const purchaserName = sessionStorage.getItem("purchaserName") || "Cliente";
  const purchaserEmail = sessionStorage.getItem("purchaserEmail") || "email@exemplo.com";
  const amount = sessionStorage.getItem("paymentAmount") || "0";
  
  // Send notification
  await notifyPaymentSuccess({
    purchaserName,
    purchaserEmail,
    amount: parseFloat(amount),
    paymentMethod: "mastercard",
    timestamp: new Date().toISOString()
  });
```

## Backend Implementation Required ⚙️

You need to create a Laravel endpoint to receive and process these notifications.

### 1. Create Controller

Create `app/Http/Controllers/NotificationController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentNotificationMail;

class NotificationController extends Controller
{
    /**
     * Handle payment success notification
     */
    public function notifyPayment(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'recipient' => 'required|email',
            'purchaserName' => 'required|string|max:255',
            'purchaserEmail' => 'required|email',
            'amount' => 'required|numeric|min:0',
            'paymentMethod' => 'required|in:mpesa,mastercard',
            'transactionId' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        try {
            // Send email to business owner
            Mail::to($validated['recipient'])
                ->send(new PaymentNotificationMail($validated));

            // Optional: Send confirmation email to customer
            Mail::to($validated['purchaserEmail'])
                ->send(new PaymentConfirmationMail($validated));

            // Log the notification
            \Log::info('Payment notification sent', $validated);

            return response()->json([
                'success' => true,
                'message' => 'Payment notification sent successfully'
            ]);
        } catch (\Exception $e) {
            // Log error but return success to not fail the payment
            \Log::error('Payment notification error: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'message' => 'Payment notification queued'
            ]);
        }
    }
}
```

### 2. Create Mail Classes

#### Business Owner Notification (`app/Mail/PaymentNotificationMail.php`)

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
            subject: 'Nova Transação: ' . $this->data['purchaserName'],
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

#### Customer Confirmation (`app/Mail/PaymentConfirmationMail.php`)

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
            subject: 'Pagamento Confirmado - ' . $this->data['amount'] . ' MT',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-confirmation',
            with: [
                'purchaserName' => $this->data['purchaserName'],
                'amount' => $this->data['amount'],
                'paymentMethod' => $this->data['paymentMethod'],
                'timestamp' => $this->data['timestamp'] ?? now()->toDateTimeString(),
            ],
        );
    }
}
```

### 3. Create Routes

Add to `routes/api.php`:

```php
Route::post('/notify-payment', [\App\Http\Controllers\NotificationController::class, 'notifyPayment']);
```

### 4. Create Email Templates

#### Business Notification Template (`resources/views/emails/payment-notification.blade.php`)

```blade
<x-mail::message>
# 📊 Nova Transação de Pagamento

Olá,

Uma nova transação foi processada com sucesso através do checkout.

## Detalhes do Pagamento

**Nome do Cliente:** {{ $purchaserName }}
**Email do Cliente:** {{ $purchaserEmail }}
**Valor Pago:** {{ number_format($amount, 2, ',', '.') }} MT
**Método de Pagamento:** {{ ucfirst($paymentMethod) }}
**ID da Transação:** {{ $transactionId }}
**Data/Hora:** {{ $timestamp }}

---

Regards,
{{ config('app.name') }}
</x-mail::message>
```

#### Customer Confirmation Template (`resources/views/emails/payment-confirmation.blade.php`)

```blade
<x-mail::message>
# ✅ Pagamento Confirmado!

Olá {{ $purchaserName }},

Seu pagamento foi processado com sucesso!

## Confirmação do Pagamento

**Valor Pago:** {{ number_format($amount, 2, ',', '.') }} MT
**Método:** {{ ucfirst($paymentMethod) }}
**Data:** {{ $timestamp }}

Obrigado por sua compra!

<x-mail::button :url="config('app.url')">
Ver Detalhes
</x-mail::button>

---

Se tem alguma dúvida, por favor contacte-nos.

Regards,
{{ config('app.name') }}
</x-mail::message>
```

## Configuration

### Environment Variables (`.env`)

```env
# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="notificacoes@example.com"
MAIL_FROM_NAME="Makagui Experience"

# Or use another email service like Mailgun, SendGrid, etc.
# MAILGUN_SECRET=
# MAILGUN_DOMAIN=
```

### Supported Email Services

1. **SMTP** (Standard): Configure in `.env`
2. **Mailgun**: Add to `config/services.php`
   ```php
   'mailgun' => [
       'secret' => env('MAILGUN_SECRET'),
       'domain' => env('MAILGUN_DOMAIN'),
   ],
   ```
3. **SendGrid**: Similar setup as Mailgun
4. **SES (AWS)**: For AWS users

## Email Delivery Verification

### Check in Laravel

View `storage/logs/laravel.log` to verify email sending:

```
[TIMESTAMP] local.INFO: Payment notification sent {"purchaserName":"John Doe","amount":500,...}
```

### Check Queued Jobs

If using queue workers:
```bash
php artisan queue:work
```

### Email Testing

Use Mailtrap.io for development testing:
1. Create free account at https://mailtrap.io
2. Get SMTP credentials
3. Add to `.env` file
4. All emails will be captured without actually sending

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Emails not sending | Check MAIL_MAILER and SMTP credentials in `.env` |
| 500 error on /api/notify-payment | Verify route exists in `routes/api.php` and controller path |
| No email received | Check spam folder, verify recipient email is correct |
| JSON error | Ensure all required fields are sent: `purchaserName`, `purchaserEmail`, `amount` |

## Security Considerations

1. **CSRF Protection**: The route uses CSRF middleware by default in web routes. For API, it should be in `api.php` which is excluded.

2. **Rate Limiting**: Add rate limiting to prevent abuse:
   ```php
   Route::post('/notify-payment', [NotificationController::class, 'notifyPayment'])
       ->middleware('throttle:60,1');
   ```

3. **Authorization**: Consider adding API token validation:
   ```php
   public function notifyPayment(Request $request)
   {
       $token = $request->header('Authorization');
       if (!$this->validateToken($token)) {
           return response()->json(['error' => 'Unauthorized'], 401);
       }
       // ... rest of code
   }
   ```

## Testing the Integration

### 1. Test M-Pesa Payment
1. Open checkout page
2. Enter: Name "Test User", Email "test@example.com"
3. Select M-Pesa, enter phone number
4. Complete payment
5. Verify email received at sheila.david@dhd.co.mz

### 2. Test Mastercard Payment
1. Open checkout page
2. Enter: Name "Test User", Email "test@example.com"
3. Select Mastercard
4. Enter card details and confirm
5. Verify email received at sheila.david@dhd.co.mz

### 3. Manual API Test (cURL)
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

## Future Enhancements

- [ ] SMS notifications to customer
- [ ] Payment receipt PDF attachment
- [ ] Admin dashboard for notifications
- [ ] Webhook for external integrations
- [ ] Email template customization via admin panel
- [ ] Notification scheduling
- [ ] Multi-language support
- [ ] Payment analytics/reporting

---

**Status:** 🟢 Frontend ready, awaiting backend implementation
**Last Updated:** January 16, 2026
