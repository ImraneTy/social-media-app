<script setup>



defineProps({
    selected:Boolean,
    text:String 
}

)

</script>

<template>

<button
              :class="[
                ' px-3 py-2.5 outline-none text-small',
                selected
                  ? 'bg-white text-blue-500 border-b-2 border-blue-500 '  
                  : 'text-gray-700',
              ]"
            > 
               {{ text }}
            </button>
    
</template>