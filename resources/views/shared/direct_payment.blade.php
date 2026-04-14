@extends('layouts.app')
@section('content')

        <div class="card mb-4" style="background: ${headerGradient}; color: white; border: none;">
            <div class="card-body" style="padding: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.5rem;">Direct Payment Collection</h2>
                        <p style="opacity: 0.9; margin: 0;">Officially receive and log walk-in payments. Transactions are instantly verified.</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); padding: 12px 20px; border-radius: var(--radius-md); text-align: center; border: 1px solid rgba(255,255,255,0.3);">
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">Total Collected Today</div>
                        <div style="font-size: 1.5rem; font-weight: bold;">₱15,000.00</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-1-2" style="grid-template-columns: 1fr 2fr;">
            <!-- Direct Collection Form -->
            <div class="card" style="align-self: start; background: white; box-shadow: var(--shadow-md); border-top: 4px solid ${themeColor};">
                <div class="card-header border-bottom">
                    <h3 style="color: var(--gray-800);"><i class="fas fa-file-invoice-dollar" style="margin-right: 8px; color: ${themeColor};"></i> New Transaction</h3>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    <p class="text-muted text-sm mb-4">Record payments collected directly by ${roleName}. This logs the transaction immediately as 'Collected' without requiring an assigned collector.</p>
                    
                    <form id="directPaymentForm" onsubmit="event.preventDefault(); processDirectPayment('${iconPrefix}');">
                        <div class="form-group mb-3">
                            <label style="font-weight: 600; color: var(--gray-700);">Client Name / Payee</label>
                            <input type="text" id="dpClient" class="form-control" placeholder="Enter name..." required>
                        </div>
                        <div class="form-group mb-3">
                            <label style="font-weight: 600; color: var(--gray-700);">Payment For (Description)</label>
                            <select id="dpDesc" class="form-control" required>
                                <option value="" disabled selected>Select service or fee...</option>
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label style="font-weight: 600; color: var(--gray-700);">Amount Received (₱)</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; color: var(--gray-500);">₱</span>
                                <input type="number" id="dpAmount" class="form-control" placeholder="0.00" step="0.01" required style="font-size: 1.2rem; font-weight: 600; padding-left: 30px;">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem; background: ${themeColor}; border-color: ${themeColor};">
                            <i class="fas fa-check-circle" style="margin-right: 8px;"></i> Process Payment
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Collections Feed -->
            <div class="card" style="border: none; box-shadow: var(--shadow-md);">
                <div class="card-header flex-between border-bottom">
                    <div>
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Collections Feed</h3>
                        <p class="text-muted text-sm" style="margin-top: 4px;">Payments recorded directly by you today.</p>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Payee</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody id="directPaymentFeed">
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 3rem;">
                                        <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                                        <p class="text-muted m-0">No direct payments processed yet today.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
@endsection