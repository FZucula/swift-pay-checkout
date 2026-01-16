import { useState } from "react";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { CreditCard, Lock } from "lucide-react";

interface MastercardFormProps {
  cardData: {
    number: string;
    name: string;
    expiry: string;
    cvv: string;
  };
  onChange: (field: string, value: string) => void;
}

export const MastercardForm = ({ cardData, onChange }: MastercardFormProps) => {
  const formatCardNumber = (value: string) => {
    const v = value.replace(/\s+/g, "").replace(/[^0-9]/gi, "");
    const matches = v.match(/\d{4,16}/g);
    const match = (matches && matches[0]) || "";
    const parts = [];
    for (let i = 0, len = match.length; i < len; i += 4) {
      parts.push(match.substring(i, i + 4));
    }
    return parts.length ? parts.join(" ") : v;
  };

  const formatExpiry = (value: string) => {
    const v = value.replace(/\s+/g, "").replace(/[^0-9]/gi, "");
    if (v.length >= 2) {
      return `${v.slice(0, 2)}/${v.slice(2, 4)}`;
    }
    return v;
  };

  return (
    <div className="space-y-4 animate-fadeIn">
      <div className="flex items-center gap-3 mb-4">
        <div className="w-10 h-10 rounded-full gradient-gold flex items-center justify-center">
          <CreditCard className="w-5 h-5 text-primary-foreground" />
        </div>
        <div>
          <h3 className="font-semibold text-lg">Cartão Mastercard</h3>
          <p className="text-sm text-muted-foreground">Introduza os dados do seu cartão</p>
        </div>
      </div>

      <div className="space-y-4">
        <div className="space-y-2">
          <Label htmlFor="cardNumber" className="text-foreground font-medium">
            Número do Cartão
          </Label>
          <Input
            id="cardNumber"
            type="text"
            placeholder="1234 5678 9012 3456"
            value={cardData.number}
            onChange={(e) => onChange("number", formatCardNumber(e.target.value))}
            maxLength={19}
            variant="gold"
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="cardName" className="text-foreground font-medium">
            Nome no Cartão
          </Label>
          <Input
            id="cardName"
            type="text"
            placeholder="NOME COMPLETO"
            value={cardData.name}
            onChange={(e) => onChange("name", e.target.value.toUpperCase())}
            variant="gold"
          />
        </div>

        <div className="grid grid-cols-2 gap-4">
          <div className="space-y-2">
            <Label htmlFor="expiry" className="text-foreground font-medium">
              Validade
            </Label>
            <Input
              id="expiry"
              type="text"
              placeholder="MM/AA"
              value={cardData.expiry}
              onChange={(e) => onChange("expiry", formatExpiry(e.target.value))}
              maxLength={5}
              variant="gold"
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="cvv" className="text-foreground font-medium">
              CVV
            </Label>
            <Input
              id="cvv"
              type="password"
              placeholder="•••"
              value={cardData.cvv}
              onChange={(e) => onChange("cvv", e.target.value.replace(/\D/g, "").slice(0, 3))}
              maxLength={3}
              variant="gold"
            />
          </div>
        </div>
      </div>

      <div className="bg-muted/50 rounded-lg p-4 mt-4 flex items-center gap-3">
        <Lock className="w-5 h-5 text-primary" />
        <p className="text-sm text-muted-foreground">
          Os seus dados estão protegidos com encriptação SSL de 256 bits.
        </p>
      </div>
    </div>
  );
};
