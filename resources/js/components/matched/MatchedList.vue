<template>
    <ChatRoomsList :hidden-search="true" :has-router="false" :show-icon-chat="true"/>
</template>

<script>
import ChatRoomsList from '@/pages/chat/ChatRoomsList.vue';

export default {
    components: {
        ChatRoomsList
    }
}
</script>
