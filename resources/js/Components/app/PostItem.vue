<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { ChevronDownIcon, PencilIcon, TrashIcon,EllipsisVerticalIcon, ArrowDownTrayIcon, PaperClipIcon } from '@heroicons/vue/20/solid'
import PostUserHeader from "@/Components/app/PostUserHeader.vue";
import{ref} from "vue";
import axiosClient from "@/axiosClient.js";
import { router } from '@inertiajs/vue3';
import {isImage} from '../../helpers'
import { ChatBubbleLeftRightIcon, HandThumbUpIcon } from '@heroicons/vue/24/outline';
const props=defineProps({
    post: Object,
});

const emit = defineEmits(['editClick','attachmentClick'])


function openEditModal() {
    emit('editClick', props.post)
}

function deletePost(){
    if(window.confirm('are you sure you want to delete this post')){
        router.delete(route('post.destroy',props.post),{
            preserveScroll:true 
        })
    }
}
function openAttachment(ind){
    emit('attachmentClick',props.post,ind)
}


function sendReaction(){
    axiosClient.post(route('post.reaction', props.post), {
        reaction: 'like'
    })
    .then(({data}) => {
            props.post.current_user_has_reaction = data.current_user_has_reaction
            props.post.num_of_reactions = data.num_of_reactions;
        })
}
</script>

<template>

    <div class="bg-white border rounded p-4 mb-3">
        <div class="flex item-center justify-between  mb-3">

            <PostUserHeader :post="post"/>
                <Menu as="div" class="relative z-10 inline-block text-left">
                    <div>
                        <MenuButton 
                        class="w-8 h-8 z-10 rounded-full hover:bg-black/5 transition flex items-center justify-center ">

                            
                            <EllipsisVerticalIcon class="w-5 h-5 "
                                aria-hidden="true" />
                        </MenuButton>
                    </div>

                    <transition enter-active-class="transition duration-100 ease-out"
                        enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-75 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0">
                        <MenuItems
                            class="absolute right-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                            <div class="px-1 py-1">
                                <MenuItem v-slot="{ active }">
                                <button 
                                @click="openEditModal"
                                :class="[
                        active ? 'bg-indigo-500 text-white' : 'text-gray-900',
                        'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                    ]">
                                    <PencilIcon class="mr-2 h-5 w-5 " aria-hidden="true" />
                                    Edit
                                </button>
                                </MenuItem>
                                <MenuItem v-slot="{ active }">
                                <button
                                @click="deletePost"
                                :class="[
                        active ? 'bg-indigo-500 text-white' : 'text-gray-900',
                        'group flex w-full items-center rounded-md px-2 py-2 text-sm',
                    ]">
                                    <TrashIcon class="mr-2 h-5 w-5 " aria-hidden="true" />
                                    Delete
                                </button>
                                </MenuItem>
                                
                            </div>
                        </MenuItems>
                    </transition>
                </Menu>






        </div>
        <div class="mb-3">
            <Disclosure v-slot="{ open }">
        <div class="ck-content-output"  v-if="!open" v-html="post.body.substring(0, 200)"/>

        <template v-if="post.body && post.body.length > 200">
            <DisclosurePanel>
                <div class="ck-content-output"  v-html="post.body"/>
            </DisclosurePanel>
            <div class="flex justify-end">
                <DisclosureButton class="text-blue-500 hover:underline">
                    {{ open ? 'Read less' : 'Read More' }}
                </DisclosureButton>
            </div>
        </template>
    </Disclosure>
        </div>

        <div class="grid    gap-3 mb-3 " :class="[
            post.attachments.length===1 ?'grid-cols-1' : 'grid-cols-2'
        ]">
            <template v-for="(attachment,ind) of post.attachments.slice(0,4)">


                <div
                @click="openAttachment(ind)"
                    class="group relative aspect-square bg-blue-100 flex  flex-col items-center justify-center text-gray-500 cursor-pointer ">
                    <div v-if="ind === 3  && post.attachments.length > 4 "
                 class="absolute left-0 top-0 right-0 bottom-0 z-10 bg-black/60 text-white flex items-center justify-center text-2xl">
                +{{ post.attachments.length - 4 }} more
            </div>
                    <!-- Download -->
                    <a :href="route('post.download', attachment)"
                        class="w-8 h-8 z-20 opacity-0 group-hover:opacity-100 transition-all  flex  items-center justify-center text-white  bg-gray-700 rounded absolute right-2 top-2 cursor-pointer hover:bg-gray-800">
                        <ArrowDownTrayIcon class="w-4 h-4"/>
                    </a>
                    <!-- Download -->

                    <img v-if="isImage(attachment)" :src="attachment.url" class="object-contain aspect-square">

                    <template v-else>
                        <PaperClipIcon class="w-12 h-12"/>

                        <small>{{ attachment.name }}</small>
                    </template>

                </div>

            </template>
        </div>
        <div class="flex gap-2">
            <button
            @click="sendReaction"
                class="text-gray-800 flex gap-1 py-2 px-4 rounded-lg  items-center bg-gray-100 justify-center  flex-1 hover:bg-gray-200"
                :class="[
                    post.current_user_has_reaction ?
                     'bg-sky-100 hover:bg-sky-200 ' :
                     'bg-gray-100 hover:bg-gray-200 '
                ]"
                >
            <HandThumbUpIcon class="w-5 h-5"/>
            <span class="mr-2">{{ post.num_of_reactions }}</span>
                    {{ post.current_user_has_reaction ? 'Unlike' : 'Like' }}
            </button>
            <button
                class="text-gray-800 flex gap-1 py-2 px-4 rounded-lg items-center bg-gray-100 justify-center flex-1 hover:bg-gray-200">
            <ChatBubbleLeftRightIcon class="w-5 h-5 mr-2"/>
                Comment
            </button>
        </div>
    </div>

</template>
