
import { describe, it, expect, beforeAll } from 'vitest';
import { savePayment, getPayments, PaymentData } from '../services/paymentStorage';
import { signInAnonymously, signInWithEmailAndPassword } from 'firebase/auth';
import { auth } from '../config/firebase';

describe('Firebase Payment Creation', () => {
    beforeAll(async () => {
        const email = import.meta.env.VITE_TEST_EMAIL;
        const password = import.meta.env.VITE_TEST_PASSWORD;

        try {
            if (email && password) {
                await signInWithEmailAndPassword(auth, email, password);
                console.log(`Signed in as ${email} for testing`);
            } else {
                console.log('No test credentials provided, attempting anonymous sign in...');
                await signInAnonymously(auth);
                console.log('Signed in anonymously for testing');
            }
        } catch (error) {
            console.error('Failed to sign in (proceeding as unauthenticated):', error);
        }
    });

    it('should check read permissions then create payment records', async () => {
        // Check read permissions first
        try {
            console.log('Attempting to read existing payments...');
            const existing = await getPayments();
            console.log(`Successfully read ${existing.length} existing payments.`);
        } catch (error) {
            console.log('Read failed (expected if rules block it):', error);
        }

        const payments: PaymentData[] = [
            {
                paymentId: `TEST_PENDING_${Date.now()}`,
                amount: 500,
                status: 'pending',
                method: 'mpesa',
                name: 'Test User Pending',
                email: 'pending@example.com',
                phoneNumber: '258840000001'
            },
            {
                paymentId: `TEST_SUCCESS_${Date.now()}`,
                amount: 1000,
                status: 'success',
                method: 'mastercard',
                name: 'Test User Success',
                email: 'success@example.com'
            },
            {
                paymentId: `TEST_FAILED_${Date.now()}`,
                amount: 1500,
                status: 'failed',
                method: 'mpesa',
                name: 'Test User Failed',
                email: 'failed@example.com',
                phoneNumber: '258840000002'
            }
        ];

        for (const payment of payments) {
            console.log(`Creating payment: ${payment.status}...`);
            const result = await savePayment(payment);
            expect(result.success).toBe(true);
            expect(result.id).toBeDefined();
            console.log(`Created payment ${payment.status} with ID: ${result.id}`);
        }
    });
});
