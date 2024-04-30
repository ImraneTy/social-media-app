<script setup>
import PostItem from './PostItem.vue';
import PostModal from "@/Components/app/PostModal.vue";
import { ref} from 'vue'
import {usePage} from "@inertiajs/vue3";
import AttachmentPreviewModal from "@/Components/app/AttachmentPreviewModal.vue";
 

const authUser = usePage().props.auth.user;


defineProps({
    posts:Array
})

const previewAttachmentsPost = ref({})
const showAttachmentsModal = ref(false)

const showEditModal=ref(false)
const editPost=ref({})

function openEditModal(post){
    editPost.value=post;
    showEditModal.value=true;
}


function openAttachmentPreviewModal(post, index) {
    previewAttachmentsPost.value = {
        post,
        index
    }
    showAttachmentsModal.value = true;
}


function onModalHide(){
    editPost.value={
        id:null,
    body:'',
    user:authUser
    }
}


</script>

<template>
    <div class=" overflow-auto">
        <PostItem v-for=" post of posts" :key="post.id" :post="post" @editClick="openEditModal" @attachmentClick="openAttachmentPreviewModal"/>
        <PostModal :post="editPost" v-model="showEditModal" @hide="onModalHide"/>
        <AttachmentPreviewModal :attachments="previewAttachmentsPost.post?.attachments || []"
                                v-model:index="previewAttachmentsPost.index"
                                v-model="showAttachmentsModal"/>

    </div>

</template>