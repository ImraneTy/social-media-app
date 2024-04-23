<script setup>
import PostItem from './PostItem.vue';
import PostModal from "@/Components/app/PostModal.vue";
import { ref} from 'vue'

defineProps({
    posts:Array
})


const showEditModal=ref(false)
const editPost=ref({})

function openEditModal(post){
    editPost.value=post;
    showEditModal.value=true;
}

const post1 ={
    user:{
        id:1,
        avatar:'https://randomuser.me/api/portraits/women/76.jpg',
        name:'jhon Smith'
        },
        group:null,
        attachments:[{
    id:1,
    name:'test.png',
    url:'https://picsum.photos/1000',
    mime:'image/png' 
}
,{
    id:2,
    name:'test.png',
    url:'https://picsum.photos/1000',
    mime:'image/png' 
}
,{
    id:3,
    name:'My Document.docx',
    url:'#',
    mime:'application/msword' 
}],
    body: ` <p> lorelllm lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  <p/>
        <p> lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  <p/> 
            <p> lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem lorem  <p/>`
                ,
    created_at:'2024-4-2 18:10'
}
const post2 = {
  user: {
    id: 2,
    avatar: 'https://randomuser.me/api/portraits/men/77.jpg',
    name: 'Jane Doe'
  },
  group: {
    id:1,
    name:'Laravel Developers'
},
attachments:[{
    id:1,
    name:'test.png',
    url:'https://picsum.photos/1000',
    mime:'image/png' 
}
]
,
  body: ` <p> Another post body </p>`,
  created_at: '2024-04-02 18:20'
};


</script>

<template>
    <div class="h-full overflow-auto">
        <PostItem v-for=" post of posts" :key="post.id" :post="post" @editClick="openEditModal"/>
        <PostModal :post="editPost" v-model="showEditModal"/>

    </div>

</template>