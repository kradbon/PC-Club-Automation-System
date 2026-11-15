<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClubReviewRequest;
use App\Models\ClubReview;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreClubReviewRequest $request, string $locale, ActivityLogger $activity): RedirectResponse
    {
        $review = ClubReview::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'rating' => $request->validated('rating'),
                'body' => $request->validated('body'),
                'status' => 'pending',
                'moderated_by' => null,
                'submitted_at' => now(),
                'moderated_at' => null,
            ],
        );
        $activity->record($request->user(), 'review.submitted', $review, ['rating' => $review->rating]);

        return back()->with('success', __('ui.review_submitted'));
    }
}
