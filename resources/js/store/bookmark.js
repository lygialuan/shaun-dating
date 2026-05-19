import { defineStore } from "pinia";
import { toggleBookmarkItem } from "@/api/bookmark";
import { usePostStore } from '@/store/post'

export const useBookmarkStore = defineStore("bookmark", {
    actions: {
        async toggleBookmark(data) {
            const response = await toggleBookmarkItem(data);
            this.updateBookmarkData(data);
            return response;
        },
        updateBookmarkData(data) {
            const postStore = usePostStore();

            switch (data.subject_type) {
                case 'posts':
                    postStore.updateBookmark(data);
                    break;
                default:
                    console.warn(`Store updateBookmark() method not found for type: ${data.subject_type}`);
            }
        }
    }
});
