@extends('layouts.app')
@section('content')

        <div class="card mb-4">
            <div class="card-header flex-between">
                <div>
                    <h3 style="margin-bottom: 0.5rem;"><i class="fas fa-calendar-alt text-primary-500" style="margin-right: 8px;"></i> Booking Calendar Schedule</h3>
                    <p style="opacity: 0.9; margin: 0; color: var(--gray-500); font-size: 0.85rem;">Chronological feed of all hall reservations.</p>
                </div>
                <button class="btn btn-primary btn-sm" onclick="navigateTo('atrium_bookings')">Manage Bookings</button>
            </div>
            <div class="card-body p-0">
                ${feedHtml}
            </div>
        </div>
    
@endsection
