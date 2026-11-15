<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || $user->isAdmin() || $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->role === 'customer' || $user->isStaff() || $user->isAdmin();
    }

    public function confirm(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return ($user->id === $booking->user_id && in_array($booking->status, ['pending', 'confirmed'], true))
            || $user->isAdmin()
            || $user->isStaff();
    }
}
