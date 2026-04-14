<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index()
    {
        $people = Person::latest()->get();
        return view('people', compact('people'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'required|integer|min:1|max:120',
            'address' => 'required|string|max:255',
        ]);

        Person::create($validated);

        return redirect()->back()->with('success', 'Saved to database!');
    }
}