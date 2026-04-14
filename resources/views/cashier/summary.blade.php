@extends('layouts.app')
@section('content')

        <div class="grid-1-2" style="grid-template-columns: 1fr 2fr;">
            <!-- Summary Generator Card -->
            <div class="card" style="align-self: start; border-top: 4px solid var(--primary-500);">
                <div class="card-header border-bottom">
                    <h3><i class="fas fa-file-contract text-primary" style="margin-right: 8px;"></i> Collection Summary</h3>
                </div>
                <div class="card-body" style="padding: 1.5rem;">
                    <p class="text-muted text-sm mb-4">Consolidate all verified collections for today and generate an official summary report.</p>
                    
                    <div class="form-group mb-3">
                        <label style="font-weight: 600; font-size: 0.85rem;">Select Report Date</label>
                        <input type="date" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                    </div>

                    <div style="background: var(--gray-50); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span class="text-muted text-sm">Total Remittances</span>
                            <span style="font-weight: 600;">18 Reports</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span class="text-muted text-sm">Direct Collections</span>
                            <span style="font-weight: 600;">₱18,500.00</span>
                        </div>
                        <hr style="margin: 8px 0; border-color: var(--gray-200);">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: 700; color: var(--gray-800);">Consolidated Total</span>
                            <span style="font-weight: 700; color: var(--primary-700);">₱56,700.00</span>
                        </div>
                    </div>

                    <button class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;" onclick="simulateReportGeneration('Daily Collection Summary', 'collection')">
                        <i class="fas fa-magic" style="margin-right: 8px;"></i> Generate Summary
                    </button>
                </div>
            </div>

            <!-- Breakdown by Department -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h3>Daily Revenue Breakdown</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; gap: 1rem;">
                        <!-- Dept 1: Fishport -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--primary-100); color: var(--primary-600); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-fish"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Fishport</div>
                                <div class="text-muted text-sm">4 remittances verified</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱20,200.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">35.6% of total</div>
                            </div>
                        </div>
                        <!-- Dept 2: Public Market -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--success-light); color: var(--success); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-store"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Public Market</div>
                                <div class="text-muted text-sm">5 remittances verified</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱15,000.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">26.5% of total</div>
                            </div>
                        </div>
                        <!-- Dept 3: Terminal -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--warning-light); color: var(--warning); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-bus"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Public Terminal</div>
                                <div class="text-muted text-sm">6 remittances verified</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱8,500.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">15.0% of total</div>
                            </div>
                        </div>
                        <!-- Dept 4: Cemetery -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--gray-200); color: var(--gray-700); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-cross"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Cemetery</div>
                                <div class="text-muted text-sm">Direct collections</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱3,000.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">5.3% of total</div>
                            </div>
                        </div>
                        <!-- Dept 5: Atrium Hall -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background: var(--gray-50); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: var(--info-light); color: var(--info); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fas fa-building"></i></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--gray-800);">Atrium Hall</div>
                                <div class="text-muted text-sm">Direct collections</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--gray-800);">₱10,000.00</div>
                                <div style="font-size: 0.75rem; color: var(--success); font-weight: 600;">17.6% of total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
@endsection