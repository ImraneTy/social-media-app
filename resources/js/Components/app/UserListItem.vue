<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    user: Object,
    forApprove: {
        type: Boolean,
        default: false
    },
})
defineEmits(['approve', 'reject', 'roleChange', 'delete'])

</script>

<template>
    <div class="bg-white transition-all border-2 border-transparent hover:border-indigo-500">


        <Link :href="route('profile', user.username)" class="flex itme-start gap-1 py-2 px-2 ">
        <img :src="user.avatar_url || '/img/defaulte_avatar.png'" class="w-[32px] h-[32px] rounded-full">
        <div>
            <h3 class="font-black text-lg">{{ user.name }}</h3>
            <div v-if="forApprove"  class="flex gap-1">
                    <button class="text-xs py-1 px-2 rounded bg-emerald-500 hover:bg-emerald-600 text-white"
                            @click.prevent.stop="$emit('approve', user)">
                        approve
                    </button>
                    <button class="text-xs py-1 px-2 rounded bg-red-500 hover:bg-red-600 text-white"
                            @click.prevent.stop="$emit('reject', user)">
                        reject
                    </button>
                </div>
        </div>

    </Link>
    </div>
</template>

<style scoped></style>
