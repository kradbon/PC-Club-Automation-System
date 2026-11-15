<?php

namespace Tests\Feature;

use App\Models\ClubReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClubReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_approved_registered_customer_feedback_appears_publicly(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->post('/en/account/feedback', [
            'rating' => 5, 'body' => 'Friendly team and a great setup for our evening matches.',
        ])->assertRedirect()->assertSessionHas('success');
        $review = ClubReview::sole();
        $this->assertSame('pending', $review->status);
        $this->get('/en')->assertSee('No member feedback has been published yet.');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put('/en/admin/reviews/'.$review->id, ['status' => 'approved'])->assertRedirect();
        $this->assertDatabaseHas('club_reviews', ['id' => $review->id, 'status' => 'approved']);
        $this->actingAs($admin)->get('/en')->assertSee('Friendly team and a great setup')->assertSee('Verified club member');
    }
}
