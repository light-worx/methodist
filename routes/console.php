<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('methodist:preaching-reminders')->weekly()->mondays()->at('09:00')->withoutOverlapping();
