@extends('layouts.app')

@section('content')
@include('terminal.partials.terminal_shared_styles')

@php
    $log = $payment->parkingLog;
    $vehicle = $log?->vehicle;
    $type = $vehicle?->type;
    $entryAt = optional($log?->entry_at)->format('m/d/Y h:i A');
    $exitAt = optional($log?->exit_at)->format('m/d/Y h:i A');
@endphp

<div class="tm" data-server-rendered-page="send_payment" data-page-title="Terminal Receipt">
    @if (session('status'))
        <div class="tm-flash">{{ session('status') }}</div>
    @endif

    <section class="tm-card" style="max-width:840px;margin:0 auto;">
        <div class="tm-card-head">
            <h3><i class="fas fa-receipt"></i> Official Parking Receipt</h3>
            <div class="tm-action-row">
                <a href="{{ $pdfUrl }}" class="tm-btn-outline"><i class="fas fa-file-pdf"></i> Download PDF</a>
                <button type="button" onclick="window.print()" class="tm-btn-primary"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
        <div class="tm-card-body">
            <div style="display:grid;gap:14px;">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div>
                        <h2 style="margin:0;font-size:1.35rem;color:#0f172a;">Terminal Fee Collection Office</h2>
                        <p style="margin:4px 0 0;color:#64748b;">Parking Fee Receipt</p>
                    </div>
                    <div style="text-align:right;">
                        <div><strong>OR No:</strong> {{ $payment->or_number }}</div>
                        <div><strong>Date:</strong> {{ optional($payment->payment_date)->format('m/d/Y h:i A') }}</div>
                    </div>
                </div>

                <table class="tm-table" style="min-width:0;">
                    <tbody>
                        <tr><th style="width:180px;">Log Number</th><td>{{ $log?->log_number ?? '-' }}</td></tr>
                        <tr><th>Plate Number</th><td>{{ $vehicle?->plate_number ?? '-' }}</td></tr>
                        <tr><th>Operator</th><td>{{ $vehicle?->operator_name ?: '-' }}</td></tr>
                        <tr><th>Vehicle Type</th><td>{{ $type?->name ?? '-' }}</td></tr>
                        <tr><th>Entry Time</th><td>{{ $entryAt ?: '-' }}</td></tr>
                        <tr><th>Exit Time</th><td>{{ $exitAt ?: '-' }}</td></tr>
                        <tr><th>Billed Hours</th><td>{{ number_format((float) $payment->billed_hours_snapshot, 2) }}</td></tr>
                        <tr><th>Rate</th><td>PHP {{ number_format((float) $payment->parking_rate_snapshot, 2) }} / hr</td></tr>
                        <tr><th>Remarks</th><td>{{ $payment->remarks ?: '-' }}</td></tr>
                        <tr>
                            <th style="font-size:1rem;">Total Paid</th>
                            <td style="font-size:1.15rem;font-weight:800;color:#0f172a;">PHP {{ number_format((float) $payment->paid_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;color:#64748b;">
                    <span>Recorded by: {{ $payment->recordedBy?->name ?? 'Terminal Personnel' }}</span>
                    <span>Generated: {{ now()->format('m/d/Y h:i A') }}</span>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

