@extends('layouts.app')

@section('content')
<style>
#contentArea{padding:10px !important}
.sdt-page{max-width:1200px;margin:0 auto;display:grid;gap:10px;font-family:'Inter',system-ui,sans-serif}
.sdt-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
.sdt-back{display:inline-flex;align-items:center;gap:7px;border:1px solid #cbd5e1;background:#fff;color:#0f5fa8;border-radius:9px;padding:.5rem .8rem;font-size:.82rem;font-weight:700;text-decoration:none}
.sdt-back:hover{background:#f8fafc}
.sdt-title{margin:0;color:#0f172a;font-size:1.05rem;font-weight:800}
.sdt-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.05);overflow:hidden}
.sdt-kpis{padding:10px;border-bottom:1px solid #e2e8f0;display:grid;grid-template-columns:repeat(5,minmax(84px,1fr));gap:10px;background:#fafcff}
.sdt-kpi{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.55rem .7rem;text-align:center}
.sdt-kpi span{display:block;font-size:.66rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:700;margin-bottom:3px}
.sdt-kpi strong{font-size:1.05rem;line-height:1;color:#0f172a}
.sdt-kpi-missed{background:#fff1f2;border-color:#fecdd3}
.sdt-kpi-missed strong{color:#b91c1c}
.sdt-wrap{overflow:auto}
.sdt-table{width:100%;border-collapse:collapse;min-width:720px}
.sdt-table th{background:#eef5fb;color:#103250;text-transform:uppercase;letter-spacing:.04em;font-size:.73rem;font-weight:700;text-align:center;padding:10px;border-bottom:1px solid #e2e8f0}
.sdt-table th:first-child{text-align:left}
.sdt-table td{padding:10px;border-bottom:1px solid #f1f5f9;font-size:.9rem;color:#334155;text-align:center}
.sdt-table td:first-child{text-align:left;font-weight:700;color:#1e3a5f}
.sdt-table tbody tr:hover td{background:#f8fafc}
.sdt-missed{color:#b91c1c;font-weight:800}
.sdt-empty{text-align:center;color:#64748b;padding:18px !important}
@media (max-width:980px){.sdt-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:640px){.sdt-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>

<div data-server-rendered-page="send_payment" data-page-title="Due Tracker" class="sdt-page">
    <div class="sdt-head">
        <h1 class="sdt-title"><i class="fa-solid fa-calendar-check" style="color:#0f5fa8;margin-right:8px;"></i>Daily Due Tracker</h1>
        <a class="sdt-back" href="{{ route('market.send_payment') }}"><i class="fa-solid fa-arrow-left"></i> Back to Send for Payment</a>
    </div>

    <section class="sdt-card">
        <div class="sdt-kpis">
            <div class="sdt-kpi"><span>Due Today</span><strong>{{ number_format((int) ($dueTrackerToday['due'] ?? 0)) }}</strong></div>
            <div class="sdt-kpi"><span>Sent/Open</span><strong>{{ number_format((int) ($dueTrackerToday['sent'] ?? 0)) }}</strong></div>
            <div class="sdt-kpi"><span>Awaiting</span><strong>{{ number_format((int) ($dueTrackerToday['awaiting'] ?? 0)) }}</strong></div>
            <div class="sdt-kpi"><span>Paid</span><strong>{{ number_format((int) ($dueTrackerToday['paid'] ?? 0)) }}</strong></div>
            <div class="sdt-kpi sdt-kpi-missed"><span>Missed</span><strong>{{ number_format((int) ($dueTrackerToday['missed'] ?? 0)) }}</strong></div>
        </div>
        <div class="sdt-wrap">
            <table class="sdt-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Due</th>
                        <th>Sent/Open</th>
                        <th>Awaiting</th>
                        <th>Paid</th>
                        <th>Missed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dueTrackerRows as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ number_format((int) $row['due']) }}</td>
                            <td>{{ number_format((int) $row['sent']) }}</td>
                            <td>{{ number_format((int) $row['awaiting']) }}</td>
                            <td>{{ number_format((int) $row['paid']) }}</td>
                            <td class="{{ (int) $row['missed'] > 0 ? 'sdt-missed' : '' }}">{{ number_format((int) $row['missed']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="sdt-empty">No due tracker records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
