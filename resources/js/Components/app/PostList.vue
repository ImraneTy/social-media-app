<script setup>
import PostItem from './PostItem.vue';

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
        <PostItem :post="post1"/>
        <PostItem :post="post2"/>

    </div>
</template>