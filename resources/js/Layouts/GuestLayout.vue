<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-custom">
   
        <div>
            <Link href="/">
                <ApplicationLogo colorClass="text-gray-900"  class="w-28 h-28 fill-current text-gray-500" />
            </Link>
        </div>
        <div>
            <Link href="/">
                <p class="text-gray-900 font-serif text-8xl">Sphere</p>
            </Link>
        </div>  

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
<style>
/* You can add a global style or scoped style depending on your project setup */
.bg-custom {
    background-image: url('/public/img/medty_white_background_with_spheres_on_it_350bfd94-f9a6-47a6-aa87-191b082ea651_1.png'); 
    background-size: cover;
    background-position: center;
}
</style>