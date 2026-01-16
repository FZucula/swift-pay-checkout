# Payment Notification API - Testing Guide

## API Endpoint

```
POST /api/notify-payment
Content-Type: application/json
```

---

## Request Payload

### Required Fields
```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "string",
  "purchaserEmail": "string",
  "amount": number,
  "paymentMethod": "string"
}
```

### Optional Fields
```json
{
  "transactionId": "string",
  "timestamp": "ISO-8601 string"
}
```

---

## Example Requests

### Example 1: M-Pesa Payment

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "João Silva",
  "purchaserEmail": "joao@example.com",
  "amount": 500.00,
  "paymentMethod": "mpesa",
  "transactionId": "TX20260116001",
  "timestamp": "2026-01-16T10:30:00Z"
}
```

### Example 2: Mastercard Payment

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "Maria Santos",
  "purchaserEmail": "maria@example.com",
  "amount": 1500.00,
  "paymentMethod": "mastercard",
  "transactionId": "MC20260116002",
  "timestamp": "2026-01-16T11:45:00Z"
}
```

### Example 3: Minimal Request

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "Test User",
  "purchaserEmail": "test@example.com",
  "amount": 100,
  "paymentMethod": "mpesa"
}
```

---

## Testing with cURL

### Basic Test
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

### Production Test
```bash
curl -X POST https://your-domain.com/api/notify-payment \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "recipient": "sheila.david@dhd.co.mz",
    "purchaserName": "João Silva",
    "purchaserEmail": "joao@example.com",
    "amount": 500,
    "paymentMethod": "mpesa",
    "transactionId": "TX20260116001"
  }'
```

---

## Testing with Postman

### 1. Create New Request
- Method: `POST`
- URL: `http://localhost:8000/api/notify-payment`

### 2. Headers Tab
```
Content-Type: application/json
Accept: application/json
```

### 3. Body Tab (raw, JSON)
```json
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

### 4. Click Send
Expected response:
```json
{
  "success": true,
  "message": "Payment notification sent successfully"
}
```

---

## Testing with Thunder Client (VS Code)

### 1. Install Extension
- Search "Thunder Client" in VS Code extensions
- Click Install

### 2. Create Request
- Click "New Request"
- Select method: POST
- Enter URL: `http://localhost:8000/api/notify-payment`

### 3. Add Headers
```
Content-Type: application/json
Accept: application/json
```

### 4. Add Body (JSON)
```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "Test User",
  "purchaserEmail": "test@example.com",
  "amount": 500,
  "paymentMethod": "mpesa"
}
```

### 5. Click Send
View response in right panel

---

## Testing with PHP Artisan Tinker

```bash
php artisan tinker

# Import the controller
use App\Http\Controllers\NotificationController;

# Create a controller instance
$controller = new NotificationController();

# Create a request
$request = new \Illuminate\Http\Request();
$request->merge([
    'recipient' => 'sheila.david@dhd.co.mz',
    'purchaserName' => 'Test User',
    'purchaserEmail' => 'test@example.com',
    'amount' => 500,
    'paymentMethod' => 'mpesa',
    'transactionId' => 'TX123456789'
]);

# Call the method
$response = $controller->notifyPayment($request);

# Check response
$response->getOriginalContent();
```

---

## Response Examples

### Success Response
```json
{
  "success": true,
  "message": "Payment notification sent successfully"
}
```

HTTP Status: `200 OK`

### Validation Error Response
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "purchaserName": [
      "The purchaser name field is required."
    ],
    "purchaserEmail": [
      "The purchaser email must be a valid email address."
    ]
  }
}
```

HTTP Status: `422 Unprocessable Entity`

### Server Error Response
```json
{
  "success": true,
  "message": "Payment notification queued"
}
```

HTTP Status: `200 OK` (returns success even on error to not fail payment)

---

## Validation Rules

### recipient
- Required: Yes
- Type: String (email)
- Format: Valid email address
- Default: sheila.david@dhd.co.mz

### purchaserName
- Required: Yes
- Type: String
- Max length: 255 characters
- Pattern: Any alphanumeric characters

### purchaserEmail
- Required: Yes
- Type: String (email)
- Format: Valid email address

### amount
- Required: Yes
- Type: Number
- Minimum: 0
- Format: Can be float (e.g., 500.00)

### paymentMethod
- Required: Yes
- Type: String
- Allowed values: "mpesa", "mastercard"
- Case-sensitive: No (both "MPESA" and "mpesa" work)

### transactionId
- Required: No
- Type: String
- Max length: 255 characters
- Pattern: Any characters allowed

### timestamp
- Required: No
- Type: String (ISO-8601)
- Format: YYYY-MM-DDTHH:MM:SSZ
- Example: "2026-01-16T10:30:00Z"

---

## Test Scenarios

### Scenario 1: Happy Path - M-Pesa
**Objective:** Verify notification sent successfully for M-Pesa payment

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "João Silva",
  "purchaserEmail": "joao@example.com",
  "amount": 500.00,
  "paymentMethod": "mpesa",
  "transactionId": "TX20260116001",
  "timestamp": "2026-01-16T10:30:00Z"
}
```

**Expected:**
- HTTP 200
- Email sent to sheila.david@dhd.co.mz
- Email contains all payment details
- Response: `{"success": true, "message": "..."}`

---

### Scenario 2: Happy Path - Mastercard
**Objective:** Verify notification sent successfully for Mastercard payment

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "Maria Santos",
  "purchaserEmail": "maria@example.com",
  "amount": 1500.00,
  "paymentMethod": "mastercard",
  "transactionId": "MC20260116002"
}
```

**Expected:**
- HTTP 200
- Email sent to sheila.david@dhd.co.mz
- Email contains all payment details
- Response: `{"success": true, "message": "..."}`

---

### Scenario 3: Missing Required Field
**Objective:** Verify validation of required fields

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "Test User",
  "amount": 500,
  "paymentMethod": "mpesa"
}
```

**Expected:**
- HTTP 422
- Error message: "The purchaser email field is required."
- No email sent

---

### Scenario 4: Invalid Email
**Objective:** Verify email validation

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "Test User",
  "purchaserEmail": "not-an-email",
  "amount": 500,
  "paymentMethod": "mpesa"
}
```

**Expected:**
- HTTP 422
- Error message: "The purchaser email must be a valid email address."
- No email sent

---

### Scenario 5: Invalid Amount
**Objective:** Verify numeric validation

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "Test User",
  "purchaserEmail": "test@example.com",
  "amount": -500,
  "paymentMethod": "mpesa"
}
```

**Expected:**
- HTTP 422
- Error message: "The amount must be at least 0."
- No email sent

---

### Scenario 6: Invalid Payment Method
**Objective:** Verify payment method validation

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "Test User",
  "purchaserEmail": "test@example.com",
  "amount": 500,
  "paymentMethod": "bitcoin"
}
```

**Expected:**
- HTTP 422
- Error message: "The selected payment method is invalid."
- No email sent

---

### Scenario 7: Large Amount
**Objective:** Verify handling of large amounts

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "VIP Customer",
  "purchaserEmail": "vip@example.com",
  "amount": 999999999.99,
  "paymentMethod": "mastercard",
  "transactionId": "MC9999999999"
}
```

**Expected:**
- HTTP 200
- Email sent with large amount properly formatted
- Response: `{"success": true, "message": "..."}`

---

### Scenario 8: Special Characters in Name
**Objective:** Verify handling of special characters

```json
{
  "recipient": "sheila.david@dhd.co.mz",
  "purchaserName": "João Maria de Araújo Neta-Silva",
  "purchaserEmail": "joao@example.com",
  "amount": 500,
  "paymentMethod": "mpesa"
}
```

**Expected:**
- HTTP 200
- Email sent with name properly displayed (UTF-8 encoding)
- Response: `{"success": true, "message": "..."}`

---

## Load Testing

### Test with Apache Bench
```bash
ab -n 100 -c 10 -p payload.json -T application/json \
  http://localhost:8000/api/notify-payment
```

### Test with wrk
```bash
wrk -t4 -c100 -d30s \
  -s notify-script.lua \
  http://localhost:8000/api/notify-payment
```

---

## Monitoring Email Delivery

### Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

### Look for Lines Like
```
[2026-01-16 10:30:00] local.INFO: Payment notification sent {"purchaserName":"João Silva",...}
[2026-01-16 10:30:01] local.INFO: Mail sent to sheila.david@dhd.co.mz
```

### Check Mailtrap (if using)
1. Go to https://mailtrap.io
2. Login to your account
3. Check "Inbox" folder
4. View message details
5. Verify email content

---

## Troubleshooting Tests

### Test Fails: Connection Refused
**Problem:** Cannot connect to /api/notify-payment
**Solution:**
1. Ensure Laravel server is running: `php artisan serve`
2. Check URL is correct: `http://localhost:8000/api/notify-payment`
3. Verify route exists: `php artisan route:list | grep notify-payment`

### Test Fails: 404 Not Found
**Problem:** Route not found
**Solution:**
1. Check route added to `routes/api.php`
2. Clear route cache: `php artisan route:cache`
3. Verify controller file exists

### Test Fails: 422 Validation Error
**Problem:** Required field missing
**Solution:**
1. Check all required fields in payload
2. Verify field names are correct (case-sensitive)
3. Check field values are valid

### Test Fails: Email Not Received
**Problem:** Email not received at destination
**Solution:**
1. Check MAIL_MAILER in .env
2. Verify SMTP credentials are correct
3. Check spam/junk folder
4. Look in Mailtrap inbox (if using)
5. Check Laravel logs for errors

---

## Automation Scripts

### Bash Script for Testing
```bash
#!/bin/bash

# test-notification.sh

ENDPOINT="http://localhost:8000/api/notify-payment"

echo "Testing Payment Notification API..."

# Test 1: M-Pesa
echo ""
echo "Test 1: M-Pesa Payment"
curl -X POST $ENDPOINT \
  -H "Content-Type: application/json" \
  -d '{
    "recipient": "sheila.david@dhd.co.mz",
    "purchaserName": "Test M-Pesa",
    "purchaserEmail": "test-mpesa@example.com",
    "amount": 500,
    "paymentMethod": "mpesa"
  }'

# Test 2: Mastercard
echo ""
echo "Test 2: Mastercard Payment"
curl -X POST $ENDPOINT \
  -H "Content-Type: application/json" \
  -d '{
    "recipient": "sheila.david@dhd.co.mz",
    "purchaserName": "Test Mastercard",
    "purchaserEmail": "test-mc@example.com",
    "amount": 1500,
    "paymentMethod": "mastercard"
  }'

echo ""
echo "Tests completed!"
```

### Usage
```bash
chmod +x test-notification.sh
./test-notification.sh
```

---

## Success Checklist

- [ ] Can successfully POST to /api/notify-payment
- [ ] Valid payload returns HTTP 200
- [ ] Invalid payload returns HTTP 422
- [ ] Email received at sheila.david@dhd.co.mz
- [ ] Email contains customer name
- [ ] Email contains customer email
- [ ] Email contains payment amount
- [ ] Email contains payment method
- [ ] All formatting looks professional
- [ ] No errors in Laravel logs
- [ ] Response time is acceptable (< 1 second)

---

**Ready to Test?** Start with Example 1 using cURL or Postman!
