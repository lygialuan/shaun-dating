<template>
  <span class="base-icon inline-block align-middle">
    <component
      :is="name"
      :size="size.toString()"
      :class="svgClass">
    </component>
  </span>
</template>
    
<script>
import emoji from './emoji.vue'
import close from './close.vue'
import plus from './plus.vue'
import message from './message.vue'
import new_post_mobile from './new_post_mobile.vue'
import search from './search.vue'
import home from './home.vue'
import compass from './compass.vue'
import bookmarks from './bookmarks.vue'
import globe from './globe.vue'
import heart from './heart.vue'
import heart_fill from './heart_fill.vue'
import comment from './comment.vue'
import share from './share.vue'
import more_horiz_outlined from './more_horiz_outlined.vue'
import bookmark_fill from './bookmark_fill.vue'
import pencil from './pencil.vue'
import hashtag from './hashtag.vue'
import link_simple from './link_simple.vue'
import arrow_right from './arrow_right.vue'
import arrow_left from './arrow_left.vue'
import error from './error.vue'
import success from './success.vue'
import camera from './camera.vue'
import check from './check.vue'
import bell_slash from './bell_slash.vue'
import send_message from './send_message.vue'
import linkedin from './linkedin.vue'
import pinterest from './pinterest.vue'
import facebook from './facebook.vue'
import twitter from './twitter.vue'
import caret_down from './caret_down.vue'
import caret_left from './caret_left.vue'
import caret_right from './caret_right.vue'
import apple from './apple.vue'
import google from './google.vue'
import more_vertical from './more_vertical.vue'
import eye from './eye.vue'
import eye_slash from './eye_slash.vue'
import feeds from './feeds.vue'
import info from './info.vue'
import video_camera from './video_camera.vue'
import sun from './sun.vue'
import moon from './moon.vue'
import character from './character.vue'
import photo from './photo.vue'
import play from './play.vue'
import pause from './pause.vue'
import speaker_none from './speaker_none.vue'
import speaker_high from './speaker_high.vue'
import speaker_low from './speaker_low.vue'
import play_fill from './play_fill.vue'
import pause_fill from './pause_fill.vue'
import music_note from './music_note.vue'
import music_notes from './music_notes.vue'
import background from './background.vue'
import fullscreen from './fullscreen.vue'
import fullscreen_exit from './fullscreen_exit.vue'
import desktop from './desktop.vue'
import upload_simple from './upload_simple.vue'
import arrow_up from './arrow_up.vue'
import arrow_down from './arrow_down.vue'
import spinner from './spinner.vue'
import images from './images.vue'
import video_solid from './video_solid.vue'
import user_switch from './user_switch.vue'
import phone from './phone.vue'
import circle_switch from './circle_switch.vue'
import clock from './clock.vue'
import coin from './coin.vue'
import earth from './earth.vue'
import mail from './mail.vue'
import star from './star.vue'
import lock from './lock.vue'
import friend from './friend.vue'
import location from './location.vue'
import media from './media.vue'
import user from './user.vue'
import users from './users.vue'
import trash from './trash.vue'
import file from './file.vue'
import briefcase from './briefcase.vue'
import paperclip from './paperclip.vue'
import money from './money.vue'
import check_circle from './check_circle.vue'
import chats from './chats.vue'
import gif from './gif.vue'
import x_circle from './x_circle.vue'
import chart_pie_slice from './chart_pie_slice.vue'
import check_square from './check_square.vue'
import square from './square.vue'
import at from './at.vue'
import seal_check from './seal_check.vue'
import user_check from './user_check.vue'
import pip from './pip.vue'
import paper_plane_tilt from './paper_plane_tilt.vue'
import key from './key.vue'
import youtube_logo from './youtube_logo.vue'
import chart from './chart.vue'
import plus_circle from './plus_circle.vue'
import bell from './bell.vue'
import user_group from './user_group.vue'
import dots_six_vertical from './dots_six_vertical.vue'
import vibb from './vibb.vue'
import funnel from './funnel.vue'
import magnifying_glass_minus from './magnifying_glass_minus.vue'
import magnifying_glass_plus from './magnifying_glass_plus.vue'
import arrow_clockwise from './arrow_clockwise.vue'
import arrow_counter_clockwise from './arrow_counter_clockwise.vue'
import bounding_box from './bounding_box.vue'
import arrow_circle_left from './arrow_circle_left.vue'
import arrow_circle_right from './arrow_circle_right.vue'
import microphone from './microphone.vue'
import user_plus from './user_plus.vue'
import lock_open from './lock_open.vue'
import list_blocking from './list_blocking.vue'
import list_followers from './list_followers.vue'
import list_users from './list_users.vue'
import list_pages from './list_pages.vue'
import List_following from './list_following.vue'
import user_circle_plus from './user_circle_plus.vue'
import user_circle from './user_circle.vue'
import Note_pencil from './note_pencil.vue'
import Plus_circle_fill from './plus_circle_fill.vue'
import filter_data from './filter_data.vue'
import book_open from './book_open.vue'
import brief_case from './brief_case.vue'
import brief_case_two from './brief_case_two.vue'
import uncheck_square from './uncheck_square.vue'
import check_circle_fill from './check_circle_fill.vue'
import uncheck_circle from './uncheck_circle.vue'
import discover from './discover.vue'
import swipe from './swipe.vue'
import heart_not_color from './heart_not_color.vue'
import heart_black from './heart_black.vue'
import heart_yellow from './heart_yellow.vue'
import dots from './dots.vue'
import gift_black from './gift_black.vue'
import gift_color from './gift_color.vue'
import gift from './gift.vue'
import arrow_circle_left_nobg from './arrow_circle_left_nobg.vue'
import arrow_circle_right_nobg from './arrow_circle_right_nobg.vue'
import message_dating from './message_dating.vue'
import message_dating_black from './message_dating_black.vue'

export default {
  name: 'BaseIcon',
  props: {
    name: {
      type: String,
      default: ''
    },
    size: {
      type: [String, Number],
      default: 24
    },
    color: {
      type: String,
      default: 'currentColor'
    },
    svgClass: {
      type: String,
      default: ''
    }
  },

  components: {
    emoji,
    close,
    plus,
    message,
    new_post_mobile,
    search,
    home,
    compass,
    bookmarks,
    globe,
    heart,
    heart_fill,
    comment,
    share,
    more_horiz_outlined,
    bookmark_fill,
    pencil,
    hashtag,
    link_simple,
    more_vertical,
    arrow_right,
    arrow_left,
    error,
    success,
    camera,
    check,
    bell_slash,
    send_message,
    linkedin,
    pinterest,
    twitter,
    caret_down,
    caret_left,
    caret_right,
    apple,
    google,
    facebook,
    eye,
    eye_slash,
    feeds,
    info,
    video_camera,
    sun,
    moon,
    character,
    photo,
    play,
    pause,
    speaker_none,
    speaker_high,
    speaker_low,
    play_fill,
    pause_fill,
    music_note,
    music_notes,
    background,
    fullscreen,
    fullscreen_exit,
    desktop,
    upload_simple,
    arrow_up,
    arrow_down,
    spinner,
    images,
    video_solid,
    user_switch,
    phone,
    circle_switch,
    clock,
    coin,
    earth,
    mail,
    star,
    lock,
    friend,
    location,
    media,
    user,
    users,
    trash,
    file,
    briefcase,
    paperclip,
    money,
    check_circle,
    chats,
    gif,
    x_circle,
    chart_pie_slice,
    check_square,
    square,
    at,
    seal_check,
    user_check,
    pip,
    paper_plane_tilt,
    key,
    youtube_logo,
    chart,
    plus_circle,
    bell,
    user_group,
    dots_six_vertical,
    vibb,
    funnel,
    magnifying_glass_minus,
    magnifying_glass_plus,
    arrow_clockwise,
    arrow_counter_clockwise,
    bounding_box,
    arrow_circle_left,
    arrow_circle_right,
    microphone,
    user_plus,
    lock_open,
    list_blocking,
    list_followers,
    List_following,
    list_pages,
    list_users,
    user_circle_plus,
    user_circle,
    Note_pencil,
    Plus_circle_fill,
    filter_data,
    book_open,
    brief_case,
    brief_case_two,
    uncheck_square,
    uncheck_circle,
    check_circle_fill,
    discover,
    swipe,
    heart_not_color,
    heart_black,
    heart_yellow,
    dots,
    gift_black,
    gift_color,
    gift,
    arrow_circle_left_nobg,
    arrow_circle_right_nobg,
    message_dating,
    message_dating_black
  }
}
</script>