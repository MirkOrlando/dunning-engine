<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { Payment } from '@/types';

defineProps<{
    payments: Payment[];
}>();

function formatDate(date: string | null): string {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function formatFailureReason(reason: string | null): string {
    return reason ? reason.replaceAll('_', ' ') : '';
}
</script>

<template>
    <p v-if="payments.length === 0" class="text-muted-foreground text-sm">
        No payments have been attempted yet.
    </p>

    <ul v-else class="flex flex-col gap-2">
        <li
            v-for="payment in payments"
            :key="payment.id"
            class="flex items-center justify-between gap-4 border-b pb-2 text-sm last:border-b-0 last:pb-0"
        >
            <span>{{ formatDate(payment.attempted_at) }}</span>

            <Badge :variant="payment.succeeded ? 'default' : 'destructive'">
                {{
                    payment.succeeded
                        ? 'Succeeded'
                        : formatFailureReason(payment.failure_reason)
                }}
            </Badge>
        </li>
    </ul>
</template>
