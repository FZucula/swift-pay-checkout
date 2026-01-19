import { useState } from "react";
import { signInWithEmailAndPassword } from "firebase/auth";
import { auth } from "@/config/firebase";
import { useNavigate, Link } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { toast } from "sonner";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
import { CheckCircle2, AlertCircle } from "lucide-react";

const Login = () => {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [loading, setLoading] = useState(false);
    const [connectionStatus, setConnectionStatus] = useState<"idle" | "success" | "error">("idle");
    const navigate = useNavigate();

    const handleLogin = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);

        try {
            await signInWithEmailAndPassword(auth, email, password);
            toast.success("Login efetuado com sucesso!");
            navigate("/admin");
        } catch (error: any) {
            console.error("Login error:", error);
            toast.error("Erro no login: " + error.message);
        } finally {
            setLoading(false);
        }
    };

    const checkConnection = async () => {
        try {
            // Just waiting for auth to be ready is usually enough to verify config 
            // but we can force a token refresh or similar if logged in. 
            // Since we are likely logged out, just accessing the auth object is a start.
            // A better check is to try fetching something from firestore or just verify app init.

            const appName = auth.app.name;
            if (appName) {
                setConnectionStatus("success");
                toast.success("Firebase conectado corretamente!");
            } else {
                throw new Error("App name not found");
            }
        } catch (error) {
            console.error("Connection check failed:", error);
            setConnectionStatus("error");
            toast.error("Erro na conexão com Firebase");
        }
    };

    return (
        <div className="flex items-center justify-center min-h-screen bg-gray-100 p-4">
            <Card className="w-full max-w-md">
                <CardHeader>
                    <CardTitle>Login Admin</CardTitle>
                    <CardDescription>Entre com suas credenciais para acessar o painel.</CardDescription>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleLogin} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                placeholder="admin@exemplo.com"
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                required
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                required
                            />
                        </div>
                        <Button type="submit" className="w-full" disabled={loading}>
                            {loading ? "Entrando..." : "Entrar"}
                        </Button>
                    </form>

                    <div className="mt-4 flex flex-col space-y-2 text-center text-sm">
                        <Link to="/forgot-password" className="text-primary hover:underline">
                            Esqueceu a senha?
                        </Link>
                    </div>

                    <div className="mt-6 pt-6 border-t">
                        <div className="flex items-center justify-between mb-4">
                            <span className="text-sm text-gray-500">Estado da conexão Firebase</span>
                            <Button variant="outline" size="sm" onClick={checkConnection} type="button">
                                Verificar
                            </Button>
                        </div>

                        {connectionStatus === "success" && (
                            <Alert className="bg-green-50 text-green-700 border-green-200">
                                <CheckCircle2 className="h-4 w-4" />
                                <AlertTitle>Conectado</AlertTitle>
                                <AlertDescription>Firebase SDK inicializado corretamente.</AlertDescription>
                            </Alert>
                        )}

                        {connectionStatus === "error" && (
                            <Alert variant="destructive">
                                <AlertCircle className="h-4 w-4" />
                                <AlertTitle>Erro</AlertTitle>
                                <AlertDescription>Falha ao conectar com Firebase.</AlertDescription>
                            </Alert>
                        )}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
};

export default Login;


