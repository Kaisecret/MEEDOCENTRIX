<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$rows = App\Models\CemeteryOccupantRecord::query()->with(['plot'])->get(['id','record_no','deceased_name','status','cemetery_plot_id']);
foreach ($rows as $r) {
    $plot = $r->plot?->plot_reference ?? '-';
    echo $r->id . ' | ' . $r->record_no . ' | ' . $r->deceased_name . ' | ' . $r->status . ' | ' . $plot . PHP_EOL;
}
