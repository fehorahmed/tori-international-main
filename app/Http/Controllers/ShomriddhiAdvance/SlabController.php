<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiAdvance\ShomriddhiInterestSlab;
use App\Services\ShomriddhiAdvance\InterestRateHistoryService;
use Illuminate\Support\Facades\DB;

class SlabController extends Controller
{
    public function index()
    {
        $pageTitle = 'Interest Slabs Configuration';
        $slabs = ShomriddhiInterestSlab::orderBy('min_amount')->get();
        return view('shomriddhi_advance.slabs.index', compact('slabs', 'pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {
            $history = app(InterestRateHistoryService::class);
            $history->lock();
            ShomriddhiInterestSlab::create($request->only('min_amount', 'max_amount', 'interest_rate'));
            $history->capture();
        });

        return redirect()->back()->with('success', 'Interest slab added successfully.');
    }

    public function update(Request $request, ShomriddhiInterestSlab $slab)
    {
        $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gt:min_amount',
            'interest_rate' => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request, $slab) {
            $history = app(InterestRateHistoryService::class);
            $history->lock();
            $slab->update($request->only('min_amount', 'max_amount', 'interest_rate'));
            $history->capture();
        });

        return redirect()->back()->with('success', 'Interest slab updated successfully.');
    }

    public function destroy(ShomriddhiInterestSlab $slab)
    {
        DB::transaction(function () use ($slab) {
            $history = app(InterestRateHistoryService::class);
            $history->lock();
            $slab->delete();
            $history->capture();
        });
        return redirect()->back()->with('success', 'Interest slab deleted successfully.');
    }
}
