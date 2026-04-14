@extends('layouts.app')
@section('content')

        <div class="card">
            <div class="card-header flex-between">
                <h3>Records & Transactions</h3>
                <div class="header-actions" style="display: flex;">
                    
                    <button class="btn btn-primary"><i class="fas fa-plus"></i> Add New</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr><th>ID</th><th>Title/Details</th><th>Amount (₱)</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" style="text-align: center; padding: 2rem;">No records found</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
@endsection