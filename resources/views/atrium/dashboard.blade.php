@extends('layouts.app')
@section('content')

            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid var(--info-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Upcoming Bookings</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">14</h2>
                        <span class="text-info" style="font-weight: 500; font-size: 0.85rem;">Next 30 days</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--primary-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-users"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Active Clients</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">120</h2>
                        <span class="text-primary" style="font-weight: 500; font-size: 0.85rem;">Registered profiles</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--success); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-hand-holding-dollar"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Collections Today</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">₱15,000</h2>
                        <span class="text-success" style="font-weight: 500; font-size: 0.85rem;">Direct payments</span>
                    </div>
                </div>
            </div>
        

            <div class="dashboard-grid mt-4">
                <div class="card col-span-2" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header flex-between border-bottom">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Atrium Activity</h3>
                        <button class="btn btn-outline btn-sm" onclick="navigateTo('atrium_records')">View All Ledger</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Ref ID</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#ATR-5042</strong></td>
                                        <td>Hall Rental (Full) - DEPEd Seminar</td>
                                        <td><strong>₱10,000.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Today, 10:15 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#ATR-5041</strong></td>
                                        <td>Sound System Rental - Wedding Reception</td>
                                        <td><strong>₱1,500.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Today, 09:30 AM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        

@endsection