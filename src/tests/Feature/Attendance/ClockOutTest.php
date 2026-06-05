<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    private function createWorkingAttendance($user)
    {
        return DB::table('attendances')->insertGetId([
            'user_id' => $user->id,
            'date' => '2026-05-28',
            'start_time' => '2026-05-28 09:00:00',
            'end_time' => null,
            'status' => 'working',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_退勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28, 18, 0, 0));

        $user = User::factory()->create();
        $this->createWorkingAttendance($user);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance/end');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2026-05-28',
            'start_time' => '2026-05-28 09:00:00',
            'end_time' => '2026-05-28 18:00:00',
            'status' => 'done',
        ]);

        Carbon::setTestNow();
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 9, 0, 0));
        $this->actingAs($user)->post('/attendance/start');

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 18, 0, 0));
        $this->actingAs($user)->post('/attendance/end');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('05/28');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}