<template>
    <button
        ref="reactionButton"
        class="flex items-center justify-center gap-2"
        :class="[ isLiked ? 'text-base-red is-liked' : '', buttonActive ? 'active' : '' ]"
        @click.stop="handleToggleLike()"
    >
        <BaseIcon :name="isLiked ? 'heart_fill' : 'heart'" :size="size" />
        <slot name="text"></slot>
    </button>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useLikeStore } from '@/store/like';
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    props: {
        subject: {
            type: Object,
            default: null
        },
        subjectType: {
            type: String,
            default: 'posts'
        },
        size: {
            type: Number,
            default: 24
        },
        params: {
            type: Object,
            default: null
        },
    },
    components: {
        BaseIcon
    },
    data(){
        return {
            buttonActive: false,
            isLiked: !!(this.subject && this.subject.is_liked),
            likePayload: null,

            // debounce state
            debounceTimer: null,
            pendingAction: null,
            pendingInitialState: null,
            debounceDelay: 800
        }
    },
    computed: {
        ...mapState(useAuthStore, ['authenticated'])
    },
    watch: {
        'subject.is_liked'(val) {
            this.isLiked = !!val
            this.buttonActive = !!val
        }
    },
    methods: {
        ...mapActions(useLikeStore, ['toggleLike', 'updateLikeData']),
        handleToggleLike(){
            if (!this.authenticated) {
                return this.showRequireLogin()
            }

            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer)
                this.debounceTimer = null
            }

            const original = this.pendingInitialState !== null ? this.pendingInitialState : this.isLiked
            const newState = !this.isLiked

            this.isLiked = newState
            this.buttonActive = newState

            if (this.pendingInitialState === null) {
                this.pendingInitialState = original
            }
            this.pendingAction = newState ? 'add' : 'remove'

            this.likePayload = {
                subject_type: this.subjectType,
                subject_id: this.subject.id,
                action: this.pendingAction,
                ...this.params
            }

            this.updateLikeData(this.likePayload)

            this.debounceTimer = setTimeout(() => {
                this._flushToggleLike()
            }, this.debounceDelay)
        },

        async _flushToggleLike(){
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer)
                this.debounceTimer = null
            }

            if (!this.pendingAction) {
                this.pendingInitialState = null
                return
            }

            const finalState = this.isLiked

            if (this.pendingInitialState === finalState) {
                this.pendingAction = null
                this.pendingInitialState = null
                return
            }

            try {
                await this.toggleLike(this.likePayload)
            } catch (error) {
                this.isLiked = this.pendingInitialState
                this.buttonActive = this.pendingInitialState
                this.showError(error.error)
            } finally {
                this.pendingAction = null
                this.pendingInitialState = null
            }
        }
    },
    beforeUnmount() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer)
            this.debounceTimer = null
        }
    }
}
</script>