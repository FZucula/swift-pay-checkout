/**
 * IMPLEMENTAÇÃO DE PAGAMENTO MASTERCARD
 * 
 * VISÃO GERAL
 * ===========
 * A implementação do pagamento Mastercard foi realizada com integração ao
 * gateway de pagamento Mastercard Checkout via Interactive (Millennium BIM).
 * 
 * ARQUIVOS CRIADOS/MODIFICADOS
 * ============================
 * 
 * 1. src/services/mastercardService.ts (NOVO)
 *    - Função: initiateMastercardPayment()
 *      Inicia a sessão de pagamento no gateway
 *    - Função: validateCardData()
 *      Valida dados do cartão (algoritmo de Luhn)
 *    - Função: detectCardType()
 *      Detecta se é Mastercard, Visa ou desconhecido
 *    - Funções auxiliares para máscaras e formatação
 * 
 * 2. src/components/checkout/MastercardForm.tsx (ATUALIZADO)
 *    - Validação em tempo real
 *    - Feedback visual (check/erro)
 *    - Detecção de tipo de cartão
 *    - Formatação automática de dados
 *    - Mensagens de ajuda
 * 
 * 3. src/pages/Index.tsx (ATUALIZADO)
 *    - Carregamento do script Checkout.js
 *    - Função handleMastercardPayment()
 *    - Chamada ao initiateMastercardPayment()
 *    - Configuração e exibição do Checkout lightbox
 *    - Handlers para erro e cancelamento
 * 
 * FLUXO DE PAGAMENTO MASTERCARD
 * =============================
 * 
 * 1. Usuário preenche dados do cartão
 *    ├─ Número (16 dígitos)
 *    ├─ Nome (conforme aparece no cartão)
 *    ├─ Expiração (MM/AA)
 *    └─ CVV (3 dígitos)
 * 
 * 2. Validação local
 *    ├─ Algoritmo de Luhn para número
 *    ├─ Verificação de expiração
 *    └─ Formato de CVV
 * 
 * 3. Clique no botão "Pagar Agora"
 *    └─ Chamada a handleMastercardPayment()
 * 
 * 4. Chamada ao endpoint /initiate-mastercard-payment (Backend Laravel)
 *    ├─ Valida dados do pacote
 *    ├─ Aplica cupom se houver
 *    ├─ Chama API do Mastercard:
 *    │  POST https://pagamentos.interactive.co.mz/api/pay/mastercard
 *    │  {
 *    │    input_amount: number,
 *    │    input_token: string
 *    │  }
 *    └─ Retorna session_id e checkout_config
 * 
 * 5. Frontend recebe resposta
 *    ├─ Extrai session_id
 *    ├─ Configura Checkout.js com:
 *    │  ├─ merchant: 15413
 *    │  ├─ session.id
 *    │  └─ interaction config
 *    └─ Mostra Checkout lightbox
 * 
 * 6. Usuário confirma no lightbox do Mastercard
 *    ├─ Mastercard processa pagamento
 *    └─ Retorna resultIndicator
 * 
 * 7. Callback do banco
 *    ├─ Parâmetro: ?resultIndicator=SUCCESS
 *    ├─ Backend valida indicador
 *    ├─ Cria registro de pagamento
 *    └─ Processa inscrição
 * 
 * VALIDAÇÃO DO CARTÃO
 * ===================
 * 
 * Algoritmo de Luhn:
 *   1. Inverter dígitos
 *   2. Multiplicar dígitos pares por 2
 *   3. Se resultado > 9, subtrair 9
 *   4. Somar todos os dígitos
 *   5. Se soma % 10 == 0, é válido
 * 
 * Expiração:
 *   - Formato: MM/AA
 *   - Mês: 01-12
 *   - Não pode estar expirado (comparado com data atual)
 * 
 * Tipo de Cartão:
 *   - Mastercard: BIN 51-55 ou 2221-2720
 *   - Visa: BIN começa com 4
 *   - Outros: desconhecido
 * 
 * CONFIGURAÇÃO NECESSÁRIA
 * =======================
 * 
 * Backend (Laravel):
 * 1. Rota: POST /initiate-mastercard-payment
 * 2. Token do Mastercard em config('services.mastercard.token')
 * 3. Endpoint: https://pagamentos.interactive.co.mz/api/pay/mastercard
 * 
 * Frontend (.env):
 * 1. Arquivo .env.example já contém VITE_MPESA_API_TOKEN
 * 2. Para Mastercard, o token fica no backend
 * 
 * TRATAMENTO DE ERROS
 * ===================
 * 
 * Erro: Número inválido
 *   → Mostra mensagem abaixo do campo
 *   → Desabilita botão Pagar
 * 
 * Erro: Cartão expirado
 *   → Validação automática
 *   → Mensagem clara: "Cartão expirado"
 * 
 * Erro: Gateway/Network
 *   → Toast com mensagem
 *   → Opção de retry
 * 
 * SEGURANÇA
 * =========
 * 
 * ✓ Dados do cartão NUNCA são armazenados no frontend
 * ✓ Validação de Luhn apenas para UX (real no gateway)
 * ✓ HTTPS obrigatório em produção
 * ✓ Tokens criptografados em sessão no backend
 * ✓ Validação no callback do resultIndicator
 * ✓ Sem armazenamento de PCI-DSS na aplicação
 * 
 * TESTES RECOMENDADOS
 * ===================
 * 
 * 1. Teste de número inválido
 *    - 4111111111111112 (não passa Luhn)
 * 
 * 2. Teste com número válido
 *    - 5555555555554444 (Mastercard válido)
 *    - 4111111111111111 (Visa válido)
 * 
 * 3. Teste de expiração
 *    - Data futura: deve passar
 *    - Data passada: deve rejeitar
 * 
 * 4. Teste de CVV
 *    - Apenas 3 dígitos
 *    - Rejeita letras
 * 
 * 5. Fluxo completo
 *    - Preencher formulário
 *    - Validar UI
 *    - Clicar pagar
 *    - Verificar chamada à API
 * 
 * LOGS E DEBUGGING
 * ================
 * 
 * Console Browser:
 *   - Logs do Checkout.js
 *   - Handlers de erro/cancelamento
 *   - Estado do formulário
 * 
 * Backend (Laravel):
 *   - Log de erro na rota initiateMastercardPayment
 *   - Resposta da API do Mastercard
 *   - Validação do callback
 * 
 * PRÓXIMOS PASSOS
 * ===============
 * 
 * 1. Testar com credenciais reais do Mastercard
 * 2. Configurar URL de callback no backend
 * 3. Validar resposta do gateway
 * 4. Implementar reconciliação de pagamentos
 * 5. Adicionar logs de auditoria
 * 6. Testar em ambiente de homologação
 */

export const MASTERCARD_CONFIG = {
  gateway: "https://pagamentos.interactive.co.mz/api/pay/mastercard",
  checkoutScript: "https://millenniumbim.gateway.mastercard.com/checkout/version/61/checkout.js",
  merchant: 15413,
  locale: "pt_BR",
};
