<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private function createAttendance($user, $date, $start, $end)
    {
        DB::table('attendances')->insert([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'done',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $admin = User::factory()->create(['is_admin' => 1]);

        $user1 = User::factory()->create(['name' => '山田太郎']);
        $user2 = User::factory()->create(['name' => '佐藤花子']);

        $this->createAttendance(
            $user1,
            '2026-05-28',
            '2026-05-28 09:00:00',
            '2026-05-28 18:00:00'
        );

        $this->createAttendance(
            $user2,
            '2026-05-28',
            '2026-05-28 10:00:00',
            '2026-05-28 19:00:00'
        );

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('佐藤花子');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        Carbon::setTestNow();
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $admin = User::factory()->create(['is_admin' => 1]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('2026/05/28');

        Carbon::setTestNow();
    }

    public function test_前日を押下した時に前の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create(['name' => '前日ユーザー']);

        $this->createAttendance(
            $user,
            '2026-05-27',
            '2026-05-27 08:30:00',
            '2026-05-27 17:30:00'
        );

        $response = $this->actingAs($admin)->get('/admin?date=2026-05-27');

        $response->assertStatus(200);
        $response->assertSee('2026/05/27');
        $response->assertSee('前日ユーザー');
        $response->assertSee('08:30');
        $response->assertSee('17:30');

        Carbon::setTestNow();
    }

    public function test_翌日を押下した時に次の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28));

        $admin = User::factory()->create(['is_admin' => 1]);
        $user = User::factory()->create(['name' => '翌日ユーザー']);

        $this->createAttendance(
            $user,
            '2026-05-29',
            '2026-05-29 11:00:00',
            '2026-05-29 20:00:00'
        );

        $response = $this->actingAs($admin)->get('/admin?date=2026-05-29');

        $response->assertStatus(200);
        $response->assertSee('2026/05/29');
        $response->assertSee('翌日ユーザー');
        $response->assertSee('11:00');
        $response->assertSee('20:00');

        Carbon::setTestNow();
    }
}