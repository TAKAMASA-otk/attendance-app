<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createAttendance($user)
    {
        return DB::table('attendances')->insertGetId([
            'user_id' => $user->id,
            'date' => '2026-05-28',
            'start_time' => '2026-05-28 09:00:00',
            'end_time' => '2026-05-28 18:00:00',
            'status' => 'done',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createBreak($attendanceId)
    {
        return DB::table('breaks')->insertGetId([
            'attendance_id' => $attendanceId,
            'break_start' => '2026-05-28 12:00:00',
            'break_end' => '2026-05-28 13:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create(['name' => '詳細確認ユーザー']);

        $attendanceId = $this->createAttendance($user);
        $this->createBreak($attendanceId);

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $attendanceId);

        $response->assertStatus(200);
        $response->assertSee('詳細確認ユーザー');
        $response->assertSee('2026年');
        $response->assertSee('5月28日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create();

        $attendanceId = $this->createAttendance($user);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $attendanceId, [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '管理者修正',
        ]);

        $response->assertSessionHasErrors([
            'end_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create();

        $attendanceId = $this->createAttendance($user);
        $this->createBreak($attendanceId);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $attendanceId, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['08:00'],
            'break_end' => ['09:30'],
            'note' => '管理者修正',
        ]);

        $response->assertSessionHasErrors();

        $this->assertTrue(
            session('errors')->has(0)
        );

        $this->assertEquals(
            '休憩時間が不適切な値です',
            session('errors')->first(0)
        );
    }

    public function test_休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create();

        $attendanceId = $this->createAttendance($user);
        $this->createBreak($attendanceId);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $attendanceId, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['17:30'],
            'break_end' => ['18:30'],
            'note' => '管理者修正',
        ]);

        $response->assertSessionHasErrors();

        $this->assertTrue(
            session('errors')->has(0)
        );

        $this->assertEquals(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first(0)
        );
    }

    public function test_備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create();

        $attendanceId = $this->createAttendance($user);

        $response = $this->actingAs($admin)->post('/admin/attendance/' . $attendanceId, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'note' => '',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}