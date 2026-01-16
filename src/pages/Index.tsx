import { useState } from "react";
import { Button } from "@/components/ui/button";
import { PaymentMethodSelector } from "@/components/checkout/PaymentMethodSelector";
import { MpesaForm } from "@/components/checkout/MpesaForm";
import { MastercardForm } from "@/components/checkout/MastercardForm";
import { OrderSummary } from "@/components/checkout/OrderSummary";
import { ArrowLeft, Shield, Loader2 } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import logo from "@/assets/logo-full.png";

type PaymentMethod = "mpesa" | "mastercard";

const Index = () => {
  const { toast } = useToast();
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>("mpesa");
  const [phoneNumber, setPhoneNumber] = useState("");
  const [cardData, setCardData] = useState({
    number: "",
    name: "",
    expiry: "",
    cvv: "",
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleCardChange = (field: string, value: string) => {
    setCardData((prev) => ({ ...prev, [field]: value }));
  };

  const validateMpesa = () => {
    if (phoneNumber.length !== 9) return false;
    if (!phoneNumber.startsWith("84") && !phoneNumber.startsWith("85")) return false;
    return true;
  };

  const validateMastercard = () => {
    return (
      cardData.number.replace(/\s/g, "").length === 16 &&
      cardData.name.length > 0 &&
      cardData.expiry.length === 5 &&
      cardData.cvv.length === 3
    );
  };

  const isFormValid = paymentMethod === "mpesa" ? validateMpesa() : validateMastercard();

  const handleSubmit = async () => {
    if (!isFormValid) return;

    setIsSubmitting(true);
    
    // Simular processamento
    await new Promise((resolve) => setTimeout(resolve, 2000));
    
    toast({
      title: "Pagamento Iniciado! 🎉",
      description: paymentMethod === "mpesa" 
        ? "Verifique o seu telemóvel para confirmar o pagamento."
        : "O seu pagamento está a ser processado.",
    });

    setIsSubmitting(false);
  };

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="border-b border-border/50">
        <div className="container max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
          <button className="flex items-center gap-2 text-muted-foreground hover:text-foreground transition-colors">
            <ArrowLeft className="w-5 h-5" />
            <span className="hidden sm:inline">Voltar</span>
          </button>
          <img src={logo} alt="Makagui Experience" className="h-12" />
          <div className="w-20" />
        </div>
      </header>

      {/* Main Content */}
      <main className="container max-w-4xl mx-auto px-4 py-8">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold mb-2">Finalizar Pagamento</h1>
          <p className="text-muted-foreground">Escolha o seu método de pagamento preferido</p>
        </div>

        <div className="grid lg:grid-cols-5 gap-8">
          {/* Payment Form - 3 columns */}
          <div className="lg:col-span-3 space-y-6">
            {/* Payment Method Selection */}
            <div className="card-glass rounded-xl p-6">
              <h2 className="font-semibold text-lg mb-4">Método de Pagamento</h2>
              <PaymentMethodSelector
                selected={paymentMethod}
                onSelect={setPaymentMethod}
              />
            </div>

            {/* Payment Form */}
            <div className="card-glass rounded-xl p-6">
              {paymentMethod === "mpesa" ? (
                <MpesaForm phoneNumber={phoneNumber} onPhoneChange={setPhoneNumber} />
              ) : (
                <MastercardForm cardData={cardData} onChange={handleCardChange} />
              )}
            </div>

            {/* Submit Button */}
            <Button
              variant="gold"
              size="lg"
              className="w-full"
              disabled={!isFormValid || isSubmitting}
              onClick={handleSubmit}
            >
              {isSubmitting ? (
                <>
                  <Loader2 className="w-5 h-5 animate-spin" />
                  A processar...
                </>
              ) : (
                <>
                  <Shield className="w-5 h-5" />
                  Pagar Agora — 4.060,00 MZN
                </>
              )}
            </Button>

            {/* Security Badge */}
            <div className="flex items-center justify-center gap-2 text-sm text-muted-foreground">
              <Shield className="w-4 h-4 text-primary" />
              <span>Pagamento 100% seguro e encriptado</span>
            </div>
          </div>

          {/* Order Summary - 2 columns */}
          <div className="lg:col-span-2">
            <OrderSummary />
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="border-t border-border/50 mt-12">
        <div className="container max-w-4xl mx-auto px-4 py-6 text-center text-sm text-muted-foreground">
          <p>© 2024 Makagui Experience. Todos os direitos reservados.</p>
        </div>
      </footer>
    </div>
  );
};

export default Index;
