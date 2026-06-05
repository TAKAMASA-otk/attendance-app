<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_現在の日時情報がuiと同じ形式で出力されている()
    {
        Carbon::setTestNow(Carbon::create(2026, 5, 28, 12, 34, 56));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee('2026年5月28日');
        $response->assertSee('(木)');
        $response->assertSee('id="clock"', false);

        Carbon::setTestNow();
    }
}