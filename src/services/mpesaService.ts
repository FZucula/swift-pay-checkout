interface MpesaPaymentRequest {
  input_token: string;
  input_amount: number;
  input_number: string; // formato: 258 + número
}

interface MpesaPaymentResponse {
  success: boolean;
  message: string;
  transaction_id?: string;
  reference?: string;
  [key: string]: any;
}

const MPESA_API_ENDPOINT = import.meta.env.VITE_MPESA_API_ENDPOINT || 
  "https://pagamentos.interactive.co.mz/api/pay/mpesa";

/**
 * Processa pagamento via M-Pesa
 * @param token - Token de autenticação da API
 * @param amount - Valor em Meticais
 * @param phoneNumber - Número de telefone (sem 258)
 */
export async function processMpesaPayment(
  token: string,
  amount: number,
  phoneNumber: string
): Promise<MpesaPaymentResponse> {
  try {
    if (!token || token === "your_api_token_here") {
      return {
        success: false,
        message: "Token de API M-Pesa não configurado. Configure a variável VITE_MPESA_API_TOKEN no arquivo .env",
      };
    }

    // Validar número de telefone
    if (!validateMozambiquanPhoneNumber(phoneNumber)) {
      return {
        success: false,
        message: "Número de telefone inválido. Deve começar com 84 ou 85 e ter 9 dígitos.",
      };
    }

    // Adicionar país (258 é código de Moçambique)
    const formattedNumber = formatPhoneNumberForAPI(phoneNumber);

    const payload: MpesaPaymentRequest = {
      input_token: token,
      input_amount: amount,
      input_number: formattedNumber,
    };

    console.log("Processando pagamento M-Pesa:", { 
      amount, 
      phoneNumber: formattedNumber,
      endpoint: MPESA_API_ENDPOINT 
    });

    const response = await fetch(MPESA_API_ENDPOINT, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log("Resposta da API M-Pesa:", data);
    
    return data;
  } catch (error) {
    const errorMessage = error instanceof Error ? error.message : "Erro desconhecido";
    console.error("M-Pesa Payment Error:", errorMessage);
    return {
      success: false,
      message: `Erro ao processar pagamento M-Pesa: ${errorMessage}`,
    };
  }
}

/**
 * Valida número de telefone Moçambicano
 * Válidos: 84XXXXXXX ou 85XXXXXXX (9 dígitos totais)
 */
export function validateMozambiquanPhoneNumber(phoneNumber: string): boolean {
  const regex = /^8[4-7]\d{7}$/;
  return regex.test(phoneNumber);
}

/**
 * Formata número de telefone para envio à API
 * Adiciona o código do país (258)
 */
export function formatPhoneNumberForAPI(phoneNumber: string): string {
  return "258" + phoneNumber;
}
