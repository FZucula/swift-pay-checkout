import { useState } from "react";
import { sendPasswordResetEmail } from "firebase/auth";
import { auth } from "@/config/firebase";
import { Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { toast } from "sonner";

const ForgotPassword = () => {
    const [email, setEmail] = useState("");
    const [loading, setLoading] = useState(false);
    const [submitted, setSubmitted] = useState(false);

    const handleReset = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        try {
            await sendPasswordResetEmail(auth, email);
            setSubmitted(true);
            toast.success("Email de recuperação enviado!");
        } catch (error: any) {
            console.error("Reset password error:", error);
            toast.error("Erro ao enviar email: " + error.message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex items-center justify-center min-h-screen bg-gray-100 p-4">
            <Card className="w-full max-w-md">
                <CardHeader>
                    <CardTitle>Recuperar Senha</CardTitle>
                    <CardDescription>
                        {submitted
                            ? "Verifique o seu email para instruções de recuperação."
                            : "Digite seu email para receber um link de redefinição de senha."}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {!submitted ? (
                        <form onSubmit={handleReset} className="space-y-4">
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
                            <Button type="submit" className="w-full" disabled={loading}>
                                {loading ? "Enviando..." : "Enviar Link de Recuperação"}
                            </Button>
                        </form>
                    ) : (
                        <div className="text-center space-y-4">
                            <div className="p-4 bg-green-50 text-green-700 rounded-md">
                                Email enviado com sucesso para <strong>{email}</strong>
                            </div>
                            <Button asChild className="w-full" variant="outline">
                                <Link to="/login">Voltar para Login</Link>
                            </Button>
                        </div>
                    )}

                    {!submitted && (
                        <div className="mt-4 text-center text-sm">
                            Lembrou da senha?{" "}
                            <Link to="/login" className="text-primary hover:underline">
                                Voltar para Login
                            </Link>
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
};

export default ForgotPassword;
