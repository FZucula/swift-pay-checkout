import { Separator } from "@/components/ui/separator";

interface OrderSummaryProps {
  items?: { name: string; price: number }[];
  subtotal?: number;
  tax?: number;
  total?: number;
}

export const OrderSummary = ({ 
  items = [
    { name: "Makagui Experience Premium", price: 2500 },
    { name: "Acesso VIP", price: 1000 },
  ],
  subtotal = 3500,
  tax = 560,
  total = 4060 
}: OrderSummaryProps) => {
  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("pt-MZ", {
      style: "decimal",
      minimumFractionDigits: 2,
    }).format(value) + " MZN";
  };

  return (
    <div className="card-glass rounded-xl p-6">
      <h3 className="font-semibold text-lg mb-4 gradient-gold-text">Resumo do Pedido</h3>
      
      <div className="space-y-3">
        {items.map((item, index) => (
          <div key={index} className="flex justify-between text-sm">
            <span className="text-muted-foreground">{item.name}</span>
            <span className="text-foreground font-medium">{formatCurrency(item.price)}</span>
          </div>
        ))}
      </div>

      <Separator className="my-4 bg-border" />

      <div className="space-y-2">
        <div className="flex justify-between text-sm">
          <span className="text-muted-foreground">Subtotal</span>
          <span className="text-foreground">{formatCurrency(subtotal)}</span>
        </div>
        <div className="flex justify-between text-sm">
          <span className="text-muted-foreground">IVA (16%)</span>
          <span className="text-foreground">{formatCurrency(tax)}</span>
        </div>
      </div>

      <Separator className="my-4 bg-border" />

      <div className="flex justify-between items-center">
        <span className="font-semibold text-lg">Total</span>
        <span className="text-2xl font-bold gradient-gold-text">{formatCurrency(total)}</span>
      </div>
    </div>
  );
};
