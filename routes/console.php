<?php

use Illuminate\Console\Scheduling\Schedule;

return function (Schedule $schedule) {
    $schedule->command('tasks:check-upcoming')->hourly();
};
