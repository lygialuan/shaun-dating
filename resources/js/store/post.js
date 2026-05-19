import { defineStore } from 'pinia'
import { getHomeFeeds, postFeed, deletePost, getUserFeeds, getPostById, getDiscoveryFeeds, getWatchFeeds, editPost, getMediaFeeds, getProfileMediaFeeds, changeContentWarningPost, getDocumentFeeds, storeStopComment, togglePinProfile, togglePinHomePage, getPostsListByIds } from '../api/posts'
import { getVibbsForYou, getFollowingVibbs, getProfileVibbs, getMyVibbs } from '../api/vibb'
import { getReviewsPage, storeReviewPage, searchPagePosts, getPagePostsWithHashtag } from '../api/page'
import { toggleEnableNotification } from '../api/notification'
import { getSearchResults } from '../api/search';
import { getBookmarkItem } from '../api/bookmark';
import { getExploreGroupFeeds, getYourGroupFeeds, getGroupPosts, getGroupMedias, getPostsWithHashtag, searchGroupPosts, togglePinPost } from '../api/group'
import { useGroupStore } from '@/store/group';
import { storeSubscribeUser, storePaidPost, getMyPaidPostsList, getProfilePaidPostsList, editPaidPost } from '../api/paid_content'

export const usePostStore = defineStore('post', {
    // convert to a function
    state: () => ({
        loadingPostsList: true,
        postsList: [],
        postInfo: null,
        currentPostPage: '',
        loadingVibbsList: true,
        vibbsList: [],
        loadingVibbInfo: true,
        vibbInfo: null,
        deletedPost: null,
        pinnedPostFlag: true,
        searchPost: {
            keyword: '',
            type: ''
        }
    }),
    actions: {
        doPushPostsList({postsList, page}){
            if (page == 1) {
                this.postsList = [];
            }
            let pushedPostsList = window._.map(postsList, function(postsListItem) {
                if(postsListItem.parent){
                    postsListItem.parent = {...postsListItem.parent, ...{showContentWarning: true}}
                }
                return window._.extend({}, postsListItem, {commentsList: [], showContentWarning: true});
            });
            this.postsList = window._.concat(this.postsList, pushedPostsList)
        },
        doPushVibbsList({vibbsList, page}){
            if (page == 1) {
                this.vibbsList = [];
            }
            let pushedVibbsList = window._.map(vibbsList, function(vibbsListItem) { 
                return window._.extend({}, vibbsListItem, {commentsList: [], showContentWarning: true});
            });
            this.vibbsList = window._.concat(this.vibbsList, pushedVibbsList)
        },
        doAddPost(response, postFrom = '') {
            if (response.id) {
                if (window._.find(this.postsList, {id: response.id})) {
                    return;
                }
                
                response = { ...response, commentsList: [], showContentWarning: true };
        
                // Check if post is being added on the Home/Profile page or Group page
                const isPostFromHomeOrProfilePage = !postFrom && ['home', 'profile'].includes(this.currentPostPage);
                const isPostFromGroupPage = postFrom === 'groups' && this.currentPostPage === 'group';
                const isPostFromReview = postFrom === 'review' && this.currentPostPage === 'review';
        
                if (isPostFromHomeOrProfilePage || isPostFromGroupPage || isPostFromReview) {
                    this.postsList.unshift(response);
                }
            }
            if(response.pending && postFrom === 'groups') {
                const groupInfo = useGroupStore().groupInfo;
                groupInfo.user_post_pending_count++
            }
        },
        doDeletePostItem(postData){
            if(this.postsList.length && window._.find(this.postsList, {id: postData.id})){
                this.postsList = this.postsList.filter(post => post.id !== postData.id)
            }
            if(this.postInfo && this.postInfo.id === postData.id){
                this.postInfo = null
            } 
            if(this.vibbsList.length && window._.find(this.vibbsList, {id: postData.id})){
                this.vibbsList = this.vibbsList.filter(vibb => vibb.id !== postData.id)
            }
            if(this.vibbInfo && this.vibbInfo.id === postData.id){
                this.vibbInfo = null
            }
            this.deletedPost = postData
        },
        doToggleEnableNotification(postData){
            const toggleEnableNotification = (item) => {
                if(item){
                    if(item.id == postData.subject_id){
                        if(postData.action === 'add'){
                            item.enable_notify = true;
                        }else if(postData.action === 'remove'){
                            item.enable_notify = false;
                        }      
                    }
                }
            }

            if(this.postsList.length){
                let post = this.postsList.find(post => post.id === postData.subject_id);
                toggleEnableNotification(post)
            }

            if(this.postInfo){
                let post = this.postInfo;
                toggleEnableNotification(post)
            }

            if(this.vibbsList.length){
                let vibb = this.vibbsList.find(vibb => vibb.id === postData.subject_id);
                toggleEnableNotification(vibb)
            }

            if(this.vibbInfo){
                let vibb = this.vibbInfo;
                toggleEnableNotification(vibb)
            }
        },
        updateLike(postData){
            const toggleLikePost = (item) => {
                if (!item) return;

                if(postData.action === 'add'){
                    item.is_liked = true;
                    item.like_count++;
                }else if(postData.action === 'remove'){
                    item.is_liked = false;
                    item.like_count--;
                }      
            }

            if(this.postsList.length){
                let post = this.postsList.find(post => post.id === postData.subject_id);
                toggleLikePost(post)
            }

            if(this.postInfo && this.postInfo.id === postData.subject_id){
                let post = this.postInfo;
                toggleLikePost(post)
            }

            if(this.vibbsList.length){
                let vibb = this.vibbsList.find(vibb => vibb.id === postData.subject_id);
                toggleLikePost(vibb)
            }

            if(this.vibbInfo && this.vibbInfo.id === postData.subject_id){
                let vibb = this.vibbInfo;
                toggleLikePost(vibb)
            }
        },
        updateBookmark(postData){
            const toggleBookmarkItem = (item) => {
                if (!item) return;

                if(postData.action === 'add'){
                    item.is_bookmarked = true;
                }else if(postData.action === 'remove'){
                    item.is_bookmarked = false;
                }  
            }

            if(this.postsList.length){
                let post = this.postsList.find(post => post.id === postData.subject_id);
                toggleBookmarkItem(post)
            }

            if(this.postInfo && this.postInfo.id === postData.subject_id){
                let post = this.postInfo;
                toggleBookmarkItem(post)
            }

            if(this.vibbsList.length){
                let vibb = this.vibbsList.find(vibb => vibb.id === postData.subject_id);
                toggleBookmarkItem(vibb)
            }

            if(this.vibbInfo && this.vibbInfo.id === postData.subject_id){
                let vibb = this.vibbInfo;
                toggleBookmarkItem(vibb)
            }
        },
        doTogglePinPostItem(postData){
            const togglePinItem = (item) => {
                if (!item) return;

                if(postData.action === 'pin'){
                    item.canPin = false;
                    item.canUnPin = true;
                    item.is_pin = true;
                    this.pinnedPostFlag = !this.pinnedPostFlag
                }else if(postData.action === 'unpin'){
                    item.canPin = true;
                    item.canUnPin = false;
                    item.is_pin = false;
                    this.pinnedPostFlag = !this.pinnedPostFlag
                }   
            }

            if(this.postsList.length){
                let post = this.postsList.find(post => post.id === postData.post_id);
                togglePinItem(post)
            }

            if(this.postInfo && this.postInfo.id === postData.post_id){
                let post = this.postInfo;
                togglePinItem(post)
            }

            if(this.vibbsList.length){
                let vibb = this.vibbsList.find(vibb => vibb.id === postData.post_id);
                togglePinItem(vibb)
            }

            if(this.vibbInfo && this.vibbInfo.id === postData.post_id){
                let vibb = this.vibbInfo;
                togglePinItem(vibb)
            }
        },
        doUpdatePost(postData, dataEdited = 'content'){
            const updateItem = (item) => {
                if (!item) return;

                Object.assign(item, postData);
                if(dataEdited == 'content'){
                    item.isEdited = true
                }
            }

            if(this.postsList.length){
                let post = this.postsList.find(post => post.id === postData.id);
                updateItem(post)
            }

            if(this.postInfo && this.postInfo.id === postData.id){
                let post = this.postInfo;
                updateItem(post)
            }

            if(this.vibbsList.length){
                let vibb = this.vibbsList.find(vibb => vibb.id === postData.id);
                updateItem(vibb)
            }

            if(this.vibbInfo && this.vibbInfo.id === postData.id){
                let vibb = this.vibbInfo;
                updateItem(vibb)
            }
        },
        unsetPostsList(){
            this.postsList = [];
            this.loadingPostsList = true
        },
        unsetVibbsList(){
            this.vibbsList = [];
            this.loadingVibbsList = true
        },
        unsetPostInfo(){
            this.postInfo = null
        },
        unsetVibbInfo(){
            this.vibbInfo = null
        },
        increaseCommentCount(postId){
            const increaseCommentCount = (item) => {
                if (!item) return;

                item.comment_count++;
            };

            if (this.postsList.length) {
                const post = this.postsList.find(post => post.id === postId);
                increaseCommentCount(post);
            }

            if (this.postInfo && this.postInfo.id === postId) {
                increaseCommentCount(this.postInfo);
            }

            if (this.vibbsList.length) {
                const vibb = this.vibbsList.find(vibb => vibb.id === postId);
                increaseCommentCount(vibb);
            }

            if (this.vibbInfo && this.vibbInfo.id === postId) {
                increaseCommentCount(this.vibbInfo);
            }
        },
        decreaseCommentCount(postId, commentCountToDecrease = 0){
            const decreaseCommentCount = (item) => {
                if (!item) return;

                item.comment_count = item.comment_count - commentCountToDecrease - 1;
            };

            if (this.postsList.length) {
                const post = this.postsList.find(post => post.id === postId);
                decreaseCommentCount(post);
            }

            if (this.postInfo && this.postInfo.id === postId) {
                decreaseCommentCount(this.postInfo);
            }

            if (this.vibbsList.length) {
                const vibb = this.vibbsList.find(vibb => vibb.id === postId);
                decreaseCommentCount(vibb);
            }

            if (this.vibbInfo && this.vibbInfo.id === postId) {
                decreaseCommentCount(this.vibbInfo);
            }
        },
        async getHomePostsList(page){
            try {
				const response = await getHomeFeeds(page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getUserPostsList(userId, page){
            try {
                const response = await getUserFeeds(userId, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getSearchPostsList(search_type, query, type, page){
            try {
                const response = await getSearchResults(search_type, query, type, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getDiscoveryPostsList(page){
            try {
				const response = await getDiscoveryFeeds(page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getWatchPostsList(page){
            try {
				const response = await getWatchFeeds(page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getBookmarkedPostsList(page){
            try {
				const response = await getBookmarkItem('posts', page)
                this.doPushPostsList({postsList: response.items, page})
                return response.items
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getExploreGroupPostsList(page){
            try {
				const response = await getExploreGroupFeeds(page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getYourGroupPostsList(page){
            try {
				const response = await getYourGroupFeeds(page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getGroupPostsList(groupId, page){
            try {
				const response = await getGroupPosts(groupId, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getGroupMediasList(groupId, page){
            try {
				const response = await getGroupMedias(groupId, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getGroupPostsListWithHashtag(groupId, hashtag, page){
            try {
				const response = await getPostsWithHashtag(groupId, hashtag, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getSearchGroupPosts(groupId, keyword, page){
            try {
				const response = await searchGroupPosts(groupId, keyword, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getVibbsForYouList(page){
            try {
                if(page === 1){
                    this.loadingVibbsList = true
                }
				const response = await getVibbsForYou(page)
                this.doPushVibbsList({vibbsList: response, page})
                return response
			} catch (error) {
				console.log(error) 
			} finally {
                this.loadingVibbsList = false
            }
        },
        async getFollowingVibbsList(page){
            try {
                if(page === 1){
                    this.loadingVibbsList = true
                }
				const response = await getFollowingVibbs(page)
                this.doPushVibbsList({vibbsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingVibbsList = false
            }
        },
        async getUserVibbsList(userId, page){
            try {
                const response = await getProfileVibbs(userId, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getUserVibbsModalList(userId, page){
            try {
                const response = await getProfileVibbs(userId, page)
                this.doPushVibbsList({vibbsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingVibbsList = false
            }
        },
        async getMyVibbsList(page){
            try {
                const response = await getMyVibbs(page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getMyVibbsModalList(page){
            try {
                const response = await getMyVibbs(page)
                this.doPushVibbsList({vibbsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingVibbsList = false
            }
        },
        async postNewFeed(newPost, postFrom){
            const response = await postFeed(newPost)
            this.doAddPost(response, postFrom)
            return response
        },
        async deletePostItem(postData){
            try {
				await deletePost(postData)
				this.doDeletePostItem(postData)
			} catch (error) {
				console.log(error)
			}
        },
        async toggleEnableNotificationPostItem(postData){
            try {
				await toggleEnableNotification(postData)
				this.doToggleEnableNotification(postData)
			} catch (error) {
				console.log(error)
			}
        },
        async togglePinPostItem(postData){
            const response = await togglePinPost(postData)
            this.doTogglePinPostItem(postData)
            return response
        },
        async getPostById(postId){
            const response = await getPostById(postId)
            this.postInfo = { ...response, showContentWarning: true }
            return response
        },
        async getVibbById(postId){
            try {
                const response = await getPostById(postId)
                this.vibbInfo = { ...response, showContentWarning: true }
                return response
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingVibbInfo = false
            }
        },
        async editPost(postData){
            const response = await editPost(postData)
            this.doUpdatePost(response)
        },
        setCurrentPostPage(page = ''){
            this.currentPostPage = page
        },
        async getMediaPostsList(page){
            try {
				const response = await getMediaFeeds(page)
                this.doPushPostsList({postsList: response, page})
                this.loadingPostsList = false
                return response
			} catch (error) {
                this.loadingPostsList = false
			}
        },
        async getProfileMediaPostsList(userId, page){
            try {
				const response = await getProfileMediaFeeds(userId, page)
                this.doPushPostsList({postsList: response, page})
                this.loadingPostsList = false
                return response
			} catch (error) {
                this.loadingPostsList = false
			}
        },
        async getReviewsPageList(pageId, page){
            try {
				const response = await getReviewsPage(pageId, page)
                this.doPushPostsList({postsList: response.items, page})
                this.loadingPostsList = false
                return response.items
			} catch (error) {
				console.log(error)
                this.loadingPostsList = false
			}
        },
        async postNewReviewFeed(newPost){
            const response = await storeReviewPage(newPost)
            this.doAddPost(response, 'review')
            return response
        },
        async changeContentWarningPost(postData){
            const response = await changeContentWarningPost(postData)
            this.doUpdatePost(response, 'content_warning_categories')
        },
        async getDocumentPostsList(page){
            try {
				const response = await getDocumentFeeds(page)
                this.doPushPostsList({postsList: response, page})
                this.loadingPostsList = false
                return response
			} catch (error) {
                this.loadingPostsList = false
			}
        },
        doToggleShowContentWarning(postData){
            const toggleShowContentWarning = (item) => {
                if (!item) return;

                item.showContentWarning = postData.action;
            }

            if(this.postsList.length){
                let post = this.postsList.find(post => post.id === postData.subject_id);
                toggleShowContentWarning(post)
            }

            if(this.postInfo && this.postInfo.id === postData.subject_id){
                let post = this.postInfo;
                toggleShowContentWarning(post)
            }

            if(this.vibbsList.length){
                let vibb = this.vibbsList.find(vibb => vibb.id === postData.subject_id);
                toggleShowContentWarning(vibb)
            }

            if(this.vibbInfo && this.vibbInfo.id === postData.subject_id){
                let vibb = this.vibbInfo;
                toggleShowContentWarning(vibb)
            }

            const sharedPosts = this.postsList.filter(post => post.parent && post.parent.id === postData.subject_id);
            sharedPosts.forEach(sharedPost => {
                if(sharedPost.parent){
                    toggleShowContentWarning(sharedPost.parent)
                }
            })
        },
        async togglePostCommenting(data){
            const response = await storeStopComment(data)
            this.doTogglePostCommenting(data)
            return response
        },
        doTogglePostCommenting(data){
            const togglePostCommenting = (item) => {
                if (!item) return;

                if(data.stop){
                    item.stop_comment = 1;
                }else{
                    item.stop_comment = 0;
                }
            }

            if(this.postsList.length){
                let post = this.postsList.find(post => post.id === data.id);
                togglePostCommenting(post)
            }

            if(this.postInfo && this.postInfo.id === data.id){
                let post = this.postInfo;
                togglePostCommenting(post)
            }

            if(this.vibbsList.length){
                let vibb = this.vibbsList.find(vibb => vibb.id === data.id);
                togglePostCommenting(vibb)
            }

            if(this.vibbInfo && this.vibbInfo.id === data.id){
                let vibb = this.vibbInfo;
                togglePostCommenting(vibb)
            }
        },
        doTogglePinProfile(data){
            const togglePinProfile = (item) => {
                if (!item) return;

                if(data.action === 'pin'){
                    item.canPinProfile = false;
                    item.canUnPinProfile = true;
                    if(this.currentPostPage === 'profile'){
                        item.is_pin = true;
                    }
                }else if(data.action === 'unpin'){
                    item.canPinProfile = true;
                    item.canUnPinProfile = false;
                    if(this.currentPostPage === 'profile'){
                        item.is_pin = false;
                    }
                }     
            }

            if(this.postsList.length){
                let post = this.postsList.find(post => post.id === data.id);
                togglePinProfile(post)
            }

            if(this.postInfo && this.postInfo.id === data.id){
                let post = this.postInfo;
                togglePinProfile(post)
            }

            if(this.vibbsList.length){
                let vibb = this.vibbsList.find(vibb => vibb.id === data.id);
                togglePinProfile(vibb)
            }

            if(this.vibbInfo && this.vibbInfo.id === data.id){
                let vibb = this.vibbInfo;
                togglePinProfile(vibb)
            }
        },
        async togglePinProfile(data){
            const response = await togglePinProfile(data)
            this.doTogglePinProfile(data)
            return response
        },
        doTogglePinHomePage(data){
            const togglePinHomePage = (item) => {
                if (!item) return;

                if(data.action === 'pin'){
                    item.canPinHome = false;
                    item.canUnPinHome = true;
                    if(this.currentPostPage === 'home'){
                        item.is_pin = true;
                    }
                }else if(data.action === 'unpin'){
                    item.canPinHome = true;
                    item.canUnPinHome = false;
                    if(this.currentPostPage === 'home'){
                        item.is_pin = false;
                    }
                }    
            }

            if(this.postsList.length){
                let post = this.postsList.find(post => post.id === data.id);
                togglePinHomePage(post)
            }

            if(this.postInfo && this.postInfo.id === data.id){
                let post = this.postInfo;
                togglePinHomePage(post)
            }

            if(this.vibbsList.length){
                let vibb = this.vibbsList.find(vibb => vibb.id === data.id);
                togglePinHomePage(vibb)
            }

            if(this.vibbInfo && this.vibbInfo.id === data.id){
                let vibb = this.vibbInfo;
                togglePinHomePage(vibb)
            }
        },
        async togglePinHomePage(data){
            const response = await togglePinHomePage(data)
            this.doTogglePinHomePage(data)
            return response
        },
        async getPostsByIds(postIds){
            const response = await getPostsListByIds(postIds)
            return response
        },
        updatePostsInLists(updatedPosts) {
            const updateList = (list) => {
                if (Array.isArray(list)) {
                    list.forEach((item, index) => {
                        const updatedPost = updatedPosts.find((post) => post.id === item.id);
                        if (updatedPost) {
                            list[index] = { ...item, ...updatedPost };
                        }
                    });
                } else if (list && list.id) {
                    const updatedPost = updatedPosts.find((post) => post.id === list.id);
                    if (updatedPost) {
                        Object.assign(list, { ...updatedPost });
                    }
                }
            };
        
            updateList(this.postsList);
            updateList(this.vibbsList);
            updateList(this.postInfo);
            updateList(this.vibbInfo);
        },
        async showPaidContentByUserId(userId) {
            try {
                const postIds = [
                    ...this.postsList.filter((post) => post.user.id === userId).map((post) => post.id),
                    ...this.vibbsList.filter((vibb) => vibb.user.id === userId).map((vibb) => vibb.id),
                    ...(this.postInfo && this.postInfo.user.id === userId ? [this.postInfo.id] : []),
                    ...(this.vibbInfo && this.vibbInfo.user.id === userId ? [this.vibbInfo.id] : []),
                ];
        
                if (!postIds.length) return;
        
                const updatedPosts = await this.getPostsByIds(postIds);
        
                this.updatePostsInLists(updatedPosts);

            } catch (error) {
                console.error(error);
            }
        },
        async showPaidContentByPostId(postId) {
            try {
                if (!postId) return;
        
                const updatedPosts = await this.getPostsByIds([postId]);
        
                this.updatePostsInLists(updatedPosts);

            } catch (error) {
                console.error(error);
            }
        },
        async storeSubscribeUser(data){
            await storeSubscribeUser(data)
            this.showPaidContentByUserId(data.user.id)
        },
        async storePaidPost(data){
            await storePaidPost(data)
            this.showPaidContentByPostId(data.postId)
        },
        async editPaidPost(data){
            await editPaidPost(data)
            this.showPaidContentByPostId(data.id)
        },
        async getMyPaidPostsList(page){
            try {
				const response = await getMyPaidPostsList(page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getProfilePaidPostsList(userId, page){
            try {
                const response = await getProfilePaidPostsList(userId, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getSearchPagePosts(pageId, keyword, page){
            try {
				const response = await searchPagePosts(pageId, keyword, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        async getPagePostsListWithHashtag(pageId, hashtag, page){
            try {
				const response = await getPagePostsWithHashtag(pageId, hashtag, page)
                this.doPushPostsList({postsList: response, page})
                return response
			} catch (error) {
				console.log(error)
			} finally {
                this.loadingPostsList = false
            }
        },
        setSearchPost({ keyword = '', type = '' }) {
            this.searchPost.keyword = keyword
            this.searchPost.type = type
        },
        resetSearchPost() {
            this.searchPost.keyword = ''
            this.searchPost.type = ''
        }
    },
    persist: false
  })