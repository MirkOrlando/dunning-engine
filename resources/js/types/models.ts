export type Customer = {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
};

export type Plan = {
    id: number;
    code: string;
    name: string;
    price: string;
};

export type SubscriptionStatus = 'active' | 'past_due' | 'suspended';

export type Payment = {
    id: number;
    subscription_id: number;
    attempted_at: string;
    succeeded: boolean;
    failure_reason: string | null;
};

export type Subscription = {
    id: number;
    customer: Customer;
    plan: Plan;
    status: SubscriptionStatus;
    next_payment_due_at: string | null;
    failed_attempts_count: number;
    payments: Payment[];
};
