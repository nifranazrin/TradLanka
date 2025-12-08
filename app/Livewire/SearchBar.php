<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class SearchBar extends Component
{
    public $query = ''; // Stores what the user types
    public $results = []; // Stores the search results

    // This runs automatically whenever $query changes (Live Search)
    public function updatedQuery()
    {
        // 1. Allow single letter searches
        if (strlen($this->query) < 1) { 
            $this->results = [];
            return;
        }

        // 2. Search Logic (Updated to match your Controller)
        $this->results = Product::where('name', 'like', '%' . $this->query . '%')
            ->whereIn('status', ['approved', 'reapproved']) // <--- FIX: Check for 'approved', not 1
            ->where('is_active', 1) // <--- FIX: Ensure product is active
            ->take(5)
            ->get();
    }

    // This runs when the user hits "Enter"
    public function search()
    {
        // Redirect to the search results page with the query
        if (!empty($this->query)) {
            return redirect()->route('search.page', ['query' => $this->query]);
        }
    }

    public function render()
    {
        return view('livewire.search-bar');
    }
}