<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Notice</title>
    <style>
        @page { size: A4 portrait; margin: 14mm 12mm 12mm 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Times New Roman", Georgia, serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.45;
            background: #fff;
        }
        .sheet { width: 100%; }
        .title {
            text-align: center;
            font-size: 34px;
            letter-spacing: 8px;
            font-weight: 700;
            margin: 0 0 14px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 13px;
        }
        .meta-label {
            width: 130px;
            font-weight: 700;
        }
        .meta-value {
            font-weight: 700;
            border-bottom: 1px solid #111827;
        }
        .rule {
            border-top: 2px solid #111827;
            margin: 8px 0 12px;
        }
        .lead {
            margin: 0 0 10px;
            text-align: justify;
            text-indent: 36px;
        }
        .notice-box {
            border: 1px solid #111827;
            padding: 10px 12px;
            margin: 8px 0 12px;
            text-align: justify;
            background: #fafafa;
        }
        .signature-wrap {
            margin-top: 18px;
            width: 100%;
        }
        .signature-right {
            width: 45%;
            margin-left: auto;
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #111827;
            height: 26px;
            margin-bottom: 4px;
        }
        .sig-name {
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
        }
        .sig-role {
            font-size: 11px;
            color: #374151;
        }
        .sig-role-strong {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: #111827;
        }
        .sig-role-sub {
            font-size: 12px;
            text-transform: uppercase;
            color: #111827;
        }
        .important {
            margin-top: 14px;
            border-top: 2px solid #111827;
            padding-top: 8px;
            font-size: 12px;
            font-weight: 700;
        }
        .important span {
            font-weight: 400;
        }
        .ledger {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 11px;
        }
        .ledger th, .ledger td {
            border: 1px solid #111827;
            padding: 5px 6px;
            vertical-align: top;
        }
        .ledger th {
            background: #f3f4f6;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
            text-align: left;
        }
        .ledger td.num, .ledger th.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        .ledger tfoot td {
            font-weight: 700;
            background: #f9fafb;
        }
        .empty {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 12px !important;
        }
        .ack {
            margin-top: 14px;
            width: 100%;
            border-collapse: collapse;
        }
        .ack td {
            padding: 4px 0;
            font-size: 12px;
        }
        .line {
            display: inline-block;
            min-width: 220px;
            border-bottom: 1px solid #111827;
            height: 16px;
            vertical-align: bottom;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <h1 class="title">Final Notice</h1>

        <table class="meta-table">
            <tr>
                <td class="meta-label">Property ID</td>
                <td class="meta-value">{{ $stallNo }}</td>
                <td class="meta-label">Location Code</td>
                <td class="meta-value">{{ $locationCode }}</td>
            </tr>
            <tr>
                <td class="meta-label">Lessee</td>
                <td class="meta-value">{{ $tenantName }}</td>
                <td class="meta-label">Contract No.</td>
                <td class="meta-value">{{ $contractNo }}</td>
            </tr>
            <tr>
                <td class="meta-label">Address</td>
                <td class="meta-value" colspan="3">{{ $address }}</td>
            </tr>
        </table>

        <div class="rule"></div>

        <p>Dear Sir/Madam,</p>

        <p class="lead">
            Per record of this office, your overdue account has reached the maximum number of past-due rent as required by law.
        </p>
        <p class="lead">
            Chapter V, Article A, Section 4(a) of the Municipal Tax Ordinance of 2009 states:
        </p>
        <p class="lead">
            "The lessee who fails to pay the monthly rental fee within the prescribed period shall pay a surcharge of twenty-five percent (25%) of the total rent due plus interest of two percent (2%) per month but in no case shall the total interest on the unpaid rental exceed 36 months or 72%. Failure to pay the rental fee for one (1) month shall cause the automatic cancellation of the contract of lease of the stall, without prejudice to suing the lessee for the unpaid rents at the expense of the lessee. The stall shall be declared vacant and subject to adjudication."
        </p>

        <div class="notice-box">
            Kindly settle your accounts <strong>WITHIN FIVE (5) DAYS FROM RECEIPT HEREOF</strong>. Failure to do so will compel us to enforce the above-mentioned provision in the Municipal Tax Ordinance. Please refer to the table below for statement of your overdue account.
        </div>

        <p class="lead"><em>Thank you very much.</em></p>

        <div class="signature-wrap">
            <div class="signature-right">
                <div class="sig-line"></div>
                <div class="sig-name">DARCY V. BUNGAY</div>
                <div class="sig-role-strong">Municipal Economic Enterprise</div>
                <div class="sig-role-sub">Development Officer (MEEDO)</div>
            </div>
        </div>

        <div class="important">
            IMPORTANT:
            <span>
                The figures below reflect overdue accounts as of {{ strtoupper($statementMonth) }}. Please present this bill when paying.
            </span>
        </div>

        <table class="ledger">
            <thead>
                <tr>
                    <th style="width:11%;">Posting ID</th>
                    <th style="width:15%;">Billing Period</th>
                    <th class="num" style="width:10%;">Rate</th>
                    <th class="num" style="width:10%;">Days Unpaid</th>
                    <th class="num" style="width:14%;">Unpaid Rent</th>
                    <th class="num" style="width:13%;">Surcharge</th>
                    <th class="num" style="width:13%;">Penalty</th>
                    <th class="num" style="width:14%;">Amount Due</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dueRows as $row)
                    <tr>
                        <td>{{ $row['posting_id'] }}</td>
                        <td>{{ $row['billing_period'] }}</td>
                        <td class="num">{{ number_format((float) $row['rate'], 2) }}</td>
                        <td class="num">{{ number_format((int) $row['days_unpaid']) }}</td>
                        <td class="num">{{ number_format((float) $row['unpaid_rent'], 2) }}</td>
                        <td class="num">{{ number_format((float) $row['surcharge'], 2) }}</td>
                        <td class="num">{{ number_format((float) $row['penalty'], 2) }}</td>
                        <td class="num"><strong>{{ number_format((float) $row['amount_due'], 2) }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">No unpaid cycle found for this tenant as of {{ $today->format('F d, Y') }}.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7">Total Amount Due</td>
                    <td class="num">PHP {{ number_format((float) $grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="ack">
            <tr>
                <td style="width:50%;">Delivered by: <span class="line"></span></td>
                <td style="width:50%;">Received by: <span class="line"></span></td>
            </tr>
            <tr>
                <td>Date Received: <span class="line"></span></td>
                <td>Signature: <span class="line"></span></td>
            </tr>
        </table>
    </div>
</body>
</html>
