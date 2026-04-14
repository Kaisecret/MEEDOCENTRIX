@extends('layouts.app')
@section('content')

            ${filterHtml}
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--primary-100); color: var(--primary-600);"><i class="fas fa-coins"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Total Revenue</h3>
                        <h2 id="statRevenue" style="font-size: 1.8rem; margin: 0.25rem 0; transition: opacity 0.3s;">₱124,500.00</h2>
                        <span id="statRevenueChange" class="text-success" style="font-weight: 500; font-size: 0.85rem; transition: opacity 0.3s;"><i class="fas fa-arrow-up"></i> 12% vs yesterday</span>
                    </div>
                </div>
                <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);"><i class="fas fa-clock"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Pending Remittances</h3>
                        <h2 id="statPending" style="font-size: 1.8rem; margin: 0.25rem 0; transition: opacity 0.3s;">15</h2>
                        <span id="statPendingDesc" class="text-warning" style="font-weight: 500; font-size: 0.85rem; transition: opacity 0.3s;">Needs review</span>
                    </div>
                </div>
                <div class="stat-card" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="stat-icon" style="background: var(--info-light); color: var(--info);"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-details">
                        <h3 style="font-size: 0.9rem; color: var(--gray-500);">Total Transactions</h3>
                        <h2 id="statTransactions" style="font-size: 1.8rem; margin: 0.25rem 0; transition: opacity 0.3s;">1,284</h2>
                        <span id="statTransactionsChange" class="text-success" style="font-weight: 500; font-size: 0.85rem; transition: opacity 0.3s;"><i class="fas fa-arrow-up"></i> 5% vs yesterday</span>
                    </div>
                </div>
            </div>
        

            <div class="dashboard-grid mt-4">
                <div class="card col-span-2">
                    <div class="card-header">
                        <h3>Revenue by Department</h3>
                        <button class="btn btn-icon"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="280"></canvas>
                    </div>
                </div>
                <div class="card" style="border: none; box-shadow: var(--shadow-md);">
                    <div class="card-header border-bottom flex-between">
                        <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Activity</h3>
                        <button class="btn btn-icon btn-sm text-muted"><i class="fas fa-filter"></i></button>
                    </div>
                    <div class="card-body" style="padding: 1.5rem;">
                        <div class="activity-timeline" style="position: relative; padding-left: 24px;">
                            <!-- Line connecting items -->
                            <div style="position: absolute; top: 10px; bottom: 10px; left: 11px; width: 2px; background: var(--gray-200); z-index: 0;"></div>
                            
                            <div class="timeline-item" style="position: relative; margin-bottom: 1.5rem; z-index: 1;">
                                <div style="position: absolute; left: -24px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--success-light); border: 2px solid white; display: flex; align-items: center; justify-content: center; color: var(--success); font-size: 0.7rem; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--success-light);">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="timeline-content" style="background: var(--gray-50); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--gray-100);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray-800);"><strong>Elena Marquez</strong> verified remittance</p>
                                        <span class="time" style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">10m ago</span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-600);"><span class="badge bg-success-light text-success" style="font-size: 0.7rem; padding: 2px 6px; margin-right: 6px;">Cashier</span> Amount: <strong>₱45,000.00</strong></div>
                                </div>
                            </div>
                            
                            <div class="timeline-item" style="position: relative; margin-bottom: 1.5rem; z-index: 1;">
                                <div style="position: absolute; left: -24px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--primary-100); border: 2px solid white; display: flex; align-items: center; justify-content: center; color: var(--primary-600); font-size: 0.7rem; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--primary-100);">
                                    <i class="fas fa-ship"></i>
                                </div>
                                <div class="timeline-content" style="background: var(--gray-50); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--gray-100);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray-800);"><strong>Juan Dela Cruz</strong> logged arrival</p>
                                        <span class="time" style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">25m ago</span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-600);">Vessel <em>MV San Juan</em> docked at Pier 3.</div>
                                </div>
                            </div>

                            <div class="timeline-item" style="position: relative; margin-bottom: 1.5rem; z-index: 1;">
                                <div style="position: absolute; left: -24px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--warning-light); border: 2px solid white; display: flex; align-items: center; justify-content: center; color: var(--warning); font-size: 0.7rem; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--warning-light);">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div class="timeline-content" style="background: var(--gray-50); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--gray-100);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray-800);"><strong>Maria Santos</strong> flagged stall</p>
                                        <span class="time" style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">1h ago</span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-600);">Stall #142 marked for maintenance.</div>
                                </div>
                            </div>
                            
                            <div class="timeline-item" style="position: relative; z-index: 1;">
                                <div style="position: absolute; left: -24px; top: 0; width: 24px; height: 24px; border-radius: 50%; background: var(--gray-200); border: 2px solid white; display: flex; align-items: center; justify-content: center; color: var(--gray-600); font-size: 0.7rem; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--gray-200);">
                                    <i class="fas fa-server"></i>
                                </div>
                                <div class="timeline-content" style="background: var(--gray-50); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--gray-100);">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                        <p style="margin: 0; font-size: 0.9rem; color: var(--gray-800);"><strong>System</strong> auto-backup</p>
                                        <span class="time" style="font-size: 0.75rem; color: var(--gray-500); font-weight: 500;">2h ago</span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-600);">Daily database backup completed successfully.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        

            <div class="card mt-4" style="border: none; box-shadow: var(--shadow-md);">
                <div class="card-header flex-between border-bottom">
                    <h3 style="font-size: 1.1rem; color: var(--gray-800);">Recent Transactions</h3>
                    <button class="btn btn-outline btn-sm" onclick="navigateTo('transactions')">View All Ledger</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Department</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>#TRX-9982</strong></td>
                                    <td><span class="badge bg-primary-100 text-primary-700">Fishport</span></td>
                                    <td>Vessel Docking Fee</td>
                                    <td><strong>₱1,500.00</strong></td>
                                    <td><span class="badge bg-success-light text-success">Completed</span></td>
                                    <td>Today, 10:23 AM</td>
                                </tr>
                                <tr>
                                    <td><strong>#TRX-9981</strong></td>
                                    <td><span class="badge bg-success-light text-success">Public Market</span></td>
                                    <td>Stall Rental - Sec A</td>
                                    <td><strong>₱5,000.00</strong></td>
                                    <td><span class="badge bg-warning-light text-warning">Pending</span></td>
                                    <td>Today, 09:45 AM</td>
                                </tr>
                                <tr>
                                    <td><strong>#TRX-9980</strong></td>
                                    <td><span class="badge bg-warning-light text-warning">Terminal</span></td>
                                    <td>Bus Terminal Fee</td>
                                    <td><strong>₱250.00</strong></td>
                                    <td><span class="badge bg-success-light text-success">Completed</span></td>
                                    <td>Today, 09:12 AM</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        

@endsection