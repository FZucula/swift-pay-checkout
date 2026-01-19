import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { toast } from "sonner";

interface PurchaserInfoDialogProps {
    open: boolean;
    onSubmit: (data: { firstName: string; email: string }) => void;
}

export const PurchaserInfoDialog = ({ open, onSubmit }: PurchaserInfoDialogProps) => {
    const [firstName, setFirstName] = useState("");
    const [email, setEmail] = useState("");

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!firstName.trim() || !email.trim()) {
            toast.error("Por favor preencha todos os campos");
            return;
        }

        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            toast.error("Por favor insira um email válido");
            return;
        }

        onSubmit({ firstName, email });
    };

    return (
        <Dialog open={open} onOpenChange={() => { }}>
            <DialogContent className="sm:max-w-[425px]" onPointerDownOutside={(e) => e.preventDefault()} onEscapeKeyDown={(e) => e.preventDefault()}>
                <DialogHeader>
                    <DialogTitle>Informações do Comprador</DialogTitle>
                    <DialogDescription>
                        Precisamos de alguns detalhes seus para processar o pagamento e enviar o recibo.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4 pt-4">
                    <div className="space-y-2">
                        <Label htmlFor="firstName">Nome</Label>
                        <Input
                            id="firstName"
                            placeholder="Seu nome"
                            value={firstName}
                            onChange={(e) => setFirstName(e.target.value)}
                            required
                        />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            placeholder="seu@email.com"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                        />
                    </div>
                    <Button type="submit" className="w-full">
                        Continuar
                    </Button>
                </form>
            </DialogContent>
        </Dialog>
    );
};
