# Fluxo Completo de Pagamento Mastercard

## Arquitetura

A implementação do Mastercard divide-se entre:
- **Frontend (React)**: Formulário simplificado, popup do Mastercard, rota de callback
- **Backend (Laravel)**: Inicialização do pagamento, validação de cupons, processamento do resultado

---

## 1️⃣ Fluxo de Pagamento

```
Usuário
   ↓
[React] Página Index.tsx
   ├─ Seleciona "Cartão" como método
   ├─ Vê informações sobre pagamento seguro
   └─ Clica "Pagar Agora"
   ↓
[React] Função handleMastercardPayment()
   ↓
[Backend] POST /initiate-mastercard-payment
   ├─ Valida dados do pacote
   ├─ Busca plano em Zoho
   ├─ Aplica cupom se houver
   ├─ Chamar API do Mastercard:
   │  POST https://pagamentos.interactive.co.mz/api/pay/mastercard
   │  { input_amount, input_token }
   └─ Retorna: { session_id, checkout_config }
   ↓
[React] window.Checkout.configure() + showLightbox()
   ├─ Mostra popup do Mastercard
   └─ Usuário preenche dados do cartão
   ↓
[Mastercard Gateway] Processa pagamento
   ├─ Valida cartão
   ├─ Processa transação
   └─ Retorna resultIndicator
   ↓
[Backend] Recebe callback do gateway
   └─ Redireciona para: /mastercard?resultIndicator=SUCCESS
   ↓
[React] Rota /mastercard (MastercardCallback.tsx)
   ├─ Lê parâmetro resultIndicator da URL
   ├─ Processa resultado:
   │  ├─ SUCCESS → Mostra sucesso, redireciona para home
   │  ├─ PENDING → Aguardando confirmação
   │  └─ FAILED/CANCELLED → Mostra erro, permite retry
   └─ Exibe status detalhado ao usuário
```

---

## 2️⃣ Componentes React

### MastercardForm.tsx
Formulário simplificado que exibe instruções:
```tsx
import { MastercardForm } from "@/components/checkout/MastercardForm";

// No Index.tsx:
{paymentMethod === "mastercard" && <MastercardForm />}
```

**Exibe:**
- Ícone de segurança
- Instruções passo-a-passo
- Badge de segurança SSL 256-bit

**Não contém:**
- Campos de entrada de cartão (o popup do Mastercard cuida disso)

### MastercardCallback.tsx
Página que processa o callback do pagamento:
```tsx
import MastercardCallback from "./pages/MastercardCallback";

// URL: /mastercard?resultIndicator=SUCCESS
```

**Lida com:**
- SUCCESS: Pagamento aprovado
- PENDING: Aguardando confirmação
- FAILED/CANCELLED: Pagamento recusado

---

## 3️⃣ Rotas React

### App.tsx
```tsx
<Routes>
  <Route path="/" element={<Index />} />
  <Route path="/mastercard" element={<MastercardCallback />} />
  <Route path="*" element={<NotFound />} />
</Routes>
```

**Rota Callback:**
- URL: `/mastercard?resultIndicator={SUCCESS|PENDING|FAILED|CANCELLED|ERROR}`
- Componente: `MastercardCallback.tsx`
- Função: Processar resposta do gateway e exibir resultado

---

## 4️⃣ Serviço mastercardService.ts

### Função: initiateMastercardPayment()
```typescript
const result = await initiateMastercardPayment({
  package: {
    package_name: "Plano Premium",
    package_id: "premium-1",
    plan_code: "monthly",
    cupon_code: "DESCONTO10"
  },
  customerData: {} // Opcional: endereço, etc
});

if (result.success) {
  // { session_id, checkout_config }
  window.Checkout.configure(result.checkout_config);
  window.Checkout.showLightbox();
}
```

---

## 5️⃣ Backend Laravel - /initiate-mastercard-payment

### Requisição
```json
{
  "package": {
    "package_name": "Plano Premium",
    "package_id": 1,
    "plan_code": "monthly",
    "cupon_code": "DESCONTO10"
  },
  "customerData": {
    "billing_address": {
      "street": "Rua X",
      "country": "MZ",
      "state": "Maputo",
      "zip": "1100"
    }
  }
}
```

### Resposta (Sucesso)
```json
{
  "success": true,
  "session_id": "abc123def456",
  "checkout_config": {
    "merchant": 15413,
    "interaction": {
      "merchant": {
        "name": "Makagui Experience",
        "address": {
          "line1": "Maputo",
          "line2": "Katembe"
        }
      },
      "locale": "pt_BR",
      "displayControl": {
        "billingAddress": "HIDE",
        "customerEmail": "HIDE",
        "shipping": "HIDE"
      }
    }
  }
}
```

### Resposta (Erro)
```json
{
  "success": false,
  "message": "Plano não encontrado",
  "error_code": "PLAN_NOT_FOUND",
  "retryable": false
}
```

---

## 6️⃣ Fluxo do Backend

**Route:** `POST /initiate-mastercard-payment`
**Controller:** `BillingController@initiateMastercardPayment`

1. Validar dados da requisição
2. Buscar plano no Zoho
3. Validar cupom (se houver)
4. Chamar API do Mastercard:
   ```php
   $response = Http::post(
     'https://pagamentos.interactive.co.mz/api/pay/mastercard',
     [
       'input_amount' => $finalAmount,
       'input_token' => $token
     ]
   );
   ```
5. Armazenar dados da sessão (encriptados):
   ```php
   session(['holdedData' => encrypt([
     'successIndicator' => $result['successIndicator'],
     'sessionVersion' => $result['session']['version'],
     'resId' => $result['session']['id'],
     'package' => $package,
     'amount' => $finalAmount,
     'userId' => $user->id,
     'customerData' => $customerData,
     'coupon' => $coupon
   ])]);
   ```
6. Retornar `session_id` e `checkout_config`

---

## 7️⃣ Callback do Gateway

Após o pagamento, o Mastercard redireciona para:
```
https://seu-dominio.com/mastercard?resultIndicator=SUCCESS
```

**Parâmetros possíveis:**
- `SUCCESS`: Pagamento aprovado
- `PENDING`: Aguardando confirmação do banco
- `FAILED`: Pagamento recusado
- `CANCELLED`: Usuário cancelou
- `ERROR`: Erro no processamento

---

## 8️⃣ Página de Resultado (React)

### URL: `/mastercard?resultIndicator=SUCCESS`

**Componente:** `MastercardCallback.tsx`

**Estados:**
- **SUCCESS**: 
  - ✅ Ícone verde
  - Mensagem: "Pagamento Processado com Sucesso!"
  - Redireciona para `/` em 3 segundos

- **PENDING**:
  - ⏳ Ícone de carregamento
  - Mensagem: "Pagamento Pendente"
  - Botão "Voltar ao Checkout"

- **FAILED/CANCELLED/ERROR**:
  - ❌ Ícone vermelho
  - Mensagem: "Pagamento Não Processado"
  - Botões: "Voltar ao Checkout" + "Tentar Novamente"

---

## 9️⃣ Integração no Backend

Quando o resultado é `SUCCESS`, o backend deve:

1. Verificar `holdedData` da sessão
2. Chamar `processUserSubscription()`:
   ```php
   $this->processUserSubscription(
     userId: $sessionData['userId'],
     packageData: $sessionData['package'],
     amount: $sessionData['amount'],
     paymentMethod: 'cartao',
     resultIndicator: $resultIndicator,
     coupon: $sessionData['coupon'],
     customerData: $sessionData['customerData']
   );
   ```

3. Criar deal em Zoho CRM
4. Enviar email de confirmação
5. Ativar inscrição no sistema

---

## 🔟 Segurança

✅ **Protegido contra:**
- CSRF (token validado no backend)
- Session Hijacking (dados encriptados)
- PCI-DSS (cartão nunca armazenado)
- Man-in-the-Middle (HTTPS obrigatório)

✅ **Boas práticas:**
- Dados de sessão encriptados com Laravel `encrypt()`
- Token de API no backend (não exposto)
- Validação em ambos cliente e servidor
- Logs de auditoria de todas as transações

---

## 1️⃣1️⃣ Testes

### Teste Local (Simulado)

```bash
# No console do navegador:
fetch('/initiate-mastercard-payment', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({
    package: {
      plan_code: 'PREMIUM_MONTHLY',
      package_name: 'Premium',
      package_id: 1
    },
    customerData: {}
  })
}).then(r => r.json()).then(console.log);
```

### Teste de Callback

```bash
# Visitar URL de callback diretamente:
http://localhost:5173/mastercard?resultIndicator=SUCCESS
```

---

## 1️⃣2️⃣ Checklist de Implementação

- [x] Página Index.tsx com seletor de método
- [x] MastercardForm.tsx simplificado
- [x] Rota /mastercard no React
- [x] MastercardCallback.tsx para processar resultado
- [x] mastercardService.ts com função initiateMastercardPayment
- [x] Script Checkout.js carregado e configurado
- [x] Temas CSS atualizados (backgrounds mais claros)
- [ ] Backend: Rota POST /initiate-mastercard-payment
- [ ] Backend: Método processUserSubscription()
- [ ] Backend: Integração com Zoho CRM
- [ ] Backend: Envio de emails de confirmação
- [ ] Testes em ambiente de homologação
- [ ] Configurar URL de callback no dashboard do Mastercard

---

## 1️⃣3️⃣ Variáveis de Ambiente

### Frontend (.env)
```env
VITE_MPESA_API_TOKEN=seu_token_aqui
VITE_MPESA_API_ENDPOINT=https://pagamentos.interactive.co.mz/api/pay/mpesa
```

### Backend (.env)
```env
MASTERCARD_TOKEN=seu_token_do_mastercard
MASTERCARD_GATEWAY=https://pagamentos.interactive.co.mz/api/pay/mastercard
MASTERCARD_MERCHANT_ID=15413
```

---

## 1️⃣4️⃣ Próximos Passos

1. Implementar rota POST /initiate-mastercard-payment no Laravel
2. Implementar método processUserSubscription() no BillingController
3. Testar fluxo completo em ambiente de homologação
4. Configurar webhook de retry para pagamentos PENDING
5. Implementar reconciliação de pagamentos
6. Adicionar logs de auditoria
7. Fazer load testing
8. Migrar para produção

---

## 📞 Suporte

Qualquer dúvida sobre a implementação:
- Documentação: Veja `MASTERCARD_PAYMENT_FLOW.md`
- Código: `/src/pages/MastercardCallback.tsx`
- Serviço: `/src/services/mastercardService.ts`
