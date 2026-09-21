<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

interface Payment {
    id: number;
    amount: string;
    provider_tx_id: string;
    voucher_path: string | null;
    created_at: string;
    reservation: { id: number; user: { name: string; email: string }; space: { code: string } };
}

defineProps<{ payments: Payment[] }>();

function processPayment(id: number, action: 'confirm' | 'reject'): void {
    router.post(`/admin/payments/${id}/${action}`);
}
</script>

<template>
    <Head title="Pagos por confirmar" />
    <div class="flex flex-1 flex-col gap-6 p-6">
        <div><p class="text-sm text-muted-foreground">Administrador</p><h1 class="text-2xl font-semibold">Pagos por confirmar</h1><p class="mt-1 text-sm text-muted-foreground">Verifica los vouchers Yape antes de marcar una reserva como pagada.</p></div>
        <section class="overflow-hidden rounded-xl border bg-card">
            <div v-if="!payments.length" class="p-8 text-center text-muted-foreground">No hay pagos pendientes de revisión.</div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Conductor</th><th class="p-4">Reserva</th><th class="p-4">Operación</th><th class="p-4">Monto</th><th class="p-4">Voucher</th><th class="p-4">Acciones</th></tr></thead>
                    <tbody class="divide-y">
                        <tr v-for="payment in payments" :key="payment.id">
                            <td class="p-4"><p class="font-medium">{{ payment.reservation.user.name }}</p><p class="text-xs text-muted-foreground">{{ payment.reservation.user.email }}</p></td>
                            <td class="p-4">#{{ payment.reservation.id }} · {{ payment.reservation.space.code }}</td>
                            <td class="p-4 font-mono">{{ payment.provider_tx_id }}</td>
                            <td class="p-4 font-semibold">S/ {{ Number(payment.amount).toFixed(2) }}</td>
                            <td class="p-4"><a v-if="payment.voucher_path" :href="`/storage/${payment.voucher_path}`" target="_blank" rel="noreferrer" class="font-medium text-blue-700 underline">Ver voucher</a><span v-else class="text-muted-foreground">Sin archivo</span></td>
                            <td class="p-4"><div class="flex flex-wrap gap-2"><Button size="sm" @click="processPayment(payment.id, 'confirm')">✅ Confirmar</Button><Button size="sm" variant="destructive" @click="processPayment(payment.id, 'reject')">❌ Rechazar</Button></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
