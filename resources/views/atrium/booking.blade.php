@extends('layouts.app')
@section('content')

        <div class="card">
            <div class="card-header flex-between">
                <h3>Atrium Hall Booking Calendar</h3>
                <button class="btn btn-primary" onclick="openModal('New Booking', 'Enter client details and schedule.')"><i class="fas fa-calendar-plus"></i> New Booking</button>
            </div>
            <div class="card-body">
                <div class="calendar-wrapper" style="min-height: 400px; display: flex; align-items: center; justify-content: center; background: var(--gray-50); border-radius: var(--radius-md); border: 1px solid var(--gray-200);">
                    <div style="text-align: center;">
                        <i class="fas fa-calendar-alt fa-3x mb-3" style="color: var(--gray-300);"></i>
                        <h4 style="color: var(--gray-600);">Calendar View Component</h4>
                        <p class="text-muted text-sm">Interactive calendar will be rendered here.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Upcoming Reservations</h3>
            </div>
            <div class="card-body p-0">
                 <table class="data-table">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Event Type</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>San Juan High School</td>
                            <td>Graduation Ball</td>
                            <td>Mar 25, 2026</td>
                            <td><span class="badge bg-success-light text-success">Confirmed</span></td>
                            <td><span class="badge bg-success-light text-success">Paid</span></td>
                        </tr>
                        <tr>
                            <td>Reyes Family</td>
                            <td>Wedding Reception</td>
                            <td>Apr 02, 2026</td>
                            <td><span class="badge bg-warning-light text-warning">Tentative</span></td>
                            <td><span class="badge bg-warning-light text-warning">Downpayment</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    
@endsection