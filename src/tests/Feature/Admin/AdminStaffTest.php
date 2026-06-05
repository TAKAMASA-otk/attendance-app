<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    private function createAttendance($user, $date, $start, $end)
    {
        return DB::table('attendances')->insertGetId([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'done',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_管理者ユーザーが全一般ユーザーの氏名メールアドレスを確認できる()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/staff');

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');

        $response->assertSee('佐藤花子');
        $response->assertSee('sato@example.com');
    }

    public function test_ユーザーの勤怠情報が正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'name' => '勤怠ユーザー',
        ]);

        $attendanceId = $this->createAttendance(
            $user,
            '2026-05-28',
            '2026-05-28 09:00:00',
            '2026-05-28 18:00:00'
        );

        $response = $this->actingAs($admin)
            ->get('/admin/staff/' . $user->id . '/attendance');

        $response->assertStatus(200);

        $response->assertSee('勤怠ユーザー');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $this->createAttendance(
            $user,
            '2026-04-15',
            '2026-04-15 09:00:00',
            '2026-04-15 18:00:00'
        );

        $response = $this->actingAs($admin)
            ->get('/admin/staff/' . $user->id . '/attendance?month=2026-04');

        $response->assertStatus(200);

        $response->assertSee('2026/04');
        $response->assertSee('09:00');
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $this->createAttendance(
            $user,
            '2026-06-15',
            '2026-06-15 10:00:00',
            '2026-06-15 19:00:00'
        );

        $response = $this->actingAs($admin)
            ->get('/admin/staff/' . $user->id . '/attendance?month=2026-06');

        $response->assertStatus(200);

        $response->assertSee('2026/06');
        $response->assertSee('10:00');
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create();

        $attendanceId = $this->createAttendance(
            $user,
            '2026-05-28',
            '2026-05-28 09:00:00',
            '2026-05-28 18:00:00'
        );

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/' . $attendanceId);

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}