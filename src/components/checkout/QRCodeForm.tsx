import { AlertCircle } from "lucide-react";

interface QRCodeFormProps {
  disabled?: boolean;
}

export const QRCodeForm = ({ disabled = true }: QRCodeFormProps) => {
  return (
    <div className="space-y-4 animate-fadeIn">
      <div className="flex items-center gap-3 mb-4">
        <div className="w-10 h-10 rounded-full bg-muted flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" className="bi bi-qr-code text-muted-foreground" viewBox="0 0 16 16">
            <path d="M2 2h2v2H2z"/>
            <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z"/>
            <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z"/>
            <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z"/>
            <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z"/>
          </svg>
        </div>
        <div>
          <h3 className="font-semibold text-lg">QR Code IZI</h3>
          <p className="text-sm text-muted-foreground">Digitalize o código QR com a sua app</p>
        </div>
      </div>

      <div className="p-4 rounded-lg bg-amber-50 border border-amber-200 flex items-start gap-3">
        <AlertCircle className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
        <div>
          <p className="text-sm font-medium text-amber-900">Serviço em Breve</p>
          <p className="text-sm text-amber-800 mt-1">O pagamento via QR Code IZI estará disponível em breve. Por favor, use outro método de pagamento.</p>
        </div>
      </div>
    </div>
  );
};
