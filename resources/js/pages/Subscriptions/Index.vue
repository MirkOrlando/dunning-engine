<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { RefreshCw } from '@lucide/vue';
import SubscriptionController from '@/actions/App/Http/Controllers/SubscriptionController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { index as subscriptionsIndex } from '@/routes/subscriptions';
import type { Subscription, SubscriptionStatus } from '@/types';

defineProps<{
    subscriptions: Subscription[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Subscriptions',
                href: subscriptionsIndex(),
            },
        ],
    },
});

const statusLabels: Record<SubscriptionStatus, string> = {
    active: 'Active',
    past_due: 'Past due',
    suspended: 'Suspended',
};

const statusVariants: Record<
    SubscriptionStatus,
    'default' | 'secondary' | 'destructive'
> = {
    active: 'default',
    past_due: 'secondary',
    suspended: 'destructive',
};

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
    if (!reason) {
        return '';
    }

    return reason.replaceAll('_', ' ');
}
</script>

<template>
    <Head title="Subscriptions" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-start justify-between gap-4">
            <Heading
                title="Subscriptions"
                description="Customer subscriptions, their current status and payment history."
            />

            <Form
                v-bind="SubscriptionController.processPayments.form()"
                v-slot="{ processing }"
            >
                <Button type="submit" :disabled="processing">
                    <RefreshCw :class="{ 'animate-spin': processing }" />
                    Run payment processing job
                </Button>
            </Form>
        </div>

        <div class="flex flex-col gap-3">
            <Card v-for="subscription in subscriptions" :key="subscription.id">
                <CardHeader
                    class="flex flex-row flex-wrap items-center justify-between gap-4"
                >
                    <div>
                        <p class="font-medium">
                            {{ subscription.customer.first_name }}
                            {{ subscription.customer.last_name }}
                        </p>
                        <p class="text-muted-foreground text-sm">
                            {{ subscription.customer.email }}
                        </p>
                    </div>

                    <div class="text-sm">
                        <p class="font-medium">{{ subscription.plan.name }}</p>
                        <p class="text-muted-foreground">
                            {{ subscription.plan.price }} / month
                        </p>
                    </div>

                    <Badge :variant="statusVariants[subscription.status]">
                        {{ statusLabels[subscription.status] }}
                    </Badge>

                    <div class="text-sm">
                        <p class="text-muted-foreground">Next payment due</p>
                        <p>{{ formatDate(subscription.next_payment_due_at) }}</p>
                    </div>

                    <div class="text-sm">
                        <p class="text-muted-foreground">Failed attempts</p>
                        <p>{{ subscription.failed_attempts_count }}</p>
                    </div>
                </CardHeader>

                <CardContent>
                    <Collapsible>
                        <CollapsibleTrigger as-child>
                            <Button variant="ghost" size="sm">
                                Payment history
                                ({{ subscription.payments.length }})
                            </Button>
                        </CollapsibleTrigger>

                        <CollapsibleContent class="mt-2">
                            <p
                                v-if="subscription.payments.length === 0"
                                class="text-muted-foreground text-sm"
                            >
                                No payments have been attempted yet.
                            </p>

                            <ul v-else class="flex flex-col gap-2">
                                <li
                                    v-for="payment in subscription.payments"
                                    :key="payment.id"
                                    class="flex items-center justify-between gap-4 border-b pb-2 text-sm last:border-b-0 last:pb-0"
                                >
                                    <span>{{
                                        formatDate(payment.attempted_at)
                                    }}</span>

                                    <Badge
                                        :variant="
                                            payment.succeeded
                                                ? 'default'
                                                : 'destructive'
                                        "
                                    >
                                        {{
                                            payment.succeeded
                                                ? 'Succeeded'
                                                : formatFailureReason(
                                                      payment.failure_reason,
                                                  )
                                        }}
                                    </Badge>
                                </li>
                            </ul>
                        </CollapsibleContent>
                    </Collapsible>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
