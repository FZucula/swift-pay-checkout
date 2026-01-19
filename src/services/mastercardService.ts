/**
 * Mastercard Payment Service
 * Integração com o gateway Mastercard via Interactive
 */

interface MastercardCheckoutConfig {
  merchant: number;
  session: {
    id: string;
  };
  interaction: {
    merchant: {
      name: string;
      address: {
        line1: string;
        line2: string;
      };
    };
    locale: string;
    displayControl: {
      billingAddress: string;
      customerEmail: string;
      shipping: string;
    };
  };
}

interface InitiateMastercardPaymentRequest {
  package: {
    package_name: string;
    package_id: string;
    plan_code: string;
    cupon_code?: string;
  };
  amount?: number; // Valor do pagamento em Meticais
  customerData?: {
    billing_address?: {
      street?: string;
      attention?: string;
      country?: string;
      state?: string;
      zip?: string;
    };
  };
}

interface InitiateMastercardPaymentResponse {
  success: boolean;
  message?: string;
  session_id?: string;
  checkout_config?: MastercardCheckoutConfig;
  error_code?: string;
  retryable?: boolean;
}

interface PaymentCardData {
  number: string;
  name: string;
  expiry: string; // MM/YY
  cvv: string;
}

/**
 * Inicia sessão de pagamento Mastercard
 * Faz chamada direta ao gateway do Mastercard
 */
export async function initiateMastercardPayment(
  payload: InitiateMastercardPaymentRequest
): Promise<InitiateMastercardPaymentResponse> {
  try {
    // Obter token da API do Mastercard
    const apiToken = import.meta.env.VITE_MASTERCARD_API_TOKEN;

    if (!apiToken || apiToken === "your_mastercard_token_here") {
      console.warn("Token do Mastercard não configurado. Use VITE_MASTERCARD_API_TOKEN no .env");

      // Retornar configuração de teste para desenvolvimento
      return {
        success: true,
        session_id: "demo_session_" + Date.now(),
        checkout_config: {
          merchant: 15413,
          session: {
            id: "demo_session_" + Date.now(),
          },
          interaction: {
            merchant: {
              name: "STV Play",
              address: {
                line1: "Maputo",
                line2: "Katembe",
              },
            },
            locale: "pt_BR",
            displayControl: {
              billingAddress: "HIDE",
              customerEmail: "HIDE",
              shipping: "HIDE",
            },
          },
        },
      };
    }

    // Valor padrão de teste se não fornecido
    const amount = payload.amount || 100;

    // Fazer chamada direta ao gateway do Mastercard
    const response = await fetch("https://pagamentos.interactive.co.mz/api/pay/mastercard", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        input_amount: amount,
        input_token: apiToken,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      console.error("Mastercard Gateway Error:", data);
      return {
        success: false,
        message: data.message || "Erro ao processar pagamento no gateway",
        error_code: data.error?.code || "GATEWAY_ERROR",
        retryable: true,
      };
    }

    // Gateway retornou sucesso
    if (!data.session?.id) {
      return {
        success: false,
        message: "Resposta inválida do gateway",
        error_code: "INVALID_RESPONSE",
        retryable: false,
      };
    }

    // guardar na memoria do navegador 
    localStorage.setItem("successIndicator", data.successIndicator);

    return {
      success: true,
      session_id: data.session.id,
      checkout_config: {
        merchant: 15413,
        session: {
          id: data.session.id,
        },
        interaction: {
          merchant: {
            name: "STV Play",
            address: {
              line1: "Maputo",
              line2: "Katembe",
            },
          },
          locale: "pt_BR",
          displayControl: {
            billingAddress: "HIDE",
            customerEmail: "HIDE",
            shipping: "HIDE",
          },
        },
      },
    };
  } catch (error) {
    console.error("Error initiating Mastercard payment:", error);

    return {
      success: false,
      message: error instanceof Error ? error.message : "Erro ao processar pagamento",
      error_code: "NETWORK_ERROR",
      retryable: true,
    };
  }
}

/**
 * Valida dados do cartão
 */
export function validateCardData(cardData: PaymentCardData): {
  valid: boolean;
  errors: Record<string, string>;
} {
  const errors: Record<string, string> = {};

  // Validar número
  const cardNumber = cardData.number.replace(/\s/g, "");
  if (cardNumber.length !== 16) {
    errors.number = "Número do cartão deve ter 16 dígitos";
  } else if (!luhnCheck(cardNumber)) {
    errors.number = "Número do cartão é inválido";
  }

  // Validar nome
  if (!cardData.name || cardData.name.trim().length < 3) {
    errors.name = "Nome deve ter pelo menos 3 caracteres";
  }

  // Validar expiração
  if (cardData.expiry.length !== 5) {
    errors.expiry = "Expiração deve ser MM/AA";
  } else {
    const [month, year] = cardData.expiry.split("/");
    const monthNum = parseInt(month, 10);
    if (isNaN(monthNum) || monthNum < 1 || monthNum > 12) {
      errors.expiry = "Mês inválido";
    }

    // Verificar se cartão está expirado
    const currentYear = new Date().getFullYear() % 100;
    const currentMonth = new Date().getMonth() + 1;
    const yearNum = parseInt(year, 10);

    if (yearNum < currentYear || (yearNum === currentYear && monthNum < currentMonth)) {
      errors.expiry = "Cartão expirado";
    }
  }

  // Validar CVV
  if (cardData.cvv.length !== 3) {
    errors.cvv = "CVV deve ter 3 dígitos";
  } else if (!/^\d{3}$/.test(cardData.cvv)) {
    errors.cvv = "CVV deve conter apenas números";
  }

  return {
    valid: Object.keys(errors).length === 0,
    errors,
  };
}

/**
 * Algoritmo de Luhn para validar número do cartão
 */
function luhnCheck(cardNumber: string): boolean {
  let sum = 0;
  let isEven = false;

  for (let i = cardNumber.length - 1; i >= 0; i--) {
    let digit = parseInt(cardNumber[i], 10);

    if (isEven) {
      digit *= 2;
      if (digit > 9) {
        digit -= 9;
      }
    }

    sum += digit;
    isEven = !isEven;
  }

  return sum % 10 === 0;
}

/**
 * Formata número do cartão para exibição
 */
export function formatCardNumberForDisplay(cardNumber: string): string {
  const cleaned = cardNumber.replace(/\s/g, "");
  const parts = cleaned.match(/.{1,4}/g) || [];
  return parts.join(" ");
}

/**
 * Obtém primeiros 6 dígitos do cartão (BIN)
 */
export function getCardBIN(cardNumber: string): string {
  const cleaned = cardNumber.replace(/\s/g, "");
  return cleaned.slice(0, 6);
}

/**
 * Detecta tipo de cartão baseado no BIN
 */
export function detectCardType(cardNumber: string): "mastercard" | "visa" | "unknown" {
  const bin = getCardBIN(cardNumber);

  if (!bin) return "unknown";

  const firstDigit = bin[0];
  const firstTwoDigits = bin.slice(0, 2);

  // Mastercard: começa com 51-55 ou 2221-2720
  if (
    (firstTwoDigits >= "51" && firstTwoDigits <= "55") ||
    (parseInt(firstTwoDigits) >= 22 && parseInt(firstTwoDigits) <= 27)
  ) {
    return "mastercard";
  }

  // Visa: começa com 4
  if (firstDigit === "4") {
    return "visa";
  }

  return "unknown";
}

/**
 * Máscara para CVV (apenas números)
 */
export function maskCVV(value: string): string {
  return value.replace(/\D/g, "").slice(0, 3);
}

/**
 * Máscara para expiração MM/YY
 */
export function maskExpiry(value: string): string {
  const cleaned = value.replace(/\D/g, "");
  if (cleaned.length < 2) return cleaned;
  if (cleaned.length < 4) return cleaned.slice(0, 2) + "/" + cleaned.slice(2);
  return cleaned.slice(0, 2) + "/" + cleaned.slice(2, 4);
}
