@extends('layouts.app')
@section('content')

        <div class="card">
            <div class="card-body" style="text-align: center; padding: 5rem 2rem;">
                <div style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem;">
                    <i class="fas fa-hammer"></i>
                </div>
                <h2>${title}</h2>
                <p class="text-muted mt-2">This module is currently under development.</p>
                <button class="btn btn-outline mt-4" onclick="navigateTo('dashboard')">Return to Dashboard</button>
            </div>
        </div>
    
@endsection