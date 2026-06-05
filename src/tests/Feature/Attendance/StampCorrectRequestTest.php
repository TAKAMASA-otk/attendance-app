<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StampCorrectionRequestTest extends TestCase
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

    public function test_出勤時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendanceId = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/' . $attendanceId . '/update', [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'reason' => '修正理由です',
        ]);

        $response->assertSessionHasErrors([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendanceId = $this->createAttendance($user);
        $this->createBreak($attendanceId);

        $response = $this->actingAs($user)->post('/attendance/' . $attendanceId . '/update', [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['19:00'],
            'break_end' => ['19:30'],
            'reason' => '修正理由です',
        ]);

        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendanceId = $this->createAttendance($user);
        $this->createBreak($attendanceId);

        $response = $this->actingAs($user)->post('/attendance/' . $attendanceId . '/update', [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['19:00'],
            'reason' => '修正理由です',
        ]);

        $response->assertSessionHasErrors([
            'break_end.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_備考欄が未入力の場合エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendanceId = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/' . $attendanceId . '/update', [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'reason' => '',
        ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

    public function test_修正申請処理が実行される()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $attendanceId = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/' . $attendanceId . '/update', [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'reason' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendanceId,
            'reason' => '電車遅延のため',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('電車遅延のため');
        $response->assertSee('承認待ち');
    }

    public function test_承認待ちにログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::factory()->create();
        $attendanceId = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/' . $attendanceId . '/update', [
            'start_time' => '09:30',
            'end_time' => '18:30',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'reason' => '打刻忘れ',
        ]);

        $response = $this->actingAs($user)->get('/requests?status=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('打刻忘れ');
    }

    public function test_承認済みに管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create();
        $attendanceId = $this->createAttendance($user);

        DB::table('stamp_correction_requests')->insert([
            'user_id' => $user->id,
            'attendance_id' => $attendanceId,
            'requested_clock_in' => '2026-05-28 09:30:00',
            'requested_clock_out' => '2026-05-28 18:30:00',
            'reason' => '承認済みテスト',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/requests?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認済みテスト');
    }

    public function test_各申請の詳細を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $attendanceId = $this->createAttendance($user);

        DB::table('stamp_correction_requests')->insert([
            'user_id' => $user->id,
            'attendance_id' => $attendanceId,
            'requested_clock_in' => '2026-05-28 09:30:00',
            'requested_clock_out' => '2026-05-28 18:30:00',
            'reason' => '詳細確認テスト',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendanceId);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}