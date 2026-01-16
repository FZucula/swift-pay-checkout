import { AlertCircle, CreditCard } from "lucide-react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";

/**
 * Componente simplificado do Mastercard
 * O formulário é renderizado pelo script Checkout.js em um popup
 * Este componente apenas exibe informações sobre o pagamento via cartão
 */
export const MastercardForm = () => {
  return (
    <Card className="border-amber-200 bg-amber-50">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <CreditCard className="w-5 h-5 text-amber-600" />
          Pagamento com Cartão
        </CardTitle>
        <CardDescription>
          Formulário seguro do Mastercard
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="space-y-3">
          <p className="text-sm text-gray-700">
            <strong>Como funciona:</strong>
          </p>
          <ul className="text-sm text-gray-600 space-y-1 list-disc list-inside">
            <li>Clique em "Pagar Agora"</li>
            <li>Uma janela segura do Mastercard irá abrir</li>
            <li>Introduza os dados do seu cartão</li>
            <li>Confirme a transação</li>
          </ul>
        </div>

        <div className="bg-blue-50 rounded-lg p-3 border border-blue-200 flex items-start gap-2">
          <AlertCircle className="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" />
          <p className="text-xs text-blue-900">
            Formulário seguro do Mastercard. Dados protegidos com encriptação SSL 256-bit.
          </p>
        </div>
      </CardContent>
    </Card>
  );
};
