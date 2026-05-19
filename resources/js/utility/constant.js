export default {
    MENTION: '@',
    HASHTAG: '#',
    HASHTAG_REGEX : /(?:#)([\p{L}\p{N}_](?:(?:[\p{L}\p{N}_]|(?:\.(?!\.))){0,128}(?:[\p{L}\p{N}_]))?)/gmu,
    MENTION_REGEX : /(?:@)([\p{L}\p{N}_](?:(?:[\p{L}\p{N}_]|(?:\.(?!\.))){0,128}(?:[\p{L}\p{N}_]))?)/gmu,
    LIMIT_CHARACTER: 200,
    CHANNEL_ROOM: 'Social.Chat.Models.ChatRoom.',
    CHANNEL_USER: 'Social.Core.Models.User.',
    USER_PRIVACY_FOLLOWER: 2,
    USER_PRIVACY_ONLYME: 3,
    CHAT_PRIVACY_FOLLOWER: 2,
    CHAT_PRIVACY_NOONE: 3,
    AVATAR_WIDTH: 800,
    AVATAR_HEIGHT: 800,
    COVER_WIDTH: 1024,
    COVER_HEIGHT: 393,
    STORY_WIDTH: 1080,
    STORY_HEIGHT: 1920,
    STORY_BACKGROUND: '#d1d5db',
    LINK_REGEX: /(https?:\/\/[^\s"]+)/gi,
    ENABLE_DARKMODE: true,
    RESPONSE_CODE_MEMBERSHIP_PERMISSION: 'membership_permission',
    AGE_TYPE: {
      ANY: 'any',
      RANGE: 'range'
    },
    GROUP_TYPE: {
      PUBLIC: 0,
      PRIVATE: 1 
    },
    IMAGE_MAX_WIDTH: 1024,
    IMAGE_MAX_HEIGHT: 1024
  }