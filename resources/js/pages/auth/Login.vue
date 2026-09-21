<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';

import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Iniciar sesión',
        description:
            'Ingresa tu correo electrónico y contraseña para acceder a DEVIOZ PARKING',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Iniciar sesión" />

    <div
        v-if="status"
        class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-center text-sm font-medium text-emerald-500"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <!-- CORREO -->
            <div class="grid gap-2">
                <Label for="email">
                    Correo electrónico
                </Label>

                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="usuario@correo.com"
                />

                <InputError :message="errors.email" />
            </div>

            <!-- CONTRASEÑA -->
            <div class="grid gap-2">
                <div
                    class="flex items-center justify-between gap-4"
                >
                    <Label for="password">
                        Contraseña
                    </Label>

                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm"
                        :tabindex="5"
                    >
                        ¿Olvidaste tu contraseña?
                    </TextLink>
                </div>

                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Contraseña"
                />

                <InputError :message="errors.password" />
            </div>

            <!-- RECORDARME -->
            <div
                class="flex items-center justify-between"
            >
                <Label
                    for="remember"
                    class="flex cursor-pointer items-center space-x-3"
                >
                    <Checkbox
                        id="remember"
                        name="remember"
                        :tabindex="3"
                    />

                    <span>Recordarme</span>
                </Label>
            </div>

            <!-- BOTÓN -->
            <Button
                type="submit"
                class="mt-2 w-full"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />

                {{
                    processing
                        ? 'Ingresando...'
                        : 'Iniciar sesión'
                }}
            </Button>
        </div>

        <!-- REGISTRO -->
        <div
            class="text-muted-foreground text-center text-sm"
        >
            ¿No tienes una cuenta?

            <TextLink
                :href="register()"
                :tabindex="5"
            >
                Regístrate
            </TextLink>
        </div>

        <!-- SEGURIDAD -->
        <div
            class="border-t border-border pt-5"
        >
            <div
                class="rounded-lg border border-border bg-muted/30 px-4 py-3"
            >
                <p
                    class="text-center text-xs leading-5 text-muted-foreground"
                >
                    Acceso protegido mediante autenticación
                    segura y verificación en dos pasos.
                </p>
            </div>
        </div>
    </Form>
</template>