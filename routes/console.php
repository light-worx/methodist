<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('methodist:preaching-reminders')->weekly()->mondays()->at('06:30')->withoutOverlapping();
