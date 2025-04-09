<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; // Directly extend MongoDB Model
use Illuminate\Notifications\Notifiable;

class MongoDBNotification extends Model
{
    use Notifiable; // Enable notifications

    protected $connection = 'mongodb';
    protected $collection = 'notifications'; // Ensure it matches your MongoDB collection
}
