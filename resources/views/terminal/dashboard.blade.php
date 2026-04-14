@extends('layouts.app')
@section('content')

            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid var(--info-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-bus"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Vehicles Logged Today</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">48</h2>
                        <span class="text-info" style="font-weight: 500; font-size: 0.85rem;"><i class="fas fa-arrow-up"></i> 12% vs yesterday</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--primary-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Terminal Fees Collected</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">₱12,450</h2>
                        <span class="text-primary" style="font-weight: 500; font-size: 0.85rem;">Estimated total</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--warning); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Pending for Payment</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">22</h2>
                        <span class="text-warning" style="font-weight: 500; font-size: 0.85rem;">Requires sending to collector</span>
                    </div>
                </div>
            </div>
        

            <div class="dashboard-grid mt-4">
                <div class="card col-span-2" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header flex-between border-bottom">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Terminal Activity</h3>
                        <button class="btn btn-outline btn-sm" onclick="navigateTo('terminal_records')">View All Ledger</button>
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
                                        <td><strong>#TRM-4042</strong></td>
                                        <td>Bus Terminal Fee - ABC-1234</td>
                                        <td><strong>₱250.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Collected</span></td>
                                        <td>Today, 11:15 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#TRM-4041</strong></td>
                                        <td>Van Terminal Fee - XYZ-9876</td>
                                        <td><strong>₱150.00</strong></td>
                                        <td><span class="badge bg-warning-light text-warning">Pending</span></td>
                                        <td>Today, 10:30 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#TRM-4040</strong></td>
                                        <td>Tricycle Fee - TR-001</td>
                                        <td><strong>₱20.00</strong></td>
                                        <td><span class="badge bg-info-light text-info">Sent</span></td>
                                        <td>Today, 09:45 AM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        

@endsection