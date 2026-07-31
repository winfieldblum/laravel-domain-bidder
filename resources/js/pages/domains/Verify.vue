<script setup lang="ts">
import DomainLayout from '@/layouts/DomainLayout.vue';
import { create as offerCreate } from '@/actions/App/Http/Controllers/OfferController';
import { home } from '@/routes';
import { CheckCircle2, XCircle } from '@lucide/vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    success: boolean;
    amount: number | null;
    hostname: string | null;
}>();

function formatMoney(amount: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(amount);
}
</script>

<template>
    <DomainLayout :hostname="hostname || 'Domain'">
        <Head title="Verify email" />

        <div class="flex min-h-[80vh] items-center justify-center p-4">
            <div class="w-full max-w-md space-y-6 text-center">
                <template v-if="success">
                    <div
                        class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100"
                    >
                        <CheckCircle2 class="h-10 w-10 text-green-600" />
                    </div>
                    <h2 class="font-display text-3xl font-bold text-slate-900">
                        Email Verified!
                    </h2>
                    <div class="space-y-2">
                        <p class="text-lg text-slate-600">
                            Your offer has been confirmed.
                        </p>
                        <p
                            v-if="amount"
                            class="font-semibold text-slate-700"
                        >
                            Bid Amount: {{ formatMoney(amount) }}
                        </p>
                        <p class="text-sm text-slate-500">
                            The domain owner will review your bid shortly.
                        </p>
                    </div>
                    <div class="pt-8">
                        <Link
                            :href="home()"
                            class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-indigo-700 px-4 font-medium text-white hover:bg-indigo-800"
                        >
                            Return Home
                        </Link>
                    </div>
                </template>

                <template v-else>
                    <div
                        class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100"
                    >
                        <XCircle class="h-10 w-10 text-red-600" />
                    </div>
                    <h2 class="font-display text-3xl font-bold text-slate-900">
                        Verification Failed
                    </h2>
                    <p class="text-lg text-slate-600">
                        This verification link is invalid or has expired. Please
                        submit a new offer.
                    </p>
                    <div class="pt-8">
                        <Link
                            :href="offerCreate()"
                            class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-indigo-700 px-4 font-medium text-white hover:bg-indigo-800"
                        >
                            Submit New Offer
                        </Link>
                    </div>
                </template>
            </div>
        </div>
    </DomainLayout>
</template>
