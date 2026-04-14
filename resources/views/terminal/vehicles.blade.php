@extends('layouts.app')
@section('content')

        <div class="card mb-4" style="background: linear-gradient(135deg, var(--info-700), var(--info-500)); color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Terminal Vehicle Logs</h2>
                        <p style="opacity: 0.9; margin: 0;">Monitor and record arriving and departing buses, vans, and tricycles in the public terminal.</p>
                    </div>
                    <button class="btn btn-outline" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4);" onclick="openAddTerminalVehicleModal()"><i class="fas fa-plus"></i> Log Vehicle Entry</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between">
                <h3>Today's Activity Log</h3>
                <div class="table-search" style="margin: 0; min-width: 250px;">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search plate no. or driver..." onkeyup="filterTerminalVehicles(this.value)">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Log ID</th>
                                <th>Plate Number</th>
                                <th>Driver / Operator</th>
                                <th>Vehicle Type</th>
                                <th>Status</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="terminalVehiclesTableBody">
                            ${trHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
@endsection