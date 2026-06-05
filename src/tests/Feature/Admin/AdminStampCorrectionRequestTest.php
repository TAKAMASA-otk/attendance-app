<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminStampCorrectionRequestTest extends TestCase
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

    private function createCorrectionRequest($user, $attendanceId, $status, $reason)
    {
        return DB::table('stamp_correction_requests')->insertGetId([
            'user_id' => $user->id,
            'attendance_id' => $attendanceId,
            'requested_clock_in' => '2026-05-28 09:30:00',
            'requested_clock_out' => '2026-05-28 18:30:00',
            'reason' => $reason,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_承認待ちの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['is_admin' => 1]);

        $user1 = User::factory()->create(['name' => '申請者A']);
        $user2 = User::factory()->create(['name' => '申請者B']);

        $attendanceId1 = $this->createAttendance($user1);
        $attendanceId2 = $this->createAttendance($user2);

        $this->createCorrectionRequest($user1, $attendanceId1, 'pending', '承認待ちA');
        $this->createCorrectionRequest($user2, $attendanceId2, 'pending', '承認待ちB');

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee('申請者A');
        $response->assertSee('承認待ちA');
        $response->assertSee('申請者B');
        $response->assertSee('承認待ちB');
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        $admin = User::factory()->create(['is_admin' => 1]);

        $user1 = User::factory()->create(['name' => '承認済みA']);
        $user2 = User::factory()->create(['name' => '承認済みB']);

        $attendanceId1 = $this->createAttendance($user1);
        $attendanceId2 = $this->createAttendance($user2);

        $this->createCorrectionRequest($user1, $attendanceId1, 'approved', '承認済み理由A');
        $this->createCorrectionRequest($user2, $attendanceId2, 'approved', '承認済み理由B');

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済みA');
        $response->assertSee('承認済み理由A');
        $response->assertSee('承認済みB');
        $response->assertSee('承認済み理由B');
    }

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create(['name' => '詳細申請ユーザー']);

        $attendanceId = $this->createAttendance($user);

        $correctionId = $this->createCorrectionRequest(
            $user,
            $attendanceId,
            'pending',
            '詳細確認理由'
        );

        DB::table('correction_break_times')->insert([
            'correction_id' => $correctionId,
            'break_start' => '2026-05-28 12:30:00',
            'break_end' => '2026-05-28 13:30:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/admin/stamp_correction_request/' . $correctionId);

        $response->assertStatus(200);
        $response->assertSee('詳細申請ユーザー');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('12:30');
        $response->assertSee('13:30');
        $response->assertSee('詳細確認理由');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create();

        $attendanceId = $this->createAttendance($user);

        $correctionId = $this->createCorrectionRequest(
            $user,
            $attendanceId,
            'pending',
            '承認処理テスト'
        );

        DB::table('correction_break_times')->insert([
            'correction_id' => $correctionId,
            'break_start' => '2026-05-28 12:30:00',
            'break_end' => '2026-05-28 13:30:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post('/admin/admin/stamp_correction_request/approve/' . $correctionId);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $correctionId,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendanceId,
            'start_time' => '2026-05-28 09:30:00',
            'end_time' => '2026-05-28 18:30:00',
        ]);

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendanceId,
            'break_start' => '2026-05-28 12:30:00',
            'break_end' => '2026-05-28 13:30:00',
        ]);
    }
}