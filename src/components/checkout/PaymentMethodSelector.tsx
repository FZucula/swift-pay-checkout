import { cn } from "@/lib/utils";
import { Smartphone, CreditCard } from "lucide-react";

type PaymentMethod = "mpesa" | "mastercard";

interface PaymentMethodSelectorProps {
  selected: PaymentMethod;
  onSelect: (method: PaymentMethod) => void;
}

const PaymentMethodButton = ({ 
  method, 
  selected, 
  onSelect, 
  icon: Icon,
  label,
  disabled = false 
}: {
  method: PaymentMethod;
  selected: PaymentMethod;
  onSelect: (method: PaymentMethod) => void;
  icon: React.ReactNode;
  label: string;
  disabled?: boolean;
}) => (
  <button
    type="button"
    onClick={() => !disabled && onSelect(method)}
    disabled={disabled}
    className={cn(
      "card-glass rounded-xl p-5 flex flex-col items-center gap-3 transition-all duration-300",
      disabled ? "opacity-50 cursor-not-allowed" : "",
      selected === method 
        ? "border-primary glow-gold" 
        : "border-border hover:border-primary/50"
    )}
  >
    <div className={cn(
      "w-14 h-14 rounded-full flex items-center justify-center transition-all duration-300",
      selected === method ? "gradient-gold" : "bg-muted"
    )}>
      {Icon}
    </div>
    <span className={cn(
      "font-semibold text-lg transition-colors text-center",
      selected === method ? "gradient-gold-text" : "text-foreground"
    )}>
      {label}
    </span>
  </button>
);

export const PaymentMethodSelector = ({ selected, onSelect }: PaymentMethodSelectorProps) => {
  return (
    <div className="grid grid-cols-2 gap-4">
      <PaymentMethodButton
        method="mpesa"
        selected={selected}
        onSelect={onSelect}
        icon={<Smartphone className="w-7 h-7 text-primary-foreground" />}
        label="M-Pesa"
      />

      <PaymentMethodButton
        method="mastercard"
        selected={selected}
        onSelect={onSelect}
        icon={<CreditCard className="w-7 h-7 text-primary-foreground" />}
        label="Cartão"
      />
    </div>
  );
};
