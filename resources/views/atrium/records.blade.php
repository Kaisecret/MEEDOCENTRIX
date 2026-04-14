@extends('layouts.app')
@section('content')

        <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-700), var(--primary-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Reservation Records</h2>
                        <p style="opacity: 0.9; margin: 0;">Manage hall bookings, client details, and update reservation statuses.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddAtriumModal()"><i class="fas fa-plus"></i> New Booking</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Hall Bookings Registry</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search event or client..." onkeyup="filterAtriumRecords(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Event Name</th>
                                <th>Client Details</th>
                                <th>Schedule Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="atriumRecordsTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
@endsection