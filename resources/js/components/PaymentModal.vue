<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    reservationId: number;
    amount: string;
    methods: Record<string, { label: string; type: string }>;
    yapeQrUrl: string | null;
}>();

const emit = defineEmits<{
    close: [];
}>();

const method = ref('yape');
const processing = ref(false);
const operationNumber = ref('');
const voucher = ref<File | null>(null);

const methods = computed(() => props.methods);

const demoQrUrl =
    'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=DEVIOZ-PARKING-DEMO-YAPE';

function pay(): void {
    processing.value = true;

    const data = new FormData();

    data.append('method', method.value);
    data.append('provider_tx_id', operationNumber.value);

    if (voucher.value) {
        data.append('voucher', voucher.value);
    }

    router.post(
        `/reservations/${props.reservationId}/payment`,
        data,
        {
            forceFormData: true,
            onFinish: () => {
                processing.value = false;
                emit('close');
            },
        },
    );
}

function selectVoucher(event: Event): void {
    voucher.value =
        (event.target as HTMLInputElement)
            .files?.[0] ?? null;
}
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 p-4"
        @click.self="emit('close')"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-xl border border-border bg-card text-card-foreground shadow-2xl"
        >
            <div
                class="flex shrink-0 items-start justify-between gap-4 border-b border-border px-4 py-3"
            >
                <div>
                    <p class="text-sm text-muted-foreground">
                        Pago seguro de demostración
                    </p>

                    <h2 class="text-lg font-semibold text-foreground">
                        Pagar {{ amount }}
                    </h2>
                </div>

                <button
                    type="button"
                    class="rounded-md p-1 text-muted-foreground transition hover:bg-accent hover:text-foreground"
                    aria-label="Cerrar"
                    @click="emit('close')"
                >
                    ✕
                </button>
            </div>

            <div
                class="min-h-0 flex-1 space-y-4 overflow-y-auto p-4"
            >
                <div
                    class="rounded-lg border-2 border-blue-500/80 bg-blue-500/10 p-3"
                >
                    <p class="font-semibold text-foreground">
                        Yape · método principal
                    </p>

                    <div
                        class="mx-auto mt-3 flex h-40 w-40 items-center justify-center overflow-hidden rounded-lg border-4 border-white bg-white shadow-inner"
                    >
                        <img
                            :src="yapeQrUrl || demoQrUrl"
                            alt="Código QR de Yape de Estacionamiento Devioz"
                            class="size-full object-contain"
                        />
                    </div>

                    <p
                        class="mt-3 text-center text-xs text-muted-foreground"
                    >
                        Escanea el QR y realiza el pago por
                        {{ amount }}.
                    </p>
                </div>

                <div class="grid gap-4">
                    <label
                        class="grid gap-2 text-sm font-medium text-foreground"
                    >
                        Nº de operación

                        <input
                            v-model="operationNumber"
                            type="text"
                            required
                            maxlength="100"
                            class="rounded-md border border-input bg-background px-3 py-2 text-foreground placeholder:text-muted-foreground outline-none transition focus:border-ring focus:ring-2 focus:ring-ring/20"
                            placeholder="Ej. 123456789"
                        />
                    </label>

                    <label
                        class="grid gap-2 text-sm font-medium text-foreground"
                    >
                        Voucher de pago

                        <input
                            type="file"
                            required
                            accept="image/jpeg,image/png,image/webp,application/pdf"
                            class="rounded-md border border-input bg-background p-2 text-sm text-foreground file:mr-3 file:rounded-md file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-accent-foreground"
                            @change="selectVoucher"
                        />
                    </label>
                </div>

                <div class="grid gap-2">
                    <div
                        v-for="(
                            paymentMethod,
                            paymentMethodKey
                        ) in methods"
                        :key="paymentMethodKey"
                        class="flex items-center justify-between rounded-lg border border-border bg-background/40 p-3"
                        :class="
                            paymentMethodKey !== 'yape'
                                ? 'opacity-60'
                                : ''
                        "
                    >
                        <span class="text-sm font-medium text-foreground">
                            {{ paymentMethod.label }}
                        </span>

                        <span
                            v-if="paymentMethodKey !== 'yape'"
                            class="text-xs font-semibold uppercase text-muted-foreground"
                        >
                            Próximamente
                        </span>
                    </div>
                </div>

                <p class="text-xs text-muted-foreground">
                    El pago quedará pendiente hasta que el
                    administrador verifique el voucher.
                </p>
            </div>

            <div
                class="sticky bottom-0 flex shrink-0 justify-end gap-2 border-t border-border bg-card px-4 py-3"
            >
                <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    @click="emit('close')"
                >
                    Cancelar
                </Button>

                <Button
                    type="button"
                    size="sm"
                    :disabled="
                        processing ||
                        !operationNumber ||
                        !voucher
                    "
                    @click="pay()"
                >
                    {{
                        processing
                            ? 'Enviando...'
                            : 'Enviar voucher'
                    }}
                </Button>
            </div>
        </div>
    </div>
</template>