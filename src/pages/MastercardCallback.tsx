import { useState, useEffect } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { CheckCircle2, AlertCircle, Loader2, Home } from "lucide-react";
import { notifyPaymentSuccess } from "@/services/notificationService";
import logo from "@/assets/logo-full.png";

type ResultStatus = "success" | "pending" | "failed" | "cancelled" | "error" | "loading";

const MastercardCallback = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [status, setStatus] = useState<ResultStatus>("loading");
  const [message, setMessage] = useState("");
  const [details, setDetails] = useState<string>("");

  useEffect(() => {
    // Obter o resultIndicator da URL
    const resultIndicator = searchParams.get("resultIndicator");

    if (!resultIndicator) {
      setStatus("error");
      setMessage("Indicador de resultado não encontrado");
      setDetails("Não foi possível determinar o status do pagamento");
      return;
    }

    // Processar o resultado
    switch (resultIndicator.toUpperCase()) {
      case "SUCCESS": {
        setStatus("success");
        setMessage("Pagamento Processado com Sucesso!");
        setDetails("Sua inscrição foi ativada. Você receberá uma confirmação por email.");
        
        // Enviar notificação de pagamento
        const purchaserName = sessionStorage.getItem("purchaserName") || "Cliente";
        const purchaserEmail = sessionStorage.getItem("purchaserEmail") || "email@exemplo.com";
        const amount = sessionStorage.getItem("paymentAmount") || "0";
        
        notifyPaymentSuccess({
          purchaserName,
          purchaserEmail,
          amount: parseFloat(amount),
          paymentMethod: "mastercard",
          timestamp: new Date().toISOString()
        });
        
        // Redirecionar para home após 3 segundos
        setTimeout(() => {
          navigate("/");
        }, 3000);
        break;
      }

      case "PENDING":
        setStatus("pending");
        setMessage("Pagamento Pendente");
        setDetails(
          "Seu pagamento está sendo processado. Por favor, aguarde. Você receberá uma confirmação por email em breve."
        );
        break;

      case "FAILED":
      case "CANCELLED":
      case "ERROR":
        setStatus("failed");
        setMessage("Pagamento Não Processado");
        setDetails(
          resultIndicator.toUpperCase() === "CANCELLED"
            ? "Você cancelou o pagamento. Clique abaixo para tentar novamente."
            : "Seu pagamento não foi processado. Por favor, verifique seus dados e tente novamente."
        );
        break;

      default:
        setStatus("error");
        setMessage("Status Desconhecido");
        setDetails(`Resultado inesperado: ${resultIndicator}`);
    }
  }, [searchParams, navigate]);

  return (
    <div className="min-h-screen bg-gradient-to-br from-background via-background to-secondary/20">
      {/* Header */}
      <header className="border-b border-border/50 bg-card/50 backdrop-blur-sm">
        <div className="container max-w-2xl mx-auto px-4 py-4 flex items-center justify-center">
          <img src={logo} alt="Makagui Experience" className="h-12" />
        </div>
      </header>

      {/* Main Content */}
      <main className="container max-w-2xl mx-auto px-4 py-8 md:py-16">
        <div className="space-y-6">
          {/* Status Card */}
          <div className="bg-card border border-border rounded-2xl overflow-hidden shadow-lg">
            <div className="p-8 md:p-12 text-center space-y-6">
              {/* Icon */}
              <div className="flex justify-center">
                {status === "loading" && (
                  <div className="w-16 h-16 rounded-full bg-primary/20 flex items-center justify-center">
                    <Loader2 className="w-8 h-8 text-primary animate-spin" />
                  </div>
                )}
                {status === "success" && (
                  <div className="w-16 h-16 rounded-full bg-green-500/20 flex items-center justify-center">
                    <CheckCircle2 className="w-8 h-8 text-green-500" />
                  </div>
                )}
                {status === "pending" && (
                  <div className="w-16 h-16 rounded-full bg-amber-500/20 flex items-center justify-center">
                    <Loader2 className="w-8 h-8 text-amber-500 animate-spin" />
                  </div>
                )}
                {(status === "failed" || status === "error") && (
                  <div className="w-16 h-16 rounded-full bg-red-500/20 flex items-center justify-center">
                    <AlertCircle className="w-8 h-8 text-red-500" />
                  </div>
                )}
              </div>

              {/* Message */}
              <div className="space-y-3">
                <h1
                  className={`text-3xl md:text-4xl font-bold ${
                    status === "success"
                      ? "text-green-500"
                      : status === "pending"
                      ? "text-amber-500"
                      : status === "error"
                      ? "text-red-500"
                      : "text-red-500"
                  }`}
                >
                  {message}
                </h1>
                <p className="text-muted-foreground text-lg">{details}</p>
              </div>

              {/* Details */}
              <div className="bg-muted/30 rounded-lg p-4 text-sm text-muted-foreground border border-border/50">
                <p>
                  <span className="font-semibold">Status do Pagamento:</span>{" "}
                  <span className="text-foreground uppercase">
                    {searchParams.get("resultIndicator") || "DESCONHECIDO"}
                  </span>
                </p>
                <p className="mt-2 text-xs">Data/Hora: {new Date().toLocaleString("pt-MZ")}</p>
              </div>

              {/* Actions */}
              <div className="flex flex-col sm:flex-row gap-3 justify-center pt-6">
                {status === "success" && (
                  <p className="text-sm text-muted-foreground">
                    Redirecionando para a página inicial em 3 segundos...
                  </p>
                )}
                {status !== "success" && status !== "loading" && (
                  <>
                    <Button
                      variant="gold"
                      size="lg"
                      onClick={() => navigate("/")}
                      className="flex items-center gap-2"
                    >
                      <Home className="w-5 h-5" />
                      Voltar ao Checkout
                    </Button>
                    {status === "failed" && (
                      <Button
                        variant="outline"
                        size="lg"
                        onClick={() => navigate("/")}
                        className="flex items-center gap-2"
                      >
                        Tentar Novamente
                      </Button>
                    )}
                  </>
                )}
              </div>
            </div>
          </div>

          {/* Support Info */}
          <div className="bg-card/50 border border-border/50 rounded-lg p-6 text-center text-sm text-muted-foreground">
            <p>
              Tem dúvidas sobre o seu pagamento?{" "}
              <a href="mailto:suporte@stv.play" className="text-primary hover:underline font-medium">
                Entre em contacto com o suporte
              </a>
            </p>
          </div>

          {/* Security Badge */}
          <div className="text-center text-xs text-muted-foreground">
            🔒 Transação segura e encriptada com SSL 256-bit
          </div>
        </div>
      </main>
    </div>
  );
};

export default MastercardCallback;
