<?php

use Illuminate\Support\Facades\Broadcast;
use Packages\ShaunSocial\Chat\Models\ChatRoom;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('Social.Core.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('Social.Chat.Models.ChatRoom.{id}', function ($user, $id) {
    $room = ChatRoom::findByField('id', $id);
    return $room && $room->canView($user->id);
});