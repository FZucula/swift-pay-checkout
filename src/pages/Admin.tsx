import { useEffect, useState } from "react";
import { getPayments, PaymentDataWithId } from "@/services/paymentStorage";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { format } from "date-fns";

const Admin = () => {
    const [payments, setPayments] = useState<PaymentDataWithId[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchPayments = async () => {
            const data = await getPayments();
            setPayments(data);
            setLoading(false);
        };

        fetchPayments();
    }, []);

    if (loading) {
        return <div className="p-8">Loading...</div>;
    }

    return (
        <div className="container mx-auto p-8">
            <Card>
                <CardHeader>
                    <CardTitle>Payment History</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead>Payment ID</TableHead>
                                <TableHead>Method</TableHead>
                                <TableHead>Amount</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Details</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {payments.map((payment) => (
                                <TableRow key={payment.id}>
                                    <TableCell>
                                        {payment.createdAt
                                            ? format(payment.createdAt, "PPpp")
                                            : "N/A"}
                                    </TableCell>
                                    <TableCell className="font-mono text-xs">
                                        {payment.paymentId}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="outline">{payment.method}</Badge>
                                    </TableCell>
                                    <TableCell>
                                        {payment.amount.toLocaleString("pt-MZ")} MZN
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                payment.status === "success" ? "default" : "destructive"
                                            }
                                            className={
                                                payment.status === "success" ? "bg-green-500 hover:bg-green-600" : ""
                                            }
                                        >
                                            {payment.status}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="text-xs text-muted-foreground truncate max-w-[200px]" title={JSON.stringify(payment.details)}>
                                            {payment.phoneNumber && `Phone: ${payment.phoneNumber}`}
                                            {payment.email && `Email: ${payment.email}`}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
};

export default Admin;
