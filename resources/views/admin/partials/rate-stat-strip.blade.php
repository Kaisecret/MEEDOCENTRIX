<div class="rate-stat-strip">
    <div class="rate-stat">
        <span>Items</span>
        <strong>{{ number_format((int) $stats['items']) }}</strong>
    </div>
    <div class="rate-stat">
        <span>Active</span>
        <strong>{{ number_format((int) $stats['active']) }}</strong>
    </div>
    <div class="rate-stat">
        <span>Average</span>
        <strong>{{ $money((float) $stats['average']) }}</strong>
    </div>
</div>
