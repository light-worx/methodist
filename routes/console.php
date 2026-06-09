<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('methodist:preaching-reminders')->weekly()->tuesdays()->at('16:30')->withoutOverlapping();
