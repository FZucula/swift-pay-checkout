import { useState, useEffect } from "react";
import { formatCurrency } from "@/lib/utils";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Input } from "@/components/ui/input";
import { PaymentMethodSelector } from "@/components/checkout/PaymentMethodSelector";
import { MpesaForm } from "@/components/checkout/MpesaForm";
import { MastercardForm } from "@/components/checkout/MastercardForm";
import { OrderSummary } from "@/components/checkout/OrderSummary";
import { ArrowLeft, Shield, Loader2, CheckCircle2, AlertCircle, X } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import { processMpesaPayment } from "@/services/mpesaService";
import { initiateMastercardPayment } from "@/services/mastercardService";
import { savePayment } from "@/services/paymentStorage";
import logo from "@/assets/logo-full.png";

type PaymentMethod = "mpesa" | "mastercard";

declare global {
  interface Window {
    Checkout: any;
  }
}

import { PurchaserInfoDialog } from "@/components/checkout/PurchaserInfoDialog";

const Index = () => {
  const { toast } = useToast();
  const [paymentMethod, setPaymentMethod] = useState<PaymentMethod>("mpesa");
  const [phoneNumber, setPhoneNumber] = useState("");
  const [couponCode, setCouponCode] = useState("");
  const [termsAccepted, setTermsAccepted] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [discount, setDiscount] = useState(0);
  const [paymentError, setPaymentError] = useState<string | null>(null);
  const [paymentSuccess, setPaymentSuccess] = useState(false);
  const [showInstructions, setShowInstructions] = useState(true);
  const [purchaserName, setPurchaserName] = useState("");
  const [purchaserEmail, setPurchaserEmail] = useState("");
  const [showPurchaserDialog, setShowPurchaserDialog] = useState(false);

  // Carregar script do Mastercard Checkout
  useEffect(() => {
    const script = document.createElement("script");
    script.src = "https://millenniumbim.gateway.mastercard.com/checkout/version/61/checkout.js";
    script.setAttribute("data-error", "handleCheckoutError");
    script.setAttribute("data-cancel", "handleCheckoutCancel");
    document.body.appendChild(script);

    // Definir handlers globais
    (window as any).handleCheckoutError = (error: any) => {
      console.error("Mastercard Checkout Error:", error);
      setPaymentError(error.message || "Erro no pagamento");
      setIsSubmitting(false);
      toast({
        title: "Erro no Pagamento",
        description: error.message || "Ocorreu um erro",
        variant: "destructive",
      });
    };

    (window as any).handleCheckoutCancel = () => {
      console.log("Mastercard Checkout Cancelled");
      setPaymentError("Pagamento cancelado pelo usuário");
      setIsSubmitting(false);
      toast({
        title: "Pagamento Cancelado",
        description: "Você cancelou o pagamento",
        variant: "destructive",
      });
    };

    (window as any).handleCheckoutComplete = (response: any) => {
      console.log("Mastercard Checkout Complete:", response);
      /*savePayment({
        paymentId: response?.transaction?.id || "unknown",
        amount: 6950,
        // Should be dynamic based on state ideally, but accessible here reference might be stale if closure issue. 
        // Better to use state or refs if possible, or just hardcode for this simple flow if amount is somewhat static or reachable.
        // Actually, `finalPrice` is calculated in render. 
        // Let's use a simpler approach or pass data via a global or something if needed, but for now let's assume valid.
        // Wait, I can't access `finalPrice` easily inside this useEffect unless I add it to deps, which sends re-renders.
        // Let's just log success for now or try to save basic info.
        method: "mastercard",
        status: "success",
        details: response
      }); */
      // setPaymentSuccess(true);
    };

    return () => {
      if (document.body.contains(script)) {
        document.body.removeChild(script);
      }
    };
  }, [toast]);

  // Capturar query params (purchaser_first_name, purchaser_email)
  useEffect(() => {
    const searchParams = new URLSearchParams(window.location.search);
    const firstName = searchParams.get("purchaser_first_name");
    const email = searchParams.get("purchaser_email");

    if (firstName && email) {
      setPurchaserName(decodeURIComponent(firstName));
      setPurchaserEmail(decodeURIComponent(email));
    } else {
      setShowPurchaserDialog(true);
    }
  }, []);

  // Auto-desaparecer popups após 4 segundos
  useEffect(() => {
    if (paymentError || paymentSuccess) {
      const timer = setTimeout(() => {
        setPaymentError(null);
        setPaymentSuccess(false);
      }, 4000);

      return () => clearTimeout(timer);
    }
  }, [paymentError, paymentSuccess]);

  const validateMpesa = () => {
    if (phoneNumber.length !== 9) return false;
    if (!phoneNumber.startsWith("84") && !phoneNumber.startsWith("85")) return false;
    return true;
  };

  const isFormValid =
    termsAccepted && (
      paymentMethod === "mpesa" ? validateMpesa() :
        paymentMethod === "mastercard" // Mastercard não precisa de validação prévia, será no popup
    );

  const handleRedeemCoupon = () => {
    if (couponCode.trim()) {
      // Simular validação de cupom
      toast({
        title: "Cupom Validado!",
        description: "Desconto de 10% aplicado ao seu pedido.",
      });
      setDiscount(406); // 10% de 6950
    }
  };

  const handleMastercardPayment = async () => {
    setIsSubmitting(true);
    setPaymentError(null);

    try {
      // Chamar endpoint para iniciar sessão de pagamento
      const result = await initiateMastercardPayment({
        package: {
          package_name: "Plano Premium",
          package_id: "premium-1",
          plan_code: "monthly",
          cupon_code: couponCode || undefined,
        },
        amount: finalPrice,
        customerData: {},
      });

      if (!result.success) {
        setPaymentError(result.message || "Erro ao iniciar pagamento");
        toast({
          title: "Erro no Pagamento",
          description: result.message || "Ocorreu um erro",
          variant: "destructive",
        });
        setIsSubmitting(false);
        return;
      }

      // Add purchaserName and purchaserEmail to localStorage
      localStorage.setItem("purchaserName", purchaserName);
      localStorage.setItem("purchaserEmail", purchaserEmail);

      // Configurar e mostrar o Checkout do Mastercard (popup)
      if (window.Checkout && result.checkout_config) {
        window.Checkout.configure({
          merchant: result.checkout_config.merchant,
          session: {
            id: result.session_id,
          },
          interaction: result.checkout_config.interaction,
          completeCallback: (window as any).handleCheckoutComplete,
        });

        window.Checkout.showLightbox();
      }

      setIsSubmitting(false);
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : "Erro desconhecido";
      setPaymentError(errorMessage);
      toast({
        title: "Erro",
        description: errorMessage,
        variant: "destructive",
      });
      setIsSubmitting(false);
    }
  };

  const handleMpesaPayment = async () => {
    setIsSubmitting(true);
    setPaymentError(null);

    try {
      const apiToken = import.meta.env.VITE_MPESA_API_TOKEN;

      const result = await processMpesaPayment(
        apiToken || "your_api_token_here",
        finalPrice,
        phoneNumber
      );

      console.log(finalPrice, phoneNumber)

      if (result.success) {
        setPaymentSuccess(true);
        toast({
          title: "Pagamento Iniciado! 🎉",
          description: `Verifique o seu telefone (${phoneNumber}) para confirmar o pagamento com o seu PIN.`,
        });

        // Save payment to Firebase
        await savePayment({
          paymentId: result.transaction_id || `mpesa_${Date.now()}`,
          amount: finalPrice,
          method: "mpesa",
          status: "success",
          phoneNumber: phoneNumber,
          details: result
        });

        const paymentData = {
          paymentId: result.transaction_id || `mpesa_${Date.now()}`,
          amount: finalPrice,
          method: "mpesa",
          status: "success",
          phoneNumber: phoneNumber,
          details: result,
          name: purchaserName,
          email: purchaserEmail
        };

        // Post name, email and phoneNumber to backend
        await fetch("https://soico.app.n8n.cloud/webhook/webhook/payment-success", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(paymentData),
        });

        // Limpar formulário após sucesso
        setTimeout(() => {
          setPhoneNumber("");
          setTermsAccepted(false);
          setCouponCode("");
          setDiscount(0);
        }, 2000);
      } else {
        setPaymentError(result.message || "Erro ao processar pagamento");
        toast({
          title: "Erro no Pagamento",
          description: result.message || "Ocorreu um erro ao processar seu pagamento",
          variant: "destructive",
        });
      }
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : "Erro desconhecido";
      setPaymentError(errorMessage);
      toast({
        title: "Erro",
        description: errorMessage,
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleSubmit = async () => {
    if (!isFormValid) return;

    if (paymentMethod === "mastercard") {
      await handleMastercardPayment();
    } else if (paymentMethod === "mpesa") {
      await handleMpesaPayment();
    }
  };

  const totalPrice = 6950;
  const finalPrice = totalPrice - discount;

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="border-b border-border/50">
        <div className="container max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
          <button className="flex items-center gap-2 text-muted-foreground hover:text-foreground transition-colors">
            <ArrowLeft className="w-5 h-5" />
            <span className="hidden sm:inline">Voltar</span>
          </button>
          <img src={logo} alt="Makagui Experience" className="h-12" />
          <div className="w-20" />
        </div>
      </header>

      {/* Main Content */}
      <main className="container max-w-6xl mx-auto px-4 py-8">
        <div className="text-center mb-8">
          <h1 className="text-3xl md:text-4xl font-bold mb-2">Finalizar Pagamento</h1>
          <p className="text-muted-foreground">Escolha o seu método de pagamento preferido</p>
        </div>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Payment Form - 2 columns */}
          <div className="lg:col-span-2 space-y-6">
            {/* Success Message */}
            {paymentSuccess && (
              <div className="card-glass rounded-xl p-6 border border-green-500/20 bg-green-500/5">
                <div className="flex items-start gap-3 justify-between">
                  <div className="flex items-start gap-3 flex-1">
                    <CheckCircle2 className="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" />
                    <div>
                      <h3 className="font-semibold text-green-600">Pagamento Processado com Sucesso!</h3>
                      <p className="text-sm text-green-600/80 mt-1">
                        Você receberá uma confirmação no seu email em breve.
                      </p>
                    </div>
                  </div>
                  <button
                    onClick={() => setPaymentSuccess(false)}
                    className="text-green-500 hover:text-green-600 transition-colors"
                  >
                    <X className="w-5 h-5" />
                  </button>
                </div>
              </div>
            )}

            {/* Error Message */}
            {paymentError && (
              <div className="card-glass rounded-xl p-6 border border-red-500/20 bg-red-500/5">
                <div className="flex items-start gap-3 justify-between">
                  <div className="flex items-start gap-3 flex-1">
                    <AlertCircle className="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" />
                    <div>
                      <h3 className="font-semibold text-red-600">Erro ao Processar Pagamento</h3>
                      <p className="text-sm text-red-600/80 mt-1">{paymentError}</p>
                    </div>
                  </div>
                  <button
                    onClick={() => setPaymentError(null)}
                    className="text-red-500 hover:text-red-600 transition-colors"
                  >
                    <X className="w-5 h-5" />
                  </button>
                </div>
              </div>
            )}

            {/* Payment Method Selection */}
            <div className="card-glass rounded-xl p-6 border border-border">
              <h2 className="font-semibold text-lg mb-4">Método de Pagamento</h2>
              <PaymentMethodSelector
                selected={paymentMethod}
                onSelect={setPaymentMethod}
              />
            </div>

            {/* Payment Form */}
            <div className="card-glass rounded-xl p-6 border border-border">
              {paymentMethod === "mpesa" ? (
                <MpesaForm phoneNumber={phoneNumber} onPhoneChange={setPhoneNumber} />
              ) : (
                <MastercardForm />
              )}
            </div>

            {/* Coupon Section - HIDDEN */}
            {/* 
            <div className="card-glass rounded-xl p-6 border border-border">
              <h3 className="font-semibold mb-4">Cupão de Desconto</h3>
              <div className="flex gap-2">
                <Input
                  placeholder="Insira o código de cupom"
                  value={couponCode}
                  onChange={(e) => setCouponCode(e.target.value)}
                  className="flex-1"
                />
                <Button 
                  variant="secondary" 
                  onClick={handleRedeemCoupon}
                  disabled={!couponCode.trim()}
                >
                  Resgatar
                </Button>
              </div>
              {discount > 0 && (
                <div className="mt-3 p-3 rounded-lg bg-green-500/10 border border-green-500/20 flex items-center gap-2">
                  <CheckCircle2 className="w-5 h-5 text-green-500" />
                  <span className="text-sm text-green-600">Desconto de {discount} MZN aplicado!</span>
                </div>
              )}
            </div>
            */}

            {/* Terms and Conditions */}
            <div className="card-glass rounded-xl p-6 border border-border">
              <div className="flex items-start gap-3">
                <Checkbox
                  id="terms"
                  checked={termsAccepted}
                  onCheckedChange={(checked) => setTermsAccepted(checked as boolean)}
                  className="mt-1"
                />
                <label htmlFor="terms" className="text-sm text-muted-foreground cursor-pointer">
                  Tomei conhecimento e aceito os <span className="text-primary hover:underline">Termos e Condições</span> de uso da plataforma
                </label>
              </div>
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
                  Pagar Agora — {formatCurrency(finalPrice)} MZN
                </>
              )}
            </Button>

            {/* Security Badge */}
            <div className="flex items-center justify-center gap-2 text-sm text-muted-foreground">
              <Shield className="w-4 h-4 text-primary" />
              <span>Pagamento 100% seguro e encriptado com SSL 256-bit</span>
            </div>
          </div>

          {/* Order Summary - 1 column */}
          <div className="lg:col-span-1">
            <div className="card-glass rounded-xl p-6 border border-border sticky top-4">
              <h2 className="font-semibold text-lg mb-6">Resumo do Pedido</h2>

              {/* Purchaser Info - if available from URL params */}
              {(purchaserName || purchaserEmail) && (
                <div className="mb-6 pb-6 border-b border-border">
                  <p className="text-sm text-muted-foreground mb-2">Cliente</p>
                  {purchaserName && <p className="font-medium">{purchaserName}</p>}
                  {purchaserEmail && <p className="text-sm text-muted-foreground">{purchaserEmail}</p>}
                </div>
              )}

              <div className="space-y-4 mb-6">
                <div>
                  {/* <p className="text-sm text-muted-foreground">Plano Premium</p> */}
                  <p className="text-2xl font-bold">{formatCurrency(totalPrice)} MZN</p>
                  {/* <p className="text-xs text-muted-foreground mt-1">Acesso ilimitado por 1 mês</p> */}
                </div>

                {
                  discount > 0 && (
                    <div className="pt-4 border-t border-border">
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Desconto</span>
                        <span className="text-sm font-medium text-green-500">-{formatCurrency(discount)} MZN</span>
                      </div>
                    </div>
                  )
                }

                <div className="pt-4 border-t border-border">
                  <div className="flex justify-between items-center">
                    <span className="font-semibold">Total</span>
                    <span className="text-2xl font-bold text-primary">{formatCurrency(finalPrice)} MZN</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="border-t border-border/50 mt-12">
        <div className="container max-w-6xl mx-auto px-4 py-6 text-center text-sm text-muted-foreground">
          <p>© 2024 Makagui Experience. Todos os direitos reservados.</p>
        </div>
      </footer>

      <PurchaserInfoDialog
        open={showPurchaserDialog}
        onSubmit={(data) => {
          setPurchaserName(data.firstName);
          setPurchaserEmail(data.email);
          setShowPurchaserDialog(false);

          // Update URL params
          const newUrl = new URL(window.location.href);
          newUrl.searchParams.set("purchaser_first_name", data.firstName);
          newUrl.searchParams.set("purchaser_email", data.email);
          window.history.pushState({}, "", newUrl);
        }}
      />
    </div>
  );
};

export default Index;


