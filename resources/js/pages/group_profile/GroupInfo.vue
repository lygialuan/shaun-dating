<template>
    <div class="main-content-section">
        <div class="space-y-base-2">
            <h3 class="main-content-section-header-title">{{ $t('Info') }}</h3>
            <div v-if="description || isGroupAdmin">
                <div class="font-bold mb-base-2">{{ $t('Description') }}</div>
                <div class="flex items-start gap-base-2">
                    <template v-if="description">
                        <div class="flex-1"><ContentHtml :content="description" /></div>
                        <BaseButton v-if="isGroupAdmin" @click="handleEditDescription()" icon="pencil" type="secondary-outlined" size="sm" />
                    </template>
                    <button v-else class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditDescription()">{{$t('Add Description')}}</button>
                </div>
            </div>
            <div v-if="categories.length || isGroupAdmin">
                <div class="font-bold mb-base-2">{{ $t('Categories') }}</div>
                <div v-if="categories.length" class="flex flex-wrap items-start gap-2 mt-base-2">
                    <div class="boxes-list flex flex-wrap gap-1 flex-1">
                        <BoxItem v-for="category in categories" :key="category.id" :item="category" :to="{name: 'groups', params: { tab: 'all' }, query: {category_id: category.id}}" />
                    </div>
                    <BaseButton v-if="isGroupAdmin" @click="handleEditCategories()" icon="pencil" type="secondary-outlined" size="sm" />
                </div>
                <button v-else-if="categories.length === 0 && isGroupAdmin" class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditCategories()">{{$t('Add Categories')}}</button>
            </div>
            <div v-if="hashtags.length || isGroupAdmin">
                <div class="font-bold mb-base-2">{{ $t('Hashtags') }}</div>
                <div v-if="hashtags.length" class="flex flex-wrap items-start gap-2 mt-base-2">
                    <div class="boxes-list flex flex-wrap gap-1 flex-1">
                        <BoxItem v-for="hashtag in hashtags" :key="hashtag.name" :item="hashtag" :to="{name: 'search', params: {search_type: 'hashtag', type: 'group'}, query: {q: hashtag.name}}" />
                    </div>
                    <BaseButton v-if="isGroupAdmin" @click="handleEditHashtags()" icon="pencil" type="secondary-outlined" size="sm" />
                </div>
                <button v-else-if="hashtags.length === 0 && isGroupAdmin" class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditHashtags()">{{$t('Add Hashtags')}}</button>
            </div>
            <div>
                <div class="font-bold mb-base-2">{{ $t('Group Privacy') }}</div>
                <div class="flex flex-wrap gap-2 mt-base-2">
                    <div class="flex-1 flex items-center gap-base-1">
                        <BaseIcon :name="privacyIcon(type)"/>
                        <span>{{ typeText }}</span>
                    </div>
                    <BaseButton v-if="isGroupAdmin && type === constant.GROUP_TYPE.PUBLIC" @click="handleEditPrivacyGroup" icon="pencil" type="secondary-outlined" size="sm" />
                </div>
            </div>
        </div>
    </div>
    <div class="main-content-section">
        <div class="space-y-base-2">
            <h3 class="main-content-section-header-title">{{ $t('Activities') }}</h3>
            <div class="space-y-base-2">
                <div class="flex gap-base-2">
                    <BaseIcon name="message" :cursor="false"/>
                    <div>
                        <div class="font-medium">{{ $filters.numberShortener(groupInfo.statistics.post_recent_count, $t('[number] post recently'), $t('[number] posts recently')) }}</div>
                        <div class="text-base-xs leading-tight text-sub-color dark:text-slate-400">{{ $filters.numberShortener(groupInfo.statistics.post_this_month_count, $t('[number] post this month'), $t('[number] posts this month')) }}</div>
                    </div>
                </div>
                <div class="flex gap-base-2">
                    <BaseIcon name="users" :cursor="false"/>
                    <div>
                        <div class="font-medium">{{ $filters.numberShortener(groupInfo.member_count, $t('[number] total member'), $t('[number] total members')) }}</div>
                        <div class="text-base-xs leading-tight text-sub-color dark:text-slate-400">{{ $filters.numberShortener(groupInfo.statistics.member_last_week_count, $t('[number] member in the last week'), $t('[number] members in the last week')) }}</div>
                    </div>
                </div>
                <div class="flex gap-base-2">
                    <BaseIcon name="clock" :cursor="false"/>
                    <div class="font-medium">{{ $t('Created') + ' ' + groupInfo.created_at }}</div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="groupInfo.members.length" class="main-content-section">
        <div class="space-y-base-2">
            <h3 class="main-content-section-header-title">{{ $t('Members') + ' · ' + shortenNumber(groupInfo.member_count) }}</h3>
            <div class="flex gap-2">
                <Avatar v-for="member in groupInfo.members" :key="member.id" :user="member" :size="50"/>
            </div>
            <BaseButton v-if="groupInfo.canView" :to="{ name: 'group_profile', params: { id: groupInfo.id, slug: groupInfo.slug, tab: 'members' } }" type="outlined" fluid>
                {{ $t('View all') }}
            </BaseButton>
        </div>
    </div>
    <div v-if="groupRulesList.length || groupInfo.is_owner" class="main-content-section">
        <div class="space-y-base-2">
            <h3 class="main-content-section-header-title">{{ $t('Group rules') }}</h3>
            <template v-if="groupInfo.is_owner">
                <Draggable :list="groupRulesList" :disabled="isMobile" @change="handleOrderRule" class="space-y-base-2">
                    <div v-for="rule in groupRulesList" :key="rule.id" class="flex items-start gap-3" :class="{'cursor-move': !isMobile}">
                        <BaseIcon name="dots_six_vertical" v-if="!isMobile" />
                        <RuleItem :item="rule" class="flex-1"/>
                        <div class="flex gap-base-2">
                            <BaseButton @click="handleDeleteRule(rule.id)" icon="trash" type="danger-outlined" size="sm" />
                            <BaseButton @click="handleEditNewRule(rule)" icon="pencil" type="secondary-outlined" size="sm" />
                        </div>
                    </div>
                </Draggable>
                <BaseButton @click="handleCreateNewRule" fluid>{{ $t('Create New Rule') }}</BaseButton>
            </template>
            <template v-else>
                <RuleItem v-for="rule in groupRulesList" :key="rule.id" :item="rule" />
            </template>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { storeGroupRuleOrder, deleteGroupRule } from '@/api/group'
import { VueDraggableNext } from "vue-draggable-next"
import { useGroupStore } from '@/store/group'
import { useAppStore } from '@/store/app'
import Avatar from '@/components/user/Avatar.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import EditDescriptionModal from '@/components/modals/EditDescriptionModal.vue'
import SelectCategoriesModal from '@/components/modals/SelectCategoriesModal.vue'
import EditHashtagsModal from '@/components/modals/EditHashtagsModal.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import CreateRuleModal from  '@/components/modals/CreateRuleModal.vue'
import SelectPrivacyGroupModal from  '@/components/modals/SelectPrivacyGroupModal.vue'
import constant from '@/utility/constant';
import RuleItem from '@/components/lists/RuleItem.vue'
import BoxItem from '@/components/lists/BoxItem.vue'

export default {
    props: ['groupInfo'],
    components: { Avatar, BaseButton, BaseIcon, ContentHtml, Draggable: VueDraggableNext, RuleItem, BoxItem },
    data(){
        return {
            description: this.groupInfo.description,
            categories: this.groupInfo.categories,
            hashtags: this.groupInfo.hashtags,
            type: this.groupInfo.type,
            typeText: this.groupInfo.type_text,
            constant: constant
        }
    },
    computed:{
        ...mapState(useGroupStore, ['groupRulesList']),
        ...mapState(useAppStore, ['isMobile']),
        isGroupAdmin(){
            return this.groupInfo.is_admin
        }
    },
    methods:{
        ...mapActions(useGroupStore, ['handleGetGroupRules']),
        handleEditDescription(){
            this.$dialog.open(EditDescriptionModal, {
                data: {
                    descriptionData: { 
                        content: this.description,
                        subject_id: this.groupInfo.id,
						subject_type: 'group'
                    }
                },
                props:{
					header: this.$t('Edit Description'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.description = options.data.description
                    }
                }
            });
        },
        handleEditCategories(){
            this.$dialog.open(SelectCategoriesModal, {
                data: {
                    categoriesData: {
                        selected_category_ids: this.categories.map(category => category.id),
                        subject_id: this.groupInfo.id,
                        subject_type: 'group',
                        selected_type: 'save'
                    }
                },
                props: {
                    header: this.$t('Select Categories'),
                    class: 'select-categories-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: async(options) => {
                    if(options.data){
                        this.categories = options.data.selectedCategoriesList
                    }
                }
            })
        },
        handleEditHashtags(){
            this.$dialog.open(EditHashtagsModal, {
                data: {
                    hashtagsData: {
                        selectedHashtags: this.hashtags,
                        subject_id: this.groupInfo.id,
                        subject_type: 'group'
                    }
                },
                props: {
                    header: this.$t('Select Hashtags'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: async(options) => {
                    if(options.data){
                        this.hashtags = options.data.hashtags
                    }
                }
            })
        },
        privacyIcon(value){
            switch (value) {
                case 1:
                    return 'lock'
                default:
                    return 'earth'
            }
        },
        handleEditPrivacyGroup(){
            this.$dialog.open(SelectPrivacyGroupModal, {
                data: {
                    groupId: this.groupInfo.id,
                    groupType: this.type,
                },
                props:{
					header: this.$t('Select Type'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.type = options.data.type
                        this.typeText = this.$t('Private')
                    }
                }
            });
        },
        handleEditNewRule(rule){
            this.$dialog.open(CreateRuleModal, {
                data: {
                    id: rule.id,
                    groupId: this.groupInfo.id,
                    title: rule.title,
                    description: rule.description
                },
                props:{
					header: this.$t('Add Rule'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: () => {
                    this.handleGetGroupRules(this.groupInfo.id)
                }
            });
        },
        handleCreateNewRule(){
            this.$dialog.open(CreateRuleModal, {
                data: {
                    groupId: this.groupInfo.id
                },
                props:{
					header: this.$t('Add Rule'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: () => {
                    this.handleGetGroupRules(this.groupInfo.id)
                }
            });
        },
        async handleOrderRule(){
            try {
                await storeGroupRuleOrder({
                    group_id: this.groupInfo.id,
                    orders: this.groupRulesList.map(rule => rule.id)
                })
                this.handleGetGroupRules(this.groupInfo.id)
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleDeleteRule(ruleId){
            this.$confirm.require({
                message: this.$t('Do you want to remove this rule?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await deleteGroupRule(ruleId)
                        this.handleGetGroupRules(this.groupInfo.id)
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            });
        }
    }
}
</script>