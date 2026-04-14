@extends("layouts.app")
@section("content")

        <div class="card">
            <div class="card-header flex-between">
                <div>
                    <h3>Master Transaction Ledger</h3>
                    <p class="text-muted text-sm" style="margin-top: 4px;">Centralized view of all records across departments.</p>
                </div>
                <button class="btn btn-primary" onclick="showToast('Exporting Ledger...', 'info')"><i class="fas fa-download"></i> Export Ledger</button>
            </div>
            <div class="tabs" style="margin-bottom: 0;">
                ${tabsHtml}
            </div>
            <div class="card-body p-0">
                <div class="table-controls">
                    <div class="table-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="ledgerSearch" onkeyup="applyLedgerFilters('${activeDept}')" placeholder="Search ID, details, personnel, or department...">
                    </div>
                    <div class="table-actions">
                        <select id="ledgerStatusFilter" class="form-control" style="padding: 8px 12px; height: 36px; font-size: 0.82rem;" onchange="applyLedgerFilters('${activeDept}')">
                            <option>All Status</option>
                            <option>Completed / Collected</option>
                            <option>Active / Pending</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Ref ID</th>
                                <th>Department</th>
                                <th>Description / Title</th>
                                <th>Personnel</th>
                                <th>Amount (₱)</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ledgerTableBody">
                            ${window.generateLedgerRows(displayRecords)}
                        </tbody>
                    </table>
                </div>
                <div class="table-pagination">
                    <span id="ledgerPaginationText">Showing ${displayRecords.length > 0 ? '1' : '0'} to ${displayRecords.length} of ${displayRecords.length} entries</span>
                    <div class="pagination-btns">
                        <button disabled><i class="fas fa-chevron-left"></i></button>
                        <button class="active">1</button>
                        <button disabled><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    
@endsection