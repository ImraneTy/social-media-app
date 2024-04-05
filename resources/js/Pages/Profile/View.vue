<template>
    <AuthenticatedLayout>
        <div class="w-[800px] bg-white mx-auto h-full overflow-auto  ">


            <div class="relative h-[200]">
                <img src="https://scontent.frak1-2.fna.fbcdn.net/v/t39.30808-6/215426776_844754353092566_5308695455289309254_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=5f2048&_nc_ohc=58zaIwolutcAb6nJH3Y&_nc_ht=scontent.frak1-2.fna&oh=00_AfDamjvRGwbl_Wf66Pau-bUTmJZl-gPTuHNxtJs3ul_9qg&oe=6615A9F8"
                    class="w-full h-[200px]  object-cover">
                <div class="flex ">
                    <img src="https://scontent.frak1-2.fna.fbcdn.net/v/t39.30808-1/331754035_723051342692030_2163097403218800183_n.jpg?stp=c144.0.480.480a_dst-jpg_p480x480&_nc_cat=110&ccb=1-7&_nc_sid=5f2048&_nc_ohc=_qfuH1mfmyAAb6xRJan&_nc_ht=scontent.frak1-2.fna&oh=00_AfBz3ZGb78eYzYQnelcsZSKfMIu3Ot1AxJg2zdZh7HKyCg&oe=6615BD26"
                        class=" ml-[48px]  w-[128px] h-[128px] -mt-[64px] rounded-full ">
                    <div class="flex justify-between items-center  flex-1 p-4 ">
                        <h2 class="font-bold  text-lg">{{ user.name }}</h2>
                        <PrimaryButton v-if="isMyProfile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-4 h-4 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                            </svg>

                            Edit Profile
                        </PrimaryButton>
                    </div>
                </div>

            </div>
            <div class="border-t-1">
                <TabGroup>
                    <TabList class=" flex bg-white ">
                        <Tab v-if="isMyProfile" v-slot="{ selected }" as="template">
                            <TabItem text="About" :selected="selected" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="posts" :selected="selected" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Followers" :selected="selected" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Followings" :selected="selected" />
                        </Tab>
                        <Tab v-slot="{ selected }" as="template">
                            <TabItem text="Photos" :selected="selected" />
                        </Tab>
                    </TabList>

                    <TabPanels class="mt-2">
                        <TabPanel v-if="isMyProfile"   >
                            <Edit :must-verify-email="mustVerifyEmail" :status="status" />
                        </TabPanel>

                        <TabPanel  class="bg-white p-3 focus:ring-2 shadow">
                            posts
                        </TabPanel>

                        <TabPanel  class="bg-white p-3 focus:ring-2 shadow">
                            Followings
                        </TabPanel>

                        <TabPanel  class="bg-white p-3 focus:ring-2 shadow">
                            Followers
                        </TabPanel>

                        <TabPanel  class="bg-white p-3 focus:ring-2 shadow">
                            images
                        </TabPanel>


                    </TabPanels>
                </TabGroup>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref,computed } from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { usePage } from "@inertiajs/vue3";
import TabItem from '../../Components/app/TabItem.vue';
import Edit from "@/Pages/Profile/Edit.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const authUser = usePage().props.auth.user;
const isMyProfile=computed(()=>authUser && authUser.id === props.user.id)

const props=  defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    user:{
        type:Object
    }
});

</script>
