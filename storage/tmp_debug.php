<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CollectorDepartmentAssignment;
use App\Models\CollectionDispatch;
use App\Models\CollectionDispatchItem;

$user = User::where('name', 'like', '%Venus%')->first();
if (! $user) { echo "no-user\n"; exit(0); }

echo "user_id={$user->id}\n";
$assign = CollectorDepartmentAssignment::with('department')->where('collector_user_id',$user->id)->first();
echo 'assignment_dept=' . ($assign?->department?->code ?? 'none') . "\n";

$dispatches = CollectionDispatch::where('department_code','market')->where('collector_user_id',$user->id)->orderByDesc('id')->limit(10)->get(['id','status','sent_at','created_at']);
echo 'market_dispatch_count=' . $dispatches->count() . "\n";
foreach($dispatches as $d){
  echo "dispatch#{$d->id} status={$d->status} sent_at={$d->sent_at} created={$d->created_at}\n";
}

$items = CollectionDispatchItem::whereIn('status',['sent','rejected','collected_pending_confirmation'])
  ->whereHas('dispatch', function($q) use ($user){ $q->where('department_code','market')->where('collector_user_id',$user->id); })
  ->orderByDesc('id')->limit(20)->get(['id','collection_dispatch_id','market_stall_lease_id','status','created_at']);
echo 'open_market_items_for_user=' . $items->count() . "\n";
foreach($items as $it){
  echo "item#{$it->id} disp={$it->collection_dispatch_id} lease={$it->market_stall_lease_id} status={$it->status} created={$it->created_at}\n";
}

$allOpenMarketItems = CollectionDispatchItem::whereIn('status',['sent','rejected','collected_pending_confirmation'])
  ->whereHas('dispatch', function($q){ $q->where('department_code','market'); })
  ->count();
echo 'all_open_market_items=' . $allOpenMarketItems . "\n";
