<?php

use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// ✅ Add the "consultations" channel
Broadcast::channel('consultations', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->first_name . ' ' . $user->last_name,
        'role' => $user->role
    ];
});



Broadcast::channel('consultations.{consultationId}', function ($user, $consultationId) {
    return [
        'id' => $user->id,
        'name' => $user->first_name . ' ' . $user->last_name,
        'role' => $user->role
    ];
});