@extends('layouts.app')
@section('content')

        <div class="card mb-4" style="background: linear-gradient(135deg, var(--primary-800), var(--primary-600)); color: white; border: none;">
            <div class="card-body" style="padding: 2.5rem 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem; font-size: 1.8rem;">Reports & Analytics Studio</h2>
                        <p style="opacity: 0.9; max-width: 600px; line-height: 1.5;">Generate comprehensive insights, export department records, and audit system activities. Select a report type below to configure and download your data.</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: var(--radius-md); backdrop-filter: blur(10px);">
                        <div style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 0.25rem;">Total Records Available</div>
                        <div style="font-size: 1.5rem; font-weight: 700;"><i class="fas fa-database" style="margin-right: 8px; font-size: 1.2rem;"></i>1,284</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
            
            <!-- Report Card 1 -->
            <div class="card report-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body">
                    <div style="width: 48px; height: 48px; background: var(--primary-50); color: var(--primary-600); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem;">Collection Summary</h3>
                    <p class="text-muted text-sm mb-4" style="line-height: 1.5; min-height: 42px;">Consolidated summary of all financial collections within a specific date range.</p>
                    
                    <div class="form-group mb-3">
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.5rem; display: block;">Date Range</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" class="form-control" style="flex: 1;" title="Start Date">
                            <input type="date" class="form-control" style="flex: 1;" title="End Date">
                        </div>
                    </div>
                    <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="simulateReportGeneration('Collection Summary Report', 'collection')">
                        <i class="fas fa-magic" style="margin-right: 8px;"></i> Generate Report
                    </button>
                </div>
            </div>

            <!-- Report Card 2 -->
            <div class="card report-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body">
                    <div style="width: 48px; height: 48px; background: var(--success-light); color: var(--success); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem;">Department Records</h3>
                    <p class="text-muted text-sm mb-4" style="line-height: 1.5; min-height: 42px;">Export detailed transaction ledgers and logs specific to a department.</p>
                    
                    <div class="form-group mb-3">
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.5rem; display: block;">Select Target</label>
                        <select class="form-control" style="width: 100%;">
                            <option>All Departments</option>
                            <option>Fishport</option>
                            <option>Public Market</option>
                            <option>Cemetery</option>
                            <option>Terminal</option>
                            <option>Atrium Hall</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" style="width: 100%; justify-content: center; background: var(--success); border-color: var(--success);" onclick="simulateReportGeneration('Department Ledger (CSV)', 'department')">
                        <i class="fas fa-file-csv" style="margin-right: 8px;"></i> Export to CSV
                    </button>
                </div>
            </div>

            <!-- Report Card 3 -->
            <div class="card report-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body">
                    <div style="width: 48px; height: 48px; background: var(--warning-light); color: var(--warning); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem;">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.2rem;">System Audit Trail</h3>
                    <p class="text-muted text-sm mb-4" style="line-height: 1.5; min-height: 42px;">Review system access logs, role changes, and record modifications for security.</p>
                    
                    <div class="form-group mb-3">
                        <label style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--gray-500); margin-bottom: 0.5rem; display: block;">Filter by Action Type</label>
                        <select class="form-control" style="width: 100%;">
                            <option>All Actions</option>
                            <option>User Logins</option>
                            <option>Record Additions</option>
                            <option>Updates & Deletions</option>
                        </select>
                    </div>
                    <button class="btn btn-outline" style="width: 100%; justify-content: center; color: var(--gray-700); border-color: var(--gray-300);" onclick="simulateReportGeneration('System Audit Trail', 'audit')">
                        <i class="fas fa-search" style="margin-right: 8px;"></i> Query Logs
                    </button>
                </div>
            </div>
        </div>

        <!-- Report Generation Overlay (Hidden by default) -->
        <div id="reportGenerationOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(255, 255, 255, 0.9); z-index: 9999; flex-direction: column; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
            
            <div style="background: white; padding: 3rem; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); text-align: center; max-width: 400px; width: 90%;">
                
                <!-- Animated Icon Container -->
                <div style="position: relative; width: 80px; height: 80px; margin: 0 auto 2rem auto;">
                    <!-- Outer spinning ring -->
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 4px solid var(--primary-100); border-top-color: var(--primary-600); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <!-- Inner static icon -->
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: var(--primary-600); font-size: 2rem;">
                        <i class="fas fa-file-invoice" id="reportGenIcon"></i>
                    </div>
                </div>

                <h3 id="reportGenTitle" style="color: var(--gray-800); margin-bottom: 0.5rem; font-size: 1.25rem;">Compiling Data...</h3>
                <p id="reportGenDesc" style="color: var(--gray-500); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.5;">Please wait while we gather the necessary records for your report.</p>
                
                <!-- Progress Bar -->
                <div style="width: 100%; height: 6px; background: var(--gray-100); border-radius: 3px; overflow: hidden; margin-bottom: 1rem;">
                    <div id="reportGenProgress" style="height: 100%; width: 0%; background: linear-gradient(90deg, var(--primary-400), var(--primary-600)); transition: width 0.3s ease;"></div>
                </div>
                
                <div id="reportGenStatusText" style="font-size: 0.75rem; color: var(--gray-400); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">0%</div>
            </div>

            <style>
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                @keyframes pulse-success { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }
                .report-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
            </style>
        </div>
    
@endsection