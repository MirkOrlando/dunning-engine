<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ChevronDown, RefreshCw } from '@lucide/vue';
import { ref } from 'vue';
import SubscriptionController from '@/actions/App/Http/Controllers/SubscriptionController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index as subscriptionsIndex } from '@/routes/subscriptions';
import type { Subscription, SubscriptionStatus } from '@/types';
import PaymentHistory from './PaymentHistory.vue';

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

const expanded = ref<Set<number>>(new Set());

function isExpanded(id: number): boolean {
    return expanded.value.has(id);
}

function toggle(id: number): void {
    const next = new Set(expanded.value);

    if (!next.delete(id)) {
        next.add(id);
    }

    expanded.value = next;
}

function formatDate(date: string | null): string {
    if (!date) {
        return '—';
    }

    return new Date(date).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
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

        <!-- Table: tablet and up -->
        <div class="hidden rounded-xl border md:block">
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead>Customer</TableHead>
                        <TableHead>Plan</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Next payment due</TableHead>
                        <TableHead class="text-right">
                            Failed attempts
                        </TableHead>
                        <TableHead class="w-px" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template
                        v-for="subscription in subscriptions"
                        :key="subscription.id"
                    >
                        <TableRow
                            :class="{
                                'border-b-0': isExpanded(subscription.id),
                            }"
                        >
                            <TableCell>
                                <p class="font-medium">
                                    {{ subscription.customer.first_name }}
                                    {{ subscription.customer.last_name }}
                                </p>
                                <p class="text-muted-foreground text-xs">
                                    {{ subscription.customer.email }}
                                </p>
                            </TableCell>
                            <TableCell>
                                <p class="font-medium">
                                    {{ subscription.plan.name }}
                                </p>
                                <p class="text-muted-foreground text-xs">
                                    {{ subscription.plan.price }} / month
                                </p>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    :variant="statusVariants[subscription.status]"
                                >
                                    {{ statusLabels[subscription.status] }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                {{
                                    formatDate(subscription.next_payment_due_at)
                                }}
                            </TableCell>
                            <TableCell class="text-right">
                                {{ subscription.failed_attempts_count }}
                            </TableCell>
                            <TableCell class="text-right">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    :aria-expanded="isExpanded(subscription.id)"
                                    @click="toggle(subscription.id)"
                                >
                                    Payments ({{ subscription.payments.length }})
                                    <ChevronDown
                                        class="transition-transform"
                                        :class="{
                                            'rotate-180': isExpanded(
                                                subscription.id,
                                            ),
                                        }"
                                    />
                                </Button>
                            </TableCell>
                        </TableRow>

                        <TableRow
                            v-if="isExpanded(subscription.id)"
                            class="bg-muted/30 hover:bg-muted/30"
                        >
                            <TableCell colspan="6">
                                <PaymentHistory
                                    :payments="subscription.payments"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Cards: mobile, where the table no longer fits -->
        <div class="flex flex-col gap-3 md:hidden">
            <Card v-for="subscription in subscriptions" :key="subscription.id">
                <CardHeader
                    class="flex flex-row items-start justify-between gap-4"
                >
                    <div class="min-w-0">
                        <p class="font-medium">
                            {{ subscription.customer.first_name }}
                            {{ subscription.customer.last_name }}
                        </p>
                        <p class="text-muted-foreground truncate text-sm">
                            {{ subscription.customer.email }}
                        </p>
                    </div>

                    <Badge :variant="statusVariants[subscription.status]">
                        {{ statusLabels[subscription.status] }}
                    </Badge>
                </CardHeader>

                <CardContent class="flex flex-col gap-4">
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Plan</dt>
                            <dd class="font-medium">
                                {{ subscription.plan.name }}
                                <span
                                    class="text-muted-foreground block font-normal"
                                >
                                    {{ subscription.plan.price }} / month
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">
                                Failed attempts
                            </dt>
                            <dd>{{ subscription.failed_attempts_count }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-muted-foreground">
                                Next payment due
                            </dt>
                            <dd>
                                {{
                                    formatDate(subscription.next_payment_due_at)
                                }}
                            </dd>
                        </div>
                    </dl>

                    <Collapsible>
                        <CollapsibleTrigger as-child>
                            <Button variant="ghost" size="sm">
                                Payment history ({{
                                    subscription.payments.length
                                }})
                            </Button>
                        </CollapsibleTrigger>

                        <CollapsibleContent class="mt-2">
                            <PaymentHistory :payments="subscription.payments" />
                        </CollapsibleContent>
                    </Collapsible>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
