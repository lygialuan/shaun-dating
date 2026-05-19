<template>
    <div class="feed-polls px-base-2 md:px-4 mb-base-2">
        <div class="feed-polls-list flex flex-col gap-base-2">
            <div v-for="pollItem in pollsToShow" :key="pollItem.id" 
                class="feed-polls-list-item bg-web-wash rounded-md relative overflow-hidden dark:bg-slate-500" 
                :class="[
                    canVote ? 'cursor-pointer' : 'cursor-default opacity-60',
                    pollItem.is_voted ? 'is_voted' : ''
                ]"
                @click="toggleVote(poll.id, pollItem.id, pollItem.is_voted ? 'remove' : 'add')"
                role="button">
                <div class="flex items-center gap-base-2 p-3 font-semibold leading-none relative z-10">
                    <BaseIcon :name="pollItem.is_voted ? 'check_square' : 'square'" />
                    <div class="flex-1 leading-5">{{ pollItem.name }}</div>
                    <button v-if="canSeeResult" @click.stop="showVotes(pollItem.id)">{{ pollItem.percent + '%' }}</button>
                </div>
                <div v-if="canSeeResult" class="feed-polls-list-item-selected absolute inset-0 h-full w-full transition duration-500 origin-start bg-primary-color dark:bg-dark-primary-color" :style="{ transform: `scaleX(${pollItem.percent / 100})` }"></div>
            </div>
            <BaseButton v-if="isShowAllButtonVisible" @click="showAllPolls">{{ $t('See All') + ' (' + remainingPollsCount + ')' }}</BaseButton>
        </div>
        <div class="feed-polls-sub-text text-sm text-sub-color mt-2 dark:text-slate-400">
            {{ $filters.numberShortener(poll.vote_count, $t('[number] vote'), $t('[number] votes')) }}
            <span>·</span>
            {{ poll.left_time || $t('Final results') }} 
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { votePoll } from '@/api/posts'
import { checkPopupBodyClass } from '@/utility/index'
import VotesModal from '@/components/modals/VotesModal.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
const MAX_VISIBLE_POLLS = 5

export default {
    components: { BaseButton, BaseIcon },
    props: {
        post: {
            type: Object,
            default: null
        },
        parentPost: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            shortenedPollsList: this.post.items[0].subject.poll_items.slice(0, MAX_VISIBLE_POLLS)
        };
    },
    computed: {
        ...mapState(useAuthStore, ['authenticated']),
        poll() {
            return this.post.items[0].subject;
        },
        canVote(){
            return this.poll.canVote;
        },
        fullPollsList() {
            return this.poll.poll_items;
        },
        pollsToShow() {
            return this.shortenedPollsList;
        },
        isShowAllButtonVisible() {
            return this.pollsToShow.length === MAX_VISIBLE_POLLS && this.fullPollsList.length > MAX_VISIBLE_POLLS;
        },
        remainingPollsCount() {
            return this.fullPollsList.length - MAX_VISIBLE_POLLS;
        },
        canSeeResult() {
            return this.fullPollsList.some((pollItem) => pollItem.is_voted) || !this.poll.left_time;
        }
    },
    watch:{
        post: {
            handler: function() {
                this.showAllPolls()
            },
            deep: true
        }
    },
    methods: {
        async toggleVote(pollId, pollItemId, action){
            if(this.authenticated){
                if(! this.canVote) return
                try {
                    const response = await votePoll(pollId, pollItemId, action);
                    this.poll.poll_items = response.poll_items
                    this.poll.vote_count = response.vote_count
                } catch (error) {
                    this.showError(error.error)
                }
            } else {
                this.showRequireLogin()
            }
        },
        showVotes(pollItemId) {
            this.$dialog.open(VotesModal, {
                data: { pollItemId },
                props: {
                    header: this.$t('Votes'),
                    class: 'vote-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: checkPopupBodyClass
            });
        },
        showAllPolls() {
            this.shortenedPollsList = this.fullPollsList;
        }
    }
}
</script>
