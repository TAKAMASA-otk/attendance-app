<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_出勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28, 9, 0, 0));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤');

        $this->actingAs($user)->post('/attendance/start');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2026-05-28',
            'start_time' => '2026-05-28 09:00:00',
            'status' => 'working',
        ]);

        Carbon::setTestNow();
    }

    public function test_出勤は一日一回のみできる()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28, 18, 0, 0));

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
        $response->assertDontSee('出勤');

        Carbon::setTestNow();
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28, 9, 0, 0));

        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/start');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('05/28');
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }
}