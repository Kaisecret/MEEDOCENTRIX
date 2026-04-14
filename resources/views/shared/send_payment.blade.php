@extends('layouts.app')
@section('content')

        <div class="grid-1-2" style="grid-template-columns: 1fr 3fr;">
            <!-- Summary Card -->
            <div class="card" style="align-self: start;">
                <div class="card-header">
                    <h3>Summary</h3>
                </div>
                <div class="card-body" style="text-align: center; padding: 2rem;">
                    <div style="width: 64px; height: 64px; background: var(--warning-light); color: var(--warning); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem auto;">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h4 style="color: var(--gray-500); font-weight: 500; font-size: 0.9rem;">Total Pending Amount</h4>
                    <h2 style="font-size: 2.2rem; color: var(--gray-800); margin: 0.5rem 0;">₱${totalAmount.toFixed(2)}</h2>
                    <p class="text-muted text-sm mb-4">${pendingRecords.length} transaction(s) waiting</p>

                    <button class="btn btn-success" style="width: 100%; justify-content: center;" onclick="openSelectCollectorModal('all')" ${pendingRecords.length === 0 ? 'disabled' : ''}>
                        <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Send All to Collector
                    </button>
                </div>
            </div>

            <!-- List Card -->
            <div class="card">
                <div class="card-header flex-between">
                    <div>
                        <h3>Pending Transactions</h3>
                        <p class="text-muted text-sm" style="margin-top: 4px;">Transactions that need to be forwarded to assigned collectors.</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Description</th>
                                    <th>Date Recorded</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${trHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
@endsection