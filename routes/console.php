<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reminders:preaching')->weeklyOn(1,'06:30')->withoutOverlapping();
