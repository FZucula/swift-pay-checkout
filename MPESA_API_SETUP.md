/**
 * CONFIGURAÇÃO DO TOKEN DA API M-PESA
 * 
 * Para usar a integração M-Pesa real, você precisa:
 * 
 * 1. OBTER O TOKEN DE API
 *    - Contacte o Interactive (pagamentos.interactive.co.mz)
 *    - Obtenha seu token de autenticação
 * 
 * 2. ADICIONAR O TOKEN
 *    Opção A - Arquivo .env (Recomendado):
 *    VITE_MPESA_API_TOKEN=seu_token_aqui
 * 
 *    Opção B - Variável de Ambiente:
 *    export VITE_MPESA_API_TOKEN=seu_token_aqui
 * 
 * 3. ATUALIZAR O CÓDIGO
 *    No arquivo src/pages/Index.tsx, linha ~75:
 *    
 *    const result = await processMpesaPayment(
 *      import.meta.env.VITE_MPESA_API_TOKEN || "your_api_token_here",
 *      finalPrice,
 *      phoneNumber
 *    );
 * 
 * 4. ENDPOINT DA API
 *    URL: https://pagamentos.interactive.co.mz/api/pay/mpesa
 *    Método: POST
 *    
 *    Corpo da Requisição:
 *    {
 *      "input_token": "seu_token_aqui",
 *      "input_amount": 6950,
 *      "input_number": "258843123456"
 *    }
 *    
 * 5. RESPOSTA ESPERADA
 *    Sucesso:
 *    {
 *      "success": true,
 *      "message": "Pagamento iniciado com sucesso",
 *      "transaction_id": "TRX123456789",
 *      "reference": "REF123456789"
 *    }
 *    
 *    Erro:
 *    {
 *      "success": false,
 *      "message": "Número inválido" ou outra mensagem de erro
 *    }
 */

export const MPESA_API_CONFIG = {
  endpoint: "https://pagamentos.interactive.co.mz/api/pay/mpesa",
  tokenEnvVar: "VITE_MPESA_API_TOKEN",
  description: "M-Pesa Payment API by Interactive",
};
