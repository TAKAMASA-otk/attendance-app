<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BreakTest extends TestCase
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

    public function test_休憩ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 0, 0));

        $user = User::factory()->create();
        $this->createWorkingAttendance($user);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance/break/start');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');

        $this->assertDatabaseHas('breaks', [
            'break_start' => '2026-05-28 12:00:00',
            'break_end' => null,
        ]);

        Carbon::setTestNow();
    }

    public function test_休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $this->createWorkingAttendance($user);

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 0, 0));
        $this->actingAs($user)->post('/attendance/break/start');

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 30, 0));
        $this->actingAs($user)->post('/attendance/break/end');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入');

        Carbon::setTestNow();
    }

    public function test_休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $this->createWorkingAttendance($user);

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 0, 0));
        $this->actingAs($user)->post('/attendance/break/start');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 30, 0));
        $this->actingAs($user)->post('/attendance/break/end');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');

        $this->assertDatabaseHas('breaks', [
            'break_start' => '2026-05-28 12:00:00',
            'break_end' => '2026-05-28 12:30:00',
        ]);

        Carbon::setTestNow();
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $this->createWorkingAttendance($user);

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 0, 0));
        $this->actingAs($user)->post('/attendance/break/start');

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 30, 0));
        $this->actingAs($user)->post('/attendance/break/end');

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 15, 0, 0));
        $this->actingAs($user)->post('/attendance/break/start');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        Carbon::setTestNow();
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $this->createWorkingAttendance($user);

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 0, 0));
        $this->actingAs($user)->post('/attendance/break/start');

        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 30, 0));
        $this->actingAs($user)->post('/attendance/break/end');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('05/28');
        $response->assertSee('00:30');

        Carbon::setTestNow();
    }
}