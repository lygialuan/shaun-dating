import { defineStore } from "pinia";
import {
    getCommentsByItemId,
    postNewComment,
    deleteComment,
    getRepliesByCommentId,
    postNewReply,
    deleteReply,
    getCommentSingle,
    editComment,
    editReply,
} from "../api/comment";

export const useCommentStore = defineStore("comment", {
    // convert to a function
    state: () => ({
        commentsList: [],
        commentInfo: null,
    }),
    actions: {
        doPushCommentsList({ response, page }) {
            if (page == 1) {
                this.commentsList = [];
            }
            let commentsList = window._.map(response.items, function (element) {
                return window._.extend({}, element, { replies: [] });
            });
            this.commentsList = window._.concat(
                this.commentsList,
                commentsList
            );
        },
        doAddComment(commentData) {
            if (window._.find(this.commentsList, { id: commentData.id })) {
                return;
            }
            commentData = window._.extend({}, commentData, { replies: [] });
            this.commentsList.unshift(commentData);
        },
        doToggleLikeComment(commentData) {
            var comment = null;
            if (
                this.commentInfo &&
                this.commentInfo.id === commentData.subject_id
            ) {
                comment = this.commentInfo;
            } else {
                comment = window._.find(this.commentsList, {
                    id: commentData.subject_id,
                });
            }
            if (comment) {
                if (commentData.action === "add") {
                    comment.is_liked = true;
                    comment.like_count++;
                } else if (commentData.action === "remove") {
                    comment.is_liked = false;
                    comment.like_count--;
                }
            }
        },
        doDeleteComment(commentId) {
            if (this.commentInfo && this.commentInfo.id == commentId) {
                this.commentInfo = null;
            } else {
                this.commentsList = this.commentsList.filter(
                    (comment) => comment.id != commentId
                );
            }
        },
        doPushRepliesList({ response, commentId, page }) {
            let comment = window._.find(this.commentsList, { id: commentId });
            if (this.commentInfo && this.commentInfo.id === commentId) {
                comment = this.commentInfo;
            }
            if (comment) {
                if (page == 1) {
                    comment.replies = [];
                }
                comment.replies = window._.concat(
                    response.items.reverse(),
                    comment.replies
                );
            }
        },
        hideRepliesList(commentId) {
            let comment = window._.find(this.commentsList, { id: commentId });
            if (this.commentInfo) {
                comment = this.commentInfo;
            }
            if (comment) {
                comment.replies = [];
            }
        },
        doAddReply(response, commentId) {
            let comment = window._.find(this.commentsList, { id: commentId });
            if (this.commentInfo && this.commentInfo.id === commentId) {
                comment = this.commentInfo;
            }
            if (comment) {
                if (window._.find(comment.replies, { id: response.id })) {
                    return;
                }
                comment.reply_count++;
                comment.replies.push(response);
            }
        },
        doToggleLikeReply(replyData) {
            const { comment_id, subject_id, action } = replyData;
			const isAdd = action === "add";

			const updateReply = (reply) => {
				reply.is_liked = isAdd;
				reply.like_count += isAdd ? 1 : -1;
			};

			let comment = null;

			if (this.commentInfo && this.commentInfo.id === comment_id) {
				comment = this.commentInfo;
			} else {
				comment = window._.find(this.commentsList, { id: comment_id });
			}

			if (!comment) return;

			const targetReply = comment.replies?.find(r => r.id === subject_id);
			if (targetReply) updateReply(targetReply);

			if (comment.reply && comment.reply.id === subject_id) {
				updateReply(comment.reply);
			}
        },
        doDeleteReply(replyId, commentId) {
            let comment = window._.find(this.commentsList, { id: commentId });
            if (this.commentInfo && this.commentInfo.id === commentId) {
                comment = this.commentInfo;
                if (comment.reply.id === replyId) {
                    comment.reply = null;
                }
            }
            if (comment) {
                comment.reply_count--;
                comment.replies = comment.replies.filter(
                    (reply) => reply.id !== replyId
                );
            }
        },
        doSetCommentSingle(commentInfo) {
            if (commentInfo) {
                this.commentInfo = window._.extend({}, commentInfo.comment, {
                    reply: null,
                    replies: [],
                });
                if (typeof commentInfo.reply === "undefined") {
                    commentInfo.reply = [];
                }
                this.commentInfo.reply = commentInfo.reply;
            }
        },
        resetCommentsData() {
            this.commentsList = [];
            this.commentInfo = null;
        },
        doUpdateComment(commentData) {
            let comment = window._.find(this.commentsList, {
                id: commentData.id,
            });
            if (this.commentInfo && this.commentInfo.id === commentData.id) {
                comment = this.commentInfo;
            }
            if (comment) {
                Object.assign(comment, commentData);
            }
        },
        doUpdateReply(replyData, commentId) {
            const updateReplies = (comment) => {
                if (!comment || !comment.replies) return;

                comment.replies.forEach(reply => {
                    if (reply.id === replyData.id) {
                        Object.assign(reply, replyData);
                    }
                });

                if (comment.reply && comment.reply.id === replyData.id) {
                    Object.assign(comment.reply, replyData);
                }
            };

            if (this.commentInfo && this.commentInfo.id === commentId) {
                updateReplies(this.commentInfo);
                return;
            }

            if (this.commentsList?.length) {
                const comment = window._.find(this.commentsList, { id: commentId });
                updateReplies(comment);
            }
        },
        async getCommentsListByItemId({ itemType, itemId, page }) {
            const response = await getCommentsByItemId(itemType, itemId, page);
            this.doPushCommentsList({ response, page });
            return response;
        },
        async postComment(commentData) {
            const response = await postNewComment(commentData);
            this.doAddComment(response);
            return response;
        },
        async deleteComment(data) {
            try {
                await deleteComment(data);
                this.doDeleteComment(data.id);
            } catch (error) {
                console.log(error);
            }
        },
        async getRepliesByCommentId({ commentId, page, reply_id }) {
            const response = await getRepliesByCommentId(commentId, page);
            // Reply Detail Page
            if (reply_id) {
                response.items = response.items.filter(
                    (item) => item.id != reply_id
                );
            }
            this.doPushRepliesList({ response, commentId, page });
            return response;
        },
        async postReply(replyData) {
            const response = await postNewReply(replyData);
            const commentId = replyData.comment_id;

            this.doAddReply(response, commentId);
        },
        async deleteReply(replyData) {
            try {
                await deleteReply(replyData);
                this.doDeleteReply(replyData.id, replyData.comment_id);
            } catch (error) {
                console.log(error);
            }
        },
        async getCommentSingleDetail({ type, itemId, commentId, replyId }) {
            const response = await getCommentSingle(
                type,
                itemId,
                commentId,
                replyId
            );
            this.doSetCommentSingle(response);
        },
        async editComment(commentData) {
            const response = await editComment(commentData);
            this.doUpdateComment(response);
        },
        async editReply(replyData) {
            const response = await editReply(replyData);
            const commentId = replyData.comment_id;
            this.doUpdateReply(response, commentId);
        },
		updateLike(likeData) {
			switch (likeData.subject_type) {
				case 'comments':
					this.doToggleLikeComment(likeData);
					break;
				case 'comment_replies':
					this.doToggleLikeReply(likeData);
					break;
				default:
					console.warn(`Unhandled like update for type: ${likeData.subject_type}`);
			}
		}
    },
    persist: false,
});
