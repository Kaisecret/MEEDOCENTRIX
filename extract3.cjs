const fs = require('fs');
const path = require('path');

const appJs = fs.readFileSync('../meedo/app.js', 'utf8');

// Render All Transactions
let start = appJs.indexOf("window.renderAllTransactionsPage = function");
let htmlStart = appJs.indexOf("contentArea.innerHTML =", start);
let backtickStart = appJs.indexOf("`", htmlStart);
let backtickEnd = appJs.indexOf("`;", backtickStart);

let allTransHtml = appJs.substring(backtickStart + 1, backtickEnd);

fs.writeFileSync('resources/views/admin/transactions.blade.php', `@extends('layouts.app')\n@section('content')\n${allTransHtml}\n@endsection`);

// Generic Table Page
let tableStart = appJs.indexOf("function renderTablePage");
let tableHtmlStart = appJs.indexOf("contentArea.innerHTML = `", tableStart);
let tableBacktickStart = appJs.indexOf("`", tableHtmlStart);
let tableBacktickEnd = appJs.indexOf("`;", tableBacktickStart);

let tableHtml = appJs.substring(tableBacktickStart + 1, tableBacktickEnd);
tableHtml = tableHtml.replace(/\$\{title\}/g, 'Records');
tableHtml = tableHtml.replace(/\$\{sendAllBtn\}/g, '');
tableHtml = tableHtml.replace(/\$\{addBtn\}/g, '<button class="btn btn-primary"><i class="fas fa-plus"></i> Add New</button>');
tableHtml = tableHtml.replace(/\$\{thHtml\}/g, '<th>ID</th><th>Details</th><th>Amount</th><th>Date</th><th>Status</th><th>Actions</th>');
tableHtml = tableHtml.replace(/\$\{trHtml\}/g, '<tr><td colspan="6" style="text-align: center; padding: 2rem;">No records found</td></tr>');

const roles = ['fishport', 'market', 'cemetery', 'terminal'];
roles.forEach(r => {
    fs.writeFileSync(`resources/views/${r}/transactions.blade.php`, `@extends('layouts.app')\n@section('content')\n${tableHtml}\n@endsection`);
});

console.log("Extracted missing pages.");
