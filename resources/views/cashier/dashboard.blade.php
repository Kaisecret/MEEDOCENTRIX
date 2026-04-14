@extends('layouts.app')
@section('content')

            <div class="stats-grid">
                <div class="stat-card" style="border-top: 4px solid var(--warning-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning-600);"><i class="fas fa-inbox"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Pending Remittances</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">8</h2>
                        <span class="text-warning" style="font-weight: 500; font-size: 0.85rem;">Awaiting verification</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--success-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--success-light); color: var(--success);"><i class="fas fa-vault"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Verified Collections</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">₱45,200</h2>
                        <span class="text-success" style="font-weight: 500; font-size: 0.85rem;">Official daily total</span>
                    </div>
                </div>
                <div class="stat-card" style="border-top: 4px solid var(--info-500); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-file-contract"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Generated Reports</h3>
                        <h2 style="font-size: 1.8rem; margin: 0.25rem 0;">2</h2>
                        <span class="text-info" style="font-weight: 500; font-size: 0.85rem;">Daily summaries</span>
                    </div>
                </div>
            </div>
        

            <div class="dashboard-grid mt-4">
                <div class="card col-span-2" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header flex-between border-bottom">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Cashier Activity</h3>
                        <button class="btn btn-outline btn-sm" onclick="navigateTo('cashier_remittance')">View Pending</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Report ID</th>
                                        <th>Source / Collector</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#REM-9942</strong></td>
                                        <td>Roberto Gomez (Collector)</td>
                                        <td><strong>₱15,000.00</strong></td>
                                        <td><span class="badge bg-success-light text-success">Verified</span></td>
                                        <td>Today, 11:45 AM</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#REM-9941</strong></td>
                                        <td>Terminal Personnel</td>
                                        <td><strong>₱8,200.00</strong></td>
                                        <td><span class="badge bg-warning-light text-warning">Pending Review</span></td>
                                        <td>Today, 10:30 AM</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        

@endsection