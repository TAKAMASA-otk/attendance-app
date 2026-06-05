<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_勤務外の場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');

        Carbon::setTestNow();
    }

    public function test_出勤中の場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $user = User::factory()->create();

        DB::table('attendances')->insert([
            'user_id' => $user->id,
            'date' => '2026-05-28',
            'start_time' => '2026-05-28 09:00:00',
            'end_time' => null,
            'status' => 'working',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_休憩中の場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $user = User::factory()->create();

        $attendanceId = DB::table('attendances')->insertGetId([
            'user_id' => $user->id,
            'date' => '2026-05-28',
            'start_time' => '2026-05-28 09:00:00',
            'end_time' => null,
            'status' => 'working',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('breaks')->insert([
            'attendance_id' => $attendanceId,
            'break_start' => '2026-05-28 12:00:00',
            'break_end' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    public function test_退勤済の場合勤怠ステータスが正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $user = User::factory()->create();

        DB::table('attendances')->insert([
            'user_id' => $user->id,
            'date' => '2026-05-28',
            'start_time' => '2026-05-28 09:00:00',
            'end_time' => '2026-05-28 18:00:00',
            'status' => 'done',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }
}