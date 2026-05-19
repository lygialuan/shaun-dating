import { defineStore } from "pinia";
import { toggleLikeItem } from "@/api/likes";
import { usePostStore } from '@/store/post'
import { useCommentStore } from '@/store/comment';
import { useChatStore } from '@/store/chat';

export const useLikeStore = defineStore("like", {
    actions: {
        async toggleLike(data) {
            const response = await toggleLikeItem(data);
            return response;
        },
        updateLikeData(data) {
            const postStore = usePostStore();
            const commentStore = useCommentStore();
            const chatStore = useChatStore();

            switch (data.subject_type) {
                case 'posts':
                    postStore.updateLike(data);
                    break;
                case 'comments':
                case 'comment_replies':
                    commentStore.updateLike(data);
                    break;
                case 'chat_messages':
                    chatStore.updateLike(data);
                    break;
                default:
                    console.warn(`Store updateLike() method not found for type: ${data.subject_type}`);
            }
        }
    }
});
