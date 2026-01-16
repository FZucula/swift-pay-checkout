/**
 * Serviço de Notificação de Pagamentos
 * Envia emails quando um pagamento é bem-sucedido
 */

interface PaymentNotificationData {
  purchaserName: string;
  purchaserEmail: string;
  amount: number;
  paymentMethod: "mpesa" | "mastercard";
  transactionId?: string;
  timestamp?: string;
}

/**
 * Notifica sobre pagamento bem-sucedido
 * Envia email para sheila.david@dhd.co.mz com detalhes do pagamento
 */
export async function notifyPaymentSuccess(
  data: PaymentNotificationData
): Promise<{ success: boolean; message?: string }> {
  try {
    // Chamar endpoint backend para enviar email
    const response = await fetch("/api/notify-payment", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "",
      },
      body: JSON.stringify({
        recipient: "sheila.david@dhd.co.mz",
        purchaserName: data.purchaserName,
        purchaserEmail: data.purchaserEmail,
        amount: data.amount,
        paymentMethod: data.paymentMethod,
        transactionId: data.transactionId || `TXN_${Date.now()}`,
        timestamp: data.timestamp || new Date().toISOString(),
      }),
    });

    if (!response.ok) {
      console.warn("Email notification failed:", response.statusText);
      // Não falha o pagamento se o email falhar
      return {
        success: true,
        message: "Pagamento processado, mas notificação de email falhou",
      };
    }

    return {
      success: true,
      message: "Email enviado com sucesso",
    };
  } catch (error) {
    console.error("Error sending payment notification:", error);
    // Não falha o pagamento se o serviço de email tiver problemas
    return {
      success: true,
      message: "Pagamento processado, serviço de email indisponível",
    };
  }
}
