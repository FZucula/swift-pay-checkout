
import { db } from "../config/firebase";
import { collection, addDoc, serverTimestamp } from "firebase/firestore";

export interface PaymentData {
    paymentId: string;
    name?: string;
    email?: string;
    amount: number;
    method: "mpesa" | "mastercard";
    status: "success" | "failed" | "pending";
    phoneNumber?: string; // For Mpesa
    details?: any; // Additional response data from payment provider
}

export const savePayment = async (paymentData: PaymentData) => {
    try {
        const docRef = await addDoc(collection(db, "payments"), {
            ...paymentData,
            createdAt: serverTimestamp(),
        });
        console.log("Payment saved with ID: ", docRef.id);
        return { success: true, id: docRef.id };
    } catch (e) {
        console.error("Error adding payment document: ", e);
        return { success: false, error: e };
    }
};

export interface PaymentDataWithId extends PaymentData {
    id: string;
    createdAt?: any;
}

import { getDocs, query, orderBy, Timestamp } from "firebase/firestore";

export const getPayments = async (): Promise<PaymentDataWithId[]> => {
    try {
        const q = query(collection(db, "payments"), orderBy("createdAt", "desc"));
        const querySnapshot = await getDocs(q);
        return querySnapshot.docs.map(doc => ({
            id: doc.id,
            ...doc.data() as PaymentData,
            createdAt: doc.data().createdAt?.toDate() // Convert Timestamp to Date
        }));
    } catch (e) {
        console.error("Error fetching payments: ", e);
        return [];
    }
};
