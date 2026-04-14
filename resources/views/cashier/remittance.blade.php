@extends('layouts.app')
@section('content')

        <div class="stats-grid mb-4">
             <div class="stat-card" style="background: var(--primary-900); color: white;">
                <div class="stat-icon text-white" style="background: rgba(255,255,255,0.2);"><i class="fas fa-vault"></i></div>
                <div class="stat-details">
                    <h3 style="color: var(--primary-100);">Total Verified Today</h3>
                    <h2 style="color: white;">₱84,200.00</h2>
                </div>
            </div>
            <div class="stat-card" style="border: 1px solid var(--warning);">
                <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);"><i class="fas fa-clock"></i></div>
                <div class="stat-details">
                    <h3>Pending Remittances</h3>
                    <h2>₱12,500.00</h2>
                    <span class="text-warning">3 collectors waiting</span>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header border-bottom">
                <h3>Incoming Remittances</h3>
                <p class="text-muted text-sm mt-1">Review and verify funds submitted by collectors and department personnel.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Collector / Personnel</th>
                                <th>Source Area</th>
                                <th>Declared Amount</th>
                                <th>Time Submitted</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Roberto Gomez</strong></td>
                                <td>Public Market</td>
                                <td><strong>₱5,500.00</strong></td>
                                <td>10:45 AM</td>
                                <td><span class="badge bg-warning-light text-warning">Awaiting Verification</span></td>
                                <td><button class="btn btn-primary btn-sm" onclick="showVerificationModal('Roberto Gomez', '5500.00', 'Public Market')">Verify Funds</button></td>
                            </tr>
                            <tr>
                                <td><strong>Luis Antonio</strong></td>
                                <td>Fishport</td>
                                <td><strong>₱4,000.00</strong></td>
                                <td>11:15 AM</td>
                                <td><span class="badge bg-warning-light text-warning">Awaiting Verification</span></td>
                                <td><button class="btn btn-primary btn-sm" onclick="showVerificationModal('Luis Antonio', '4000.00', 'Fishport')">Verify Funds</button></td>
                            </tr>
                            <tr>
                                <td><strong>Mario Lopez</strong></td>
                                <td>Terminal</td>
                                <td><strong>₱3,000.00</strong></td>
                                <td>11:30 AM</td>
                                <td><span class="badge bg-warning-light text-warning">Awaiting Verification</span></td>
                                <td><button class="btn btn-primary btn-sm" onclick="showVerificationModal('Mario Lopez', '3000.00', 'Terminal')">Verify Funds</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
@endsection