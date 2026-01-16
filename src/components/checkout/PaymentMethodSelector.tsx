import { cn } from "@/lib/utils";
import { Smartphone, CreditCard } from "lucide-react";

type PaymentMethod = "mpesa" | "mastercard";

interface PaymentMethodSelectorProps {
  selected: PaymentMethod;
  onSelect: (method: PaymentMethod) => void;
}

export const PaymentMethodSelector = ({ selected, onSelect }: PaymentMethodSelectorProps) => {
  return (
    <div className="grid grid-cols-2 gap-4">
      <button
        type="button"
        onClick={() => onSelect("mpesa")}
        className={cn(
          "card-glass rounded-xl p-5 flex flex-col items-center gap-3 transition-all duration-300",
          selected === "mpesa" 
            ? "border-primary glow-gold" 
            : "border-border hover:border-primary/50"
        )}
      >
        <div className={cn(
          "w-14 h-14 rounded-full flex items-center justify-center transition-all duration-300",
          selected === "mpesa" ? "gradient-gold" : "bg-muted"
        )}>
          <Smartphone className={cn(
            "w-7 h-7",
            selected === "mpesa" ? "text-primary-foreground" : "text-muted-foreground"
          )} />
        </div>
        <span className={cn(
          "font-semibold text-lg transition-colors",
          selected === "mpesa" ? "gradient-gold-text" : "text-foreground"
        )}>
          M-Pesa
        </span>
      </button>

      <button
        type="button"
        onClick={() => onSelect("mastercard")}
        className={cn(
          "card-glass rounded-xl p-5 flex flex-col items-center gap-3 transition-all duration-300",
          selected === "mastercard" 
            ? "border-primary glow-gold" 
            : "border-border hover:border-primary/50"
        )}
      >
        <div className={cn(
          "w-14 h-14 rounded-full flex items-center justify-center transition-all duration-300",
          selected === "mastercard" ? "gradient-gold" : "bg-muted"
        )}>
          <CreditCard className={cn(
            "w-7 h-7",
            selected === "mastercard" ? "text-primary-foreground" : "text-muted-foreground"
          )} />
        </div>
        <span className={cn(
          "font-semibold text-lg transition-colors",
          selected === "mastercard" ? "gradient-gold-text" : "text-foreground"
        )}>
          Mastercard
        </span>
      </button>
    </div>
  );
};
