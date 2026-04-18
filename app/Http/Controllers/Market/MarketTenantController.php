<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\MarketTenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketTenantController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $tenantQuery = MarketTenant::query()
            ->with([
                'activeLease.stall.location',
            ]);

        if ($search !== '') {
            $like = '%' . $search . '%';
            $tenantQuery->where(function ($query) use ($like): void {
                $query->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('business_name', 'like', $like)
                    ->orWhere('business_type', 'like', $like)
                    ->orWhere('contact_number', 'like', $like)
                    ->orWhere('mpo_control_no', 'like', $like)
                    ->orWhereHas('activeLease.stall', function ($stallQuery) use ($like): void {
                        $stallQuery->where('stall_no', 'like', $like)
                            ->orWhereHas('location', function ($locationQuery) use ($like): void {
                                $locationQuery->where('location_code', 'like', $like)
                                    ->orWhere('location_name', 'like', $like);
                            });
                    });
            });
        }

        $tenants = $tenantQuery
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        $totalTenants = (int) MarketTenant::query()->count();
        $activeTenants = (int) MarketTenant::query()
            ->whereHas('activeLease')
            ->count();

        return view('market.vendors', [
            'tenants' => $tenants,
            'search' => $search,
            'summary' => [
                'total' => $totalTenants,
                'active' => $activeTenants,
                'inactive' => max(0, $totalTenants - $activeTenants),
            ],
        ]);
    }
}

