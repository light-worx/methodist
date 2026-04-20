<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('methodist:preaching-reminders')->weekly()->tuesdays()->at('09:00')->withoutOverlapping();
