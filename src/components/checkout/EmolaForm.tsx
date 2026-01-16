import { useState } from "react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Smartphone, AlertCircle, Check } from "lucide-react";

interface EmolaFormProps {
  phoneNumber: string;
  onPhoneChange: (value: string) => void;
}

export const EmolaForm = ({ phoneNumber, onPhoneChange }: EmolaFormProps) => {
  const [touched, setTouched] = useState(false);

  const validatePhone = (phone: string) => {
    if (phone.length === 0) return { valid: false, message: "" };
    if (phone.length !== 9) return { valid: false, message: "O número deve ter exactamente 9 dígitos" };
    return { valid: true, message: "Número válido" };
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value.replace(/\D/g, "").slice(0, 9);
    onPhoneChange(value);
  };

  const validation = validatePhone(phoneNumber);
  const showError = touched && !validation.valid && phoneNumber.length > 0;
  const showSuccess = validation.valid;

  return (
    <div className="space-y-4 animate-fadeIn">
      <div className="flex items-center gap-3 mb-4">
        <div className="w-10 h-10 rounded-full gradient-gold flex items-center justify-center">
          <Smartphone className="w-5 h-5 text-primary-foreground" />
        </div>
        <div>
          <h3 className="font-semibold text-lg">Pagamento via Emola</h3>
          <p className="text-sm text-muted-foreground">Introduza o seu número de telefone</p>
        </div>
      </div>

      <div className="space-y-2">
        <Label htmlFor="phone" className="text-foreground font-medium">
          Número de Telefone
        </Label>
        <div className="relative">
          <div className="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">
            +258
          </div>
          <Input
            id="phone"
            type="tel"
            placeholder="8X XXX XXXX"
            value={phoneNumber}
            onChange={handleChange}
            onBlur={() => setTouched(true)}
            variant="gold"
            className="pl-16 pr-12"
          />
          {showSuccess && (
            <div className="absolute right-4 top-1/2 -translate-y-1/2">
              <Check className="w-5 h-5 text-green-500" />
            </div>
          )}
          {showError && (
            <div className="absolute right-4 top-1/2 -translate-y-1/2">
              <AlertCircle className="w-5 h-5 text-destructive" />
            </div>
          )}
        </div>
        
        {showError && (
          <p className="text-sm text-destructive flex items-center gap-1.5">
            <AlertCircle className="w-4 h-4" />
            {validation.message}
          </p>
        )}
        {showSuccess && (
          <p className="text-sm text-green-500 flex items-center gap-1.5">
            <Check className="w-4 h-4" />
            {validation.message}
          </p>
        )}
      </div>

      <div className="mt-4 p-3 rounded-lg bg-muted/50 border border-border">
        <p className="text-sm text-muted-foreground">
          <strong>Instruções:</strong> Você receberá uma notificação no seu telemóvel. Confirme o pagamento inserindo o seu PIN.
        </p>
      </div>
    </div>
  );
};
