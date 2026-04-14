@extends('layouts.app')
@section('content')

        <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-800), var(--primary-600)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Official Collection Vault</h2>
                        <p style="opacity: 0.9; margin: 0;">Master record of all verified and confirmed remittances for the current fiscal period.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);" onclick="showToast('Exporting collection logs...', 'info')"><i class="fas fa-file-export"></i> Export Logs</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between border-bottom">
                <h3>Verified Transaction History</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search reference or personnel...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>OR Number</th>
                                <th>Source / Collector</th>
                                <th>Department</th>
                                <th>Amount (₱)</th>
                                <th>Date Verified</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>#OR-2026-0042</strong></td>
                                <td>Roberto Gomez</td>
                                <td>Public Market</td>
                                <td><strong>₱15,000.00</strong></td>
                                <td>Today, 09:15 AM</td>
                                <td><span class="badge bg-success-light text-success"><i class="fas fa-check-circle"></i> Verified</span></td>
                            </tr>
                            <tr>
                                <td><strong>#OR-2026-0041</strong></td>
                                <td>Clara Recto</td>
                                <td>Atrium Hall</td>
                                <td><strong>₱10,000.00</strong></td>
                                <td>Today, 08:45 AM</td>
                                <td><span class="badge bg-success-light text-success"><i class="fas fa-check-circle"></i> Verified</span></td>
                            </tr>
                            <tr>
                                <td><strong>#OR-2026-0040</strong></td>
                                <td>Luis Antonio</td>
                                <td>Fishport</td>
                                <td><strong>₱20,200.00</strong></td>
                                <td>Yesterday, 04:30 PM</td>
                                <td><span class="badge bg-success-light text-success"><i class="fas fa-check-circle"></i> Verified</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
@endsection