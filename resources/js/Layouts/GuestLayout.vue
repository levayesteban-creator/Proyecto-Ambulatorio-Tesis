<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative">
        <!-- Imagen de fondo del consultorio con transparencia -->
        <div class="absolute inset-0 z-0">
            <div class="w-full h-full bg-cover bg-center opacity-30"
                 style="background-image: url('/images/consultorio-background.jpg');">
            </div>
            <!-- Overlay para asegurar legibilidad -->
            <div class="absolute inset-0 bg-gray-100 opacity-70"></div>
        </div>

        <!-- Contenido principal con z-index para estar sobre la imagen -->
        <div class="relative z-10 w-full flex flex-col sm:justify-center items-center px-6">
            <div class="text-center mb-6">
                <Link href="/">
                    <ApplicationLogo class="w-24 h-24 fill-current text-gray-700 mx-auto" />
                </Link>
            </div>

            <div
                class="w-full sm:max-w-md px-6 py-8 bg-white bg-opacity-95 backdrop-blur-sm shadow-lg overflow-hidden sm:rounded-lg border border-gray-200"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
