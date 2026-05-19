<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="getLinkData()">    
        <template v-slot:text>
            {{notificationItem.message}}
        </template>
    </NotificationContent>
</template>
<script>
import NotificationContent from '../NotificationContent.vue';
export default {
    props: ["notificationItem"],
    components: { NotificationContent },
    methods:{
        getLinkData(){
           var name = '';
           switch (this.notificationItem.extra.subject_type) {
                case 'posts' :
                    name = 'post';
                    break;
           }

           return  {
                name : name,
                params : {
                    id: this.notificationItem.extra.subject_id,
                    comment_id : this.notificationItem.extra.comment_id,
                    reply_id : this.notificationItem.extra.reply_id
                }
            }
        }
    }
}
</script>