@extends('layouts.app')
@section('content')

        <div class="grid-1-2">
            <div class="card">
                <div class="card-header flex-between">
                    <h3>System Roles</h3>
                    <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add</button>
                </div>
                <div class="card-body p-0">
                    <div class="role-list">
                        <div class="role-item active">
                            <div class="role-info">
                                <span class="role-name">Administrator</span>
                                <span class="badge bg-primary-100 text-primary-700" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Full Access</span>
                            </div>
                            <i class="fas fa-chevron-right text-primary-400"></i>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Fishport Personnel</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Public Market Personnel</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Cemetery Personnel</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Terminal Personnel</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Assigned Collector</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                        <div class="role-item">
                            <div class="role-info">
                                <span class="role-name">Main Cashier</span>
                                <span class="badge bg-gray-200 text-gray-600" style="font-size:0.7rem; padding: 2px 6px; border-radius: 4px;">Restricted</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header flex-between">
                    <div>
                        <h3>Permissions: Administrator</h3>
                        <p class="text-muted text-sm mt-1">Configure access levels for this role.</p>
                    </div>
                    <span class="status-badge active" style="font-size: 0.75rem;">System Role</span>
                </div>
                <div class="card-body bg-gray-50" style="background: var(--gray-50);">
                    <div class="permissions-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                        
                        <div class="permission-card" style="background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 18px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid var(--gray-100); padding-bottom: 14px;">
                                <div style="width: 32px; height: 32px; background: var(--primary-50); color: var(--primary-400); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-chart-pie"></i></div>
                                <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--gray-800); margin: 0;">Dashboard & Analytics</h4>
                            </div>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">View Main Dashboard</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">View Revenue Analytics</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Export Reports</span></label>
                        </div>

                        <div class="permission-card" style="background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 18px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid var(--gray-100); padding-bottom: 14px;">
                                <div style="width: 32px; height: 32px; background: var(--success-light); color: var(--success); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-users-gear"></i></div>
                                <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--gray-800); margin: 0;">User Management</h4>
                            </div>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">View Users Directory</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Add New Users</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Edit User Details</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Delete/Suspend Users</span></label>
                        </div>

                        <div class="permission-card" style="background: white; border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 18px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; border-bottom: 1px solid var(--gray-100); padding-bottom: 14px;">
                                <div style="width: 32px; height: 32px; background: var(--warning-light); color: var(--warning); border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-tags"></i></div>
                                <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--gray-800); margin: 0;">System Configuration</h4>
                            </div>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Manage Roles & Permissions</span></label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;"><input type="checkbox" checked disabled style="width: 16px; height: 16px; accent-color: var(--primary-400);"><span style="font-size: 0.85rem; color: var(--gray-700);">Manage Rates & Fees Matrix</span></label>
                        </div>

                    </div>
                </div>
                <div class="card-footer" style="padding: 16px 22px; border-top: 1px solid var(--gray-100); display: flex; justify-content: flex-end; gap: 10px; background: white;">
                    <button class="btn btn-secondary">Discard Changes</button>
                    <button class="btn btn-primary" onclick="showToast('Permissions saved successfully!', 'success')"><i class="fas fa-save"></i> Save Permissions</button>
                    </div>
                </div>
            </div>
        </div>
    
@endsection