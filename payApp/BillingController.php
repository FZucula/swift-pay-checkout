<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

use App\Services\ZohoService;
use App\Services\ZohoInvoiceService;
use App\Services\VimeoService;
use App\Services\ZohoCouponsService;
use App\Services\ZohoCRMService;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Payment;

class BillingController extends Controller
{
    private $zoho;
    private $zohoIS;
    private $vimeo;
    private $coupons;
    private $zohoCRM;
    private $generatedPassword;

    public function __construct(
        ZohoService $zoho,
        ZohoInvoiceService $zohoIS,
        VimeoService $vimeo,
        ZohoCouponsService $coupons,
        ZohoCRMService $zohoCRM
    ) {
        $this->middleware('auth')->except(['checkout', 'processMpesaPayment', 'processVodacomApp']);
        $this->zoho = $zoho;
        $this->zohoIS = $zohoIS;
        $this->vimeo = $vimeo;
        $this->coupons = $coupons;
        $this->zohoCRM = $zohoCRM;
        $this->generatedPassword = Str::random(12);
    }

    /**
     * Processar compra gratuita (cupão 100%)
     */
    public function processFreePurchase(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->jsonError('Usuário não autenticado', 401);
            }

            $validated = $request->validate([
                'package' => 'required|array',
                'package.plan_code' => 'required|string',
                'user_id' => 'required|exists:users,id'
            ]);

            if ($user->id != $validated['user_id']) {
                return $this->jsonError('Usuário não autorizado', 403);
            }

            $package = $validated['package'];
            $couponCode = $package['cupon_code'] ?? null;

            // Buscar plano no Zoho
            $plan = $this->getZohoPlan($package['plan_code']);
            if (!$plan) {
                return $this->jsonError('Plano não encontrado', 404);
            }

            $subscription_id = $user->subscriptions()->first()?->id;

            // Criar pagamento gratuito
            $payment = $this->createFreePayment($user, $plan, $couponCode, $subscription_id);

            // Preparar dados da assinatura
            $subscriptionData = [
                'package' => [
                    'package_name' => $plan['name'],
                    'package_id' => $plan['plan_id'],
                    'plan_code' => $plan['plan_code'],
                    'cupon_code' => $couponCode
                ],
                'payment_method' => 'free_coupon',
                'amount' => 0.00,
                'payment_response' => [
                    'output_TransactionID' => $payment->transaction_id,
                    'output_ResponseCode' => 'FREE-100'
                ],
                'user' => $user,
                'customerData' => [],
                'coupon' => $couponCode,
            ];

            // Processar assinatura
            $subscriptionResult = $this->processUserSubscription($subscriptionData);

            if (!$subscriptionResult['success']) {
                $payment->delete();
                return $this->jsonError('Falha ao processar assinatura gratuita', 500);
            }

            // Atualizar cupão no Zoho
            if ($couponCode) {
                $this->updateZohoCoupon($couponCode);
            }

            // Criar Deal no Zoho CRM
            $this->createZohoCRMDeal($user, 0.00, 'Closed Won', 'Cupom Gratuito');

            return response()->json([
                'success' => true,
                'transaction_id' => $payment->transaction_id,
                'redirect_url' => route('payment.result', [
                    'status' => 'success',
                    'plano' => $plan['plan_code'] ?? '',
                    'results' => $payment->transaction_id
                ]),
                'message' => 'Plano ativado com sucesso!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonError('Dados inválidos', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Free purchase error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return $this->jsonError('Erro ao processar compra gratuita', 500);
        }
    }

    /**
     * Processar pagamento M-Pesa
     */
    public function processMpesaPayment(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->jsonError('Usuário não autenticado', 401);
            }

            $validated = $request->validate([
                'input_number' => 'required|string|regex:/^8[4-5]\d{7}$/',
                'package' => 'required|array',
                'package.plan_code' => 'required|string'
            ]);

            $couponCode = $request->input('package.cupon_code');
            $customerData = $request->input('customerData', []);
            $planCode = $validated['package']['plan_code'];

            // Buscar plano e aplicar cupão
            $plan = $this->getZohoPlan($planCode);
            if (!$plan) {
                return $this->jsonError('Plano não encontrado', 404);
            }

            $amount = $this->applyCouponToAmount($couponCode, $plan['recurring_price']);
            if (!$amount['success']) {
                return $this->jsonError($amount['message'], 400);
            }

            $finalAmount = $amount['new_amount'];

            // Processar transação M-Pesa
            $mpesaResponse = $this->processMpesaTransaction($finalAmount, $validated['input_number']);
            if (!$mpesaResponse['success']) {
                $this->createZohoCRMDeal($user, $finalAmount, 'Pending payment', 'M-Pesa');
                return $this->jsonError($mpesaResponse['message'], 400);
            }

            // Criar registro de pagamento
            $payment = $this->createPaymentRecord($user, $planCode, $finalAmount, 'mpesa', $mpesaResponse['transaction_id']);

            // Preparar dados da assinatura
            $subscriptionData = [
                'package' => $validated['package'],
                'payment_method' => 'mpesa',
                'amount' => $finalAmount,
                'payment_response' => [
                    'output_TransactionID' => $mpesaResponse['transaction_id'],
                    'output_ResponseCode' => 'INS-0'
                ],
                'user' => $user,
                'customerData' => $customerData,
                'coupon' => $couponCode,
            ];

            // Processar assinatura
            $subscriptionResult = $this->processUserSubscription($subscriptionData);

            if (!$subscriptionResult['success']) {
                $this->createZohoCRMDeal($user, $finalAmount, 'Pending payment', 'M-Pesa');
                return $this->jsonError('Falha ao processar assinatura', 500);
            }

            // Atualizar cupão se aplicável
            if ($couponCode) {
                $this->updateZohoCoupon($couponCode);
            }

            // Criar Deal no Zoho CRM
            $this->createZohoCRMDeal($user, $finalAmount, 'Closed Won', 'M-Pesa');

            return response()->json([
                'success' => true,
                'transaction_id' => $mpesaResponse['transaction_id'],
                'redirect_url' => route('payment.result', [
                    'status' => 'success',
                    'plano' => $planCode ?? '',
                    'results' => $mpesaResponse['transaction_id']
                ]),
                'message' => 'Pagamento iniciado com sucesso. Confirme o PIN no seu dispositivo M-Pesa.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonError('Dados inválidos', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('M-Pesa payment error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            $amount = $request->input('package.amount') ?? 0;
            $this->createZohoCRMDeal(auth()->user(), $amount, 'Pending payment', 'M-Pesa');

            return $this->jsonError('Erro no processamento do pagamento', 500);
        }
    }

    /**
     * Iniciar pagamento com Mastercard
     */
    public function initiateMastercardPayment(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->jsonError('Usuário não autenticado', 401);
            }

            $validated = $request->validate([
                'package' => 'required|array',
                'package.plan_code' => 'required|string',
                'customerData' => 'sometimes|array'
            ]);

            $couponCode = $request->input('package.cupon_code');
            $customerData = $request->input('customerData', []);
            $planCode = $validated['package']['plan_code'];

            // Buscar plano e aplicar cupão
            $plan = $this->getZohoPlan($planCode);
            if (!$plan) {
                return $this->jsonError('Plano não encontrado', 404, null, 'PLAN_NOT_FOUND');
            }

            $amount = $this->applyCouponToAmount($couponCode, $plan['recurring_price']);
            if (!$amount['success']) {
                return $this->jsonError($amount['message'], 400, null, 'INVALID_COUPON');
            }

            $finalAmount = $amount['new_amount'];

            // Validar valor mínimo
            if ($finalAmount < 10) {
                return $this->jsonError('Valor do pagamento é muito baixo', 400, null, 'AMOUNT_TOO_LOW');
            }

            // Processar com gateway Mastercard
            $token = config('services.mastercard.token');
            $response = Http::withoutVerifying()
                ->post('https://pagamentos.interactive.co.mz/api/pay/mastercard', [
                    'input_amount' => $finalAmount,
                    'input_token' => $token,
                ]);

            $result = $response->json();

            if (!$response->successful() || empty($result['session']['id'])) {
                $errorCode = $result['error']['code'] ?? 'GATEWAY_ERROR';
                $this->createZohoCRMDeal($user, $finalAmount, 'Pending payment', 'Mastercard');

                return $this->jsonError(
                    $result['message'] ?? 'Erro no gateway de pagamento',
                    400,
                    null,
                    $errorCode,
                    $this->isRetryableError($errorCode)
                );
            }

            // Preparar dados da sessão
            $sessionData = [
                'successIndicator' => $result['successIndicator'],
                'sessionVersion' => $result['session']['version'],
                'resId' => $result['session']['id'],
                'package' => $validated['package'],
                'amount' => $finalAmount,
                'paymentMethod' => 'cartao',
                'userId' => $user->id,
                'customerData' => $customerData,
                'coupon' => $couponCode,
            ];

            session(['holdedData' => encrypt($sessionData)]);

            return response()->json([
                'success' => true,
                'session_id' => $result['session']['id'],
                'checkout_config' => [
                    'merchant' => 15413,
                    'interaction' => [
                        'merchant' => [
                            'name' => 'STV Play',
                            'address' => [
                                'line1' => 'Maputo',
                                'line2' => 'Katembe'
                            ]
                        ],
                        'locale' => 'pt_BR',
                        'displayControl' => [
                            'billingAddress' => 'HIDE',
                            'customerEmail' => 'HIDE',
                            'shipping' => 'HIDE'
                        ]
                    ]
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonError('Dados inválidos', 422, $e->errors(), 'VALIDATION_ERROR');
        } catch (\Exception $e) {
            Log::error('Mastercard initiation error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            $amount = $request->input('package.amount') ?? 0;
            $this->createZohoCRMDeal(auth()->user(), $amount, 'Pending payment', 'Mastercard');

            return $this->jsonError('Erro temporário no processamento', 503, null, 'INTERNAL_ERROR', true);
        }
    }

    /**
     * Processar assinatura do usuário (usado pelo callback do Mastercard)
     */
    public function processUserSubscription($subscriptionData = null)
    {
        try {
            if ($subscriptionData === null) {
                // Para pagamentos com cartão (callback do banco)
                $resultIndicator = request()->query('resultIndicator');
                if (empty($resultIndicator)) {
                    return $this->redirectWithError('Parâmetro resultIndicator em falta');
                }

                $sessionData = decrypt(session('holdedData'));
                if ($sessionData['successIndicator'] !== $resultIndicator) {
                    $this->createZohoCRMDeal(
                        User::find($sessionData['userId']),
                        $sessionData['amount'],
                        'Pending payment',
                        'Mastercard'
                    );
                    return $this->redirectWithError('Falha na verificação do pagamento');
                }

                $user = User::find($sessionData['userId']);
                $subscription = $user->subscriptions()->first();

                // Criar registro de pagamento
                $payment = $this->createPaymentRecord(
                    $user,
                    $sessionData['package']['plan_code'],
                    $sessionData['amount'],
                    'cartao',
                    $sessionData['resId']
                );

                $subscriptionData = [
                    'package' => $sessionData['package'],
                    'payment_method' => 'cartao',
                    'amount' => $sessionData['amount'],
                    'payment_response' => ['output_TransactionID' => $resultIndicator],
                    'user' => $user,
                    'customerData' => $sessionData['customerData'],
                    'coupon' => $sessionData['coupon'],
                ];
            }

            $user = $subscriptionData['user'];
            $periodicity = $subscriptionData['package']['plan_code'] ?? 'mensal';

            // Verificar se precisa criar integrações
            $subscription = Subscription::where('user_id', $user->id)->first();
            $isNewIntegration = false;

            if (!$subscription || empty($subscription->zoho_customer_id)) {
                // Criar integrações no Zoho e Vimeo
                $integrationResult = $this->createUserIntegrations($user, $periodicity);
                if (!$integrationResult['success']) {
                    throw new \Exception($integrationResult['message']);
                }

                $subscription = $integrationResult['subscription'];
                $isNewIntegration = true;
            } else {
                // Atualizar integrações existentes
                $this->zoho->updateSubscription(
                    $subscription->zoho_subscription_id,
                    ['status' => 'live', 'auto_collect' => false]
                );
                $this->vimeo->restoreUserProduct($subscription->vimeo_customer_id, 223376);
                $subscription->update(['status' => 'active']);
            }

            // Criar pagamento no Zoho
            $invoiceId = $this->getSubscriptionInvoiceId($subscription, $isNewIntegration);
            if ($invoiceId) {
                $this->createZohoPayment($subscription, $invoiceId, $subscriptionData);
            }

            // Atualizar cupão no Zoho
            if (!empty($subscriptionData['coupon'])) {
                $this->updateZohoCoupon($subscriptionData['coupon']);
            }

            // Criar Deal no Zoho CRM
            $this->createZohoCRMDeal(
                $user,
                $subscriptionData['amount'],
                'Closed Won',
                $subscriptionData['payment_method']
            );

            // Limpar dados da sessão se existirem
            if (session()->has('holdedData')) {
                session()->forget('holdedData');
                $planCode = $subscriptionData['package']['plan_code'] ?? '';
                return $this->redirectToResults($payment->transaction_id ?? 'MC-' . time(), $planCode);
            }

            return [
                'success' => true,
                'subscription' => $subscription,
                'payment' => [
                    'method' => $subscriptionData['payment_method'],
                    'amount' => $subscriptionData['amount'],
                    'transaction_id' => $subscriptionData['payment_response']['output_TransactionID']
                ],
                'message' => 'Assinatura processada com sucesso'
            ];

        } catch (\Exception $e) {
            Log::error('Subscription processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (session()->has('holdedData')) {
                session()->forget('holdedData');
                return $this->redirectWithError('Erro ao processar a sua assinatura');
            }

            return [
                'success' => false,
                'message' => 'Erro ao processar assinatura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Página de resultados do pagamento
     */
    public function paymentResult(Request $request)
    {
        $status = $request->query('status');
        $plano = $request->query('plano');
        $results = $request->query('results');

        if ($status === 'success' && $results) {
            $transaction = Payment::where('transaction_id', $results)->first();

            if ($transaction) {
                session()->flash('success', 'O pagamento foi concluído com sucesso!');
                session()->flash('amount', $transaction->amount);
                session()->flash('subscription_id', $transaction->subscription_id);
                session()->flash('plano', $plano ?: '');
                session()->flash('transaction_id', $results);
            } else {
                session()->flash('error', 'Transação inválida ou inexistente.');
            }
        } else {
            // Mesmo sem transaction_id, armazenar dados na sessão
            session()->flash('status', $status);
            session()->flash('plano', $plano ?: '');
            session()->flash('transaction_id', $results ?: '');
        }

        return view('results');
    }

    // ================= MÉTODOS PRIVADOS AUXILIARES =================

    /**
     * Criar registro de pagamento
     */
    private function createPaymentRecord($user, $planCode, $amount, $method, $transactionId)
    {
        $subscription = $user->subscriptions()->first();

        return Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription?->id,
            'amount' => $amount,
            'currency' => 'MZN',
            'payment_method' => $method,
            'product_slug' => $planCode,
            'status' => 'done',
            'transaction_id' => $transactionId,
            'zoho_payment_id' => '',
            'receipt_url' => '',
            'metadata' => json_encode([
                'processed_at' => now()->toDateTimeString(),
                'method' => $method
            ])
        ]);
    }

    /**
     * Criar pagamento gratuito
     */
    private function createFreePayment($user, $plan, $couponCode, $subscription_id)
    {
        return Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription_id,
            'amount' => 0.00,
            'currency' => 'MZN',
            'payment_method' => 'free_coupon',
            'product_slug' => $plan['plan_code'],
            'status' => 'done',
            'transaction_id' => 'FREE-' . time() . rand(100, 999),
            'zoho_payment_id' => '',
            'receipt_url' => '',
            'metadata' => json_encode([
                'coupon_code' => $couponCode,
                'plan_name' => $plan['name'],
                'original_price' => $plan['recurring_price'],
                'processed_at' => now()->toDateTimeString()
            ])
        ]);
    }

    /**
     * Processar transação M-Pesa
     */
    private function processMpesaTransaction($amount, $phoneNumber)
    {
        $token = config('services.mpesa.token');

        $response = Http::withoutVerifying()->post('https://pagamentos.interactive.co.mz/api/pay/mpesa', [
            'input_token' => $token,
            'input_amount' => $amount,
            'input_number' => '258' . $phoneNumber,
        ]);

        $data = $response->json();

        if ($response->failed() || ($data['output_ResponseCode'] ?? null) !== "INS-0") {
            return [
                'success' => false,
                'message' => $data['output_ResponseDesc'] ?? 'Falha na comunicação com o gateway'
            ];
        }

        return [
            'success' => true,
            'transaction_id' => $data['output_TransactionID'],
            'message' => $data['output_ResponseDesc'] ?? 'Pagamento iniciado com sucesso'
        ];
    }

    /**
     * Obter plano do Zoho
     */
    private function getZohoPlan($planCode)
    {
        $zohoPlans = Cache::remember('zoho_plans', now()->addMinutes(30), function() {
            return $this->zoho->getPlans();
        });

        return collect($zohoPlans)->firstWhere('plan_code', $planCode);
    }

    /**
     * Aplicar cupão ao valor
     */
    private function applyCouponToAmount($couponCode, $amount)
    {
        if (!$couponCode) {
            return ['success' => true, 'new_amount' => $amount];
        }

        try {
            $cupomData = Cache::remember("coupon_{$couponCode}", now()->addMinutes(30), function() use ($couponCode) {
                return $this->coupons->obterCupom($couponCode);
            });

            $coupon = $cupomData['coupon'] ?? null;

            if (!$coupon || empty($coupon['coupon_code'])) {
                return ['success' => false, 'message' => 'Cupom não encontrado'];
            }

            // Validações do cupom
            if ($coupon['is_active'] !== true) {
                return ['success' => false, 'message' => 'Cupom inativo'];
            }

            if (!empty($coupon['expiry_at']) && now()->gt(\Carbon\Carbon::parse($coupon['expiry_at']))) {
                return ['success' => false, 'message' => 'Cupom expirado'];
            }

            // Calcular desconto
            $discount = $this->calculateCouponDiscount($coupon, $amount);
            $finalAmount = $amount - $discount;

            if ($finalAmount < 0) {
                return ['success' => false, 'message' => 'Valor final inválido após desconto'];
            }

            return [
                'success' => true,
                'new_amount' => $finalAmount,
                'discount' => $discount
            ];

        } catch (\Exception $e) {
            Log::warning('Coupon application error', [
                'coupon' => $couponCode,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => 'Erro ao aplicar cupom'];
        }
    }

    /**
     * Calcular desconto do cupom
     */
    private function calculateCouponDiscount($coupon, $amount)
    {
        $discountType = $coupon['discount_by'] ?? 'flat';
        $discountValue = (float)$coupon['discount_value'];
        $maxDiscount = isset($coupon['max_discount_amount']) ? (float)$coupon['max_discount_amount'] : null;

        $discount = 0;

        switch ($discountType) {
            case 'percentage':
                $discount = $amount * ($discountValue / 100);
                if ($maxDiscount !== null) {
                    $discount = min($discount, $maxDiscount);
                }
                break;

            case 'flat':
                $discount = min($discountValue, $amount);
                break;
        }

        return round($discount, 2);
    }

    /**
     * Criar integrações do usuário
     */
    private function createUserIntegrations($user, $periodicity)
    {
        try {
            // Obter produto ativo do Vimeo
            $products = $this->vimeo->getProducts();
            $product = collect($products)->firstWhere('is_active', true);

            if (!$product) {
                throw new \Exception('Nenhum produto ativo encontrado');
            }

            // Criar cliente no Vimeo
            $vimeoCustomer = $this->vimeo->createCustomerWithSubscription([
                'email' => $user->email,
                'name' => $user->name,
                'password' => $this->generatedPassword,
                'metadata' => ['laravel_user_id' => $user->id]
            ], $product['id'], $periodicity, false);

            if (empty($vimeoCustomer['id'])) {
                throw new \Exception('Falha ao criar cliente no Vimeo');
            }

            // Criar cliente no Zoho
            $zohoCustomer = $this->zoho->createCustomer([
                'display_name' => $user->name,
                'email' => $user->email,
                'first_name' => $user->name,
                'last_name' => $user->name,
                'currency_code' => 'MZN',
            ]);

            if (empty($zohoCustomer['customer']['customer_id'])) {
                throw new \Exception('Falha ao criar cliente no Zoho');
            }

            // Criar assinatura no Zoho
            $zohoSubscription = $this->zoho->createSubscription([
                'customer_id' => $zohoCustomer['customer']['customer_id'],
                'plan' => ['plan_code' => $periodicity],
                'start_date' => now()->format('Y-m-d'),
                'auto_collect' => false,
                'salesperson_name' => 'Middleware App',
            ]);

            // Criar ou atualizar assinatura local
            $subscription = Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'zoho_customer_id' => $zohoCustomer['customer']['customer_id'],
                    'zoho_subscription_id' => $zohoSubscription['subscription']['subscription_id'] ?? null,
                    'vimeo_customer_id' => $vimeoCustomer['id'],
                    'vimeo_subscription_id' => $vimeoCustomer['id'],
                    'status' => 'active',
                ]
            );

            return [
                'success' => true,
                'subscription' => $subscription,
                'invoice_id' => $zohoSubscription['subscription']['child_invoice_id'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('User integration error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obter ID da fatura da assinatura
     */
    private function getSubscriptionInvoiceId($subscription, $isNewIntegration)
    {
        if ($isNewIntegration) {
            return null; // Já obtido durante criação
        }

        $latestInvoice = $this->zohoIS->getLatestInvoiceForSubscription($subscription->zoho_subscription_id);
        return $latestInvoice['invoice_id'] ?? null;
    }

    /**
     * Criar pagamento no Zoho
     */
    private function createZohoPayment($subscription, $invoiceId, $subscriptionData)
    {
        try {
            $paymentData = [
                'customer_id' => $subscription->zoho_customer_id,
                'payment_mode' => $subscriptionData['payment_method'],
                'amount' => (float)$subscriptionData['amount'],
                'date' => now()->format('Y-m-d'),
                'reference_number' => $subscriptionData['payment_response']['output_TransactionID'] ?? 'PAGTO-' . uniqid(),
                'description' => 'Payment via ' . $subscriptionData['payment_method'],
                'invoices' => [
                    [
                        'invoice_id' => $invoiceId,
                        'amount_applied' => (float)$subscriptionData['amount']
                    ]
                ]
            ];

            $this->zoho->createPayment($paymentData);

        } catch (\Exception $e) {
            Log::error('Zoho payment creation error', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Criar Deal no Zoho CRM
     */
    private function createZohoCRMDeal($user, $amount, $stage, $source)
    {
        try {
            $this->zohoCRM->createDeal([
                'data' => [
                    [
                        'Deal_Name' => 'Assinatura STV Play - ' . $user->email,
                        'Amount' => (float)$amount,
                        'Expected_Revenue' => (float)$amount,
                        'Stage' => $stage,
                        'Closing_Date' => now()->format('Y-m-d'),
                        'Account_Name' => $user->email,
                        'Description' => "Assinatura via $source - Processado pelo middleware STV Play",
                        '$currency_symbol' => 'MZN',
                        'Lead_Source' => 'STV Play - ' . $source,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::warning('Zoho CRM deal creation error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Atualizar cupão no Zoho
     */
    private function updateZohoCoupon(string $couponCode)
    {
        try {
            $cupom = $this->coupons->obterCupom($couponCode);
            $dadosCupom = $cupom['coupon'];

            $redemptionCount = (int)($dadosCupom['redemption_count'] ?? 0) + 1;
            $maxRedemption = (int)($dadosCupom['max_redemption'] ?? 0);

            $updateData = ['redemption_count' => $redemptionCount];

            if ($maxRedemption > 0 && $redemptionCount >= $maxRedemption) {
                $updateData['status'] = 'maxed_out';
            }

            $this->coupons->atualizarCupom($couponCode, $updateData);

        } catch (\Exception $e) {
            Log::error("Coupon update error [$couponCode]: " . $e->getMessage());
        }
    }

    /**
     * Redirecionar para página de resultados
     */
    private function redirectToResults($transactionId, $planCode = '')
    {
        return redirect()->route('payment.result', [
            'status' => 'success',
            'plano' => $planCode,
            'results' => $transactionId
        ]);
    }

    /**
     * Redirecionar com erro
     */
    private function redirectWithError($message)
    {
        return redirect()->route('payment.result')->with([
            'error' => $message,
            'success' => false
        ]);
    }

    /**
     * Retornar erro JSON formatado
     */
    private function jsonError($message, $code = 400, $errors = null, $errorCode = null, $retryable = false)
    {
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'retryable' => $retryable
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Verificar se erro é retryable
     */
    private function isRetryableError($errorCode)
    {
        $nonRetryableCodes = ['INVALID_AMOUNT', 'INVALID_CARD', 'INVALID_TOKEN'];
        return !in_array($errorCode, $nonRetryableCodes);
    }

    // Métodos restantes mantidos por compatibilidade
    public function processVodacomApp(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'plan_code' => 'required|string',
                'trade_no' => 'required|string',
                'result_code' => 'required|string',
                'valor' => 'required|numeric|min:0'
            ]);

            // Verificar se o pagamento foi bem-sucedido
            if ($validated['result_code'] !== '200') {
                return $this->jsonError('Pagamento não autorizado', 400, null, 'PAYMENT_FAILED');
            }

            // Buscar usuário
            $user = \App\Models\User::find($validated['user_id']);
            if (!$user) {
                return $this->jsonError('Usuário não encontrado', 404);
            }

            // Buscar plano no Zoho
            $plan = $this->getZohoPlan($validated['plan_code']);
            if (!$plan) {
                return $this->jsonError('Plano não encontrado', 404);
            }

            $amount = $validated['valor'];

            // Verificar se já existe assinatura para o usuário
            $subscription = \App\Models\Subscription::where('user_id', $user->id)->first();

            if (!$subscription || empty($subscription->zoho_customer_id)) {
                // Criar integrações no Zoho e Vimeo
                $integrationResult = $this->createUserIntegrations($user, $validated['plan_code']);
                if (!$integrationResult['success']) {
                    return $this->jsonError($integrationResult['message'], 500);
                }
                $subscription = $integrationResult['subscription'];
            }

            // Criar registro de pagamento
            $payment = $this->createPaymentRecord($user, $validated['plan_code'], $amount, 'vodacom', $validated['trade_no']);

            // Atualizar status da assinatura se necessário
            if ($subscription->status !== 'active') {
                $subscription->update(['status' => 'active']);
            }

            // Criar Deal no Zoho CRM
            $this->createZohoCRMDeal($user, $amount, 'Closed Won', 'Vodacom');

            return response()->json([
                'success' => true,
                'transaction_id' => $validated['trade_no'],
                'message' => 'Pagamento efectuado com sucesso. Disfrute do STV Play.',
                'subscription' => $subscription
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonError('Dados inválidos', 422, $e->errors());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Vodacom payment processing error', [
                'user_id' => $request->input('user_id'),
                'trade_no' => $request->input('trade_no'),
                'error' => $e->getMessage()
            ]);

            return $this->jsonError('Erro no processamento do pagamento', 500);
        }
    }
}
