<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Message;
use App\Models\BroadcastMessage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-delete chat messages older than 45 days
Schedule::call(function () {
    Message::where('created_at', '<', now()->subDays(45))->delete();
})->daily()->description('Delete chat messages older than 45 days');

// Auto-delete broadcast messages older than 7 days
Schedule::call(function () {
    BroadcastMessage::where('created_at', '<', now()->subDays(7))->delete();
})->daily()->description('Delete broadcast messages older than 7 days');
