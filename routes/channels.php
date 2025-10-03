<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public channel for station updates - anyone can listen
Broadcast::channel('stations-channel', function () {
    return true; // Public channel, no authentication required
});
