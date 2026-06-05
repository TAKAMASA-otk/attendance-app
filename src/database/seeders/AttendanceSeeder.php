<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('is_admin', 0)->get();

        foreach ($users as $user) {
            for ($i = 1; $i <= 10; $i++) {
                $date = Carbon::today()->subDays($i);

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => $date->copy()->setTime(9, 0),
                    'end_time' => $date->copy()->setTime(18, 0),
                    'status' => 'done',
                ]);

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $date->copy()->setTime(12, 0),
                    'break_end' => $date->copy()->setTime(13, 0),
                ]);
            }
        }
    }
}