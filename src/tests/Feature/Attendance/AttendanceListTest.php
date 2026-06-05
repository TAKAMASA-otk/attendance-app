<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function createAttendance($user, $date, $start, $end = null)
    {
        return DB::table('attendances')->insertGetId([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $end ? 'done' : 'working',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_自分が行った勤怠情報が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $user = User::factory()->create();

        $this->createAttendance(
            $user,
            '2026-05-10',
            '2026-05-10 09:00:00',
            '2026-05-10 18:00:00'
        );

        $this->createAttendance(
            $user,
            '2026-05-20',
            '2026-05-20 10:00:00',
            '2026-05-20 19:00:00'
        );

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('05/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('05/20');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        Carbon::setTestNow();
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026/05');

        Carbon::setTestNow();
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $user = User::factory()->create();

        $this->createAttendance(
            $user,
            '2026-04-15',
            '2026-04-15 09:30:00',
            '2026-04-15 18:30:00'
        );

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('2026/04');
        $response->assertSee('04/15');
        $response->assertSee('09:30');
        $response->assertSee('18:30');

        Carbon::setTestNow();
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $user = User::factory()->create();

        $this->createAttendance(
            $user,
            '2026-06-10',
            '2026-06-10 08:45:00',
            '2026-06-10 17:45:00'
        );

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-06');

        $response->assertStatus(200);
        $response->assertSee('2026/06');
        $response->assertSee('06/10');
        $response->assertSee('08:45');
        $response->assertSee('17:45');

        Carbon::setTestNow();
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $user = User::factory()->create();

        $attendanceId = $this->createAttendance(
            $user,
            '2026-05-28',
            '2026-05-28 09:00:00',
            '2026-05-28 18:00:00'
        );

        $response = $this->actingAs($user)->get('/attendance/' . $attendanceId);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('2026年');
        $response->assertSee('5月28日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}