<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Crm\Market;
use App\Models\Upazila;
use App\Models\District;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Division;

class MarketController extends Controller
{
    public function index()
    {
        $divisions = Division::orderBy('name')->get();
        return view('crm.markets.index', compact('divisions'));
    }

    public function dataTable(Request $request)
    {
        $query = Market::with('upazila')
            ->orderBy('id', 'desc');
            
        if ($request->upazila_id != '') {
            $query->where('upazila_id', $request->upazila_id);
        } elseif ($request->district_id != '') {
            $query->whereHas('upazila', function($q) use ($request) {
                $q->where('district_id', $request->district_id);
            });
        } elseif ($request->division_id != '') {
            $query->whereHas('upazila.district', function($q) use ($request) {
                $q->where('division_id', $request->division_id);
            });
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('upazila_name', function (Market $market) {
                return $market->upazila->name ?? 'N/A';
            })
            ->addColumn('status', function (Market $market) {
                return $market->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function (Market $market) {
                $btn = '';
                if(auth()->user()->can('market_edit')) {
                    $btn .= '<a href="' . route('markets.edit', $market->id) . '" class="btn btn-info btn-sm"><i class="fa fa-edit"></i></a> ';
                }
                // if(auth()->user()->can('market_delete')) {
                //     $btn .= '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $market->id . '"><i class="fa fa-trash"></i></button>';
                // }
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('market_create')) {
            abort(403, 'Unauthorized');
        }
        $districts = District::orderBy('name')->get();
        return view('crm.markets.create', compact('districts'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('market_create')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'upazila_id' => 'required|integer',
            'status' => 'required|integer',
        ]);

        Market::create([
            'name' => $request->name,
            'upazila_id' => $request->upazila_id,
            'status' => $request->status,
        ]);

        return redirect()->route('markets.index')->with('message', 'Market created successfully.');
    }

    public function edit(Market $market)
    {
        if (!auth()->user()->hasPermissionTo('market_edit')) {
            abort(403, 'Unauthorized');
        }
        $districts = District::orderBy('name')->get();
        $upazilas = Upazila::orderBy('name')->get();
        return view('crm.markets.edit', compact('market', 'districts', 'upazilas'));
    }

    public function update(Request $request, Market $market)
    {
        if (!auth()->user()->hasPermissionTo('market_edit')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'upazila_id' => 'required|integer',
            'status' => 'required|integer',
        ]);

        $market->update([
            'name' => $request->name,
            'upazila_id' => $request->upazila_id,
            'status' => $request->status,
        ]);

        return redirect()->route('markets.index')->with('message', 'Market updated successfully.');
    }

    public function destroy(Market $market)
    {
        if (!auth()->user()->hasPermissionTo('market_delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $market->delete();
        return response()->json(['success' => true, 'message' => 'Market deleted successfully.']);
    }
}
