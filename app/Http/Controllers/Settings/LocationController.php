<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Pourashava;
use App\Models\Union;
use App\Models\Village;
use Yajra\DataTables\Facades\DataTables;

class LocationController extends Controller
{
    private function getModelConfig($type)
    {
        $map = [
            'divisions' => [
                'model' => Division::class, 
                'title' => 'Division', 
                'parents' => []
            ],
            'districts' => [
                'model' => District::class, 
                'title' => 'District', 
                'parents' => [
                    ['key' => 'division_id', 'title' => 'Division', 'model' => Division::class]
                ]
            ],
            'upazilas' => [
                'model' => Upazila::class, 
                'title' => 'Upazila', 
                'parents' => [
                    ['key' => 'district_id', 'title' => 'District', 'model' => District::class]
                ]
            ],
            'pourashavas' => [
                'model' => Pourashava::class, 
                'title' => 'Pourashava', 
                'parents' => [
                    ['key' => 'district_id', 'title' => 'District', 'model' => District::class]
                ]
            ],
            'unions' => [
                'model' => Union::class, 
                'title' => 'Union', 
                'parents' => [
                    ['key' => 'upazila_id', 'title' => 'Upazila', 'model' => Upazila::class]
                ]
            ],
            'villages' => [
                'model' => Village::class, 
                'title' => 'Village', 
                'parents' => [
                    ['key' => 'upazila_id', 'title' => 'Upazila', 'model' => Upazila::class],
                    ['key' => 'union_id', 'title' => 'Union', 'model' => Union::class]
                ]
            ],
            'roads' => [
                'model' => \App\Models\RoutePlan\Road::class,
                'title' => 'Road', 
                'parents' => [
                    ['key' => 'upazila_id', 'title' => 'Upazila', 'model' => Upazila::class]
                ]
            ],
        ];

        return $map[$type] ?? null;
    }

    public function index($type)
    {
        $config = $this->getModelConfig($type);
        if (!$config) abort(404);

        $parentsData = [];
        foreach ($config['parents'] as $parent) {
            $parentsData[$parent['key']] = $parent['model']::all();
        }

        return view('settings.locations.index', compact('type', 'config', 'parentsData'));
    }

    public function dataTable(Request $request, $type)
    {
        $config = $this->getModelConfig($type);
        if (!$config) abort(404);

        $query = $config['model']::orderBy('id','asc');

        if ($type === 'roads') {
            $query->where('sister_concern_id', auth()->user()->activeConcernId());
        }

        // Apply filters from request
        foreach ($config['parents'] as $parent) {
            if ($request->has($parent['key']) && $request->get($parent['key']) != '') {
                $query->where($parent['key'], $request->get($parent['key']));
            }
        }
        
        // Eager load parents if they exist
        $withRelations = [];
        foreach ($config['parents'] as $parent) {
            $relationName = str_replace('_id', '', $parent['key']);
            if (method_exists($config['model'], $relationName)) {
                $withRelations[] = $relationName;
            }
        }
        if (!empty($withRelations)) {
            $query->with($withRelations);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function($item) use ($type) {
                /*
                $btn = '<a href="' . route('locations.edit', [$type, $item->id]) . '" class="btn btn-info btn-sm"><i class="fa fa-edit"></i> Edit</a> ';
                $btn .= '<form action="' . route('locations.destroy', [$type, $item->id]) . '" method="POST" class="d-inline">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this?\')"><i class="fa fa-trash"></i> Delete</button>
                        </form>';
                return $btn;
                */
                return '-';
            })
            ->addColumn('parents', function($item) use ($config) {
                $parentsText = [];
                foreach ($config['parents'] as $parent) {
                    $relation = str_replace('_id', '', $parent['key']);
                    $parentsText[$parent['title']] = $item->$relation->name ?? '-';
                }
                return $parentsText; // We will handle this in frontend columns, but Yajra prefers direct columns. 
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create($type)
    {
        $config = $this->getModelConfig($type);
        if (!$config) abort(404);

        $parentsData = [];
        foreach ($config['parents'] as $parent) {
            $parentsData[$parent['key']] = $parent['model']::all();
        }

        return view('settings.locations.create', compact('type', 'config', 'parentsData'));
    }

    public function store(Request $request, $type)
    {
        $config = $this->getModelConfig($type);
        if (!$config) abort(404);

        $rules = [
            'name' => 'required|string|max:255',
            'bn_name' => 'nullable|string|max:255',
        ];

        // Ensure parent fields are required
        foreach ($config['parents'] as $parent) {
            $rules[$parent['key']] = 'required|integer';
        }

        // Build unique rule for name based on parents
        $tableName = (new $config['model'])->getTable();
        $uniqueRule = \Illuminate\Validation\Rule::unique($tableName, 'name');
        
        foreach ($config['parents'] as $parent) {
            if ($request->has($parent['key'])) {
                $uniqueRule->where($parent['key'], $request->get($parent['key']));
            }
        }
        $rules['name'] = ['required', 'string', 'max:255', $uniqueRule];

        $request->validate($rules);

        $data = $request->except('_token');
        if ($type === 'roads') {
            $data['sister_concern_id'] = auth()->user()->activeConcernId();
        }
        $config['model']::create($data);

        return redirect()->route('locations.index', $type)->with('success', $config['title'] . ' Created Successfully!');
    }

    public function edit($type, $id)
    {
        $config = $this->getModelConfig($type);
        if (!$config) abort(404);

        $item = $config['model']::findOrFail($id);
        
        $parentsData = [];
        foreach ($config['parents'] as $parent) {
            $parentsData[$parent['key']] = $parent['model']::all();
        }

        return view('settings.locations.edit', compact('item', 'type', 'config', 'parentsData'));
    }

    public function update(Request $request, $type, $id)
    {
        $config = $this->getModelConfig($type);
        if (!$config) abort(404);

        $item = $config['model']::findOrFail($id);
        
        $rules = [
            'name' => 'required|string|max:255',
            'bn_name' => 'nullable|string|max:255',
        ];

        foreach ($config['parents'] as $parent) {
            $rules[$parent['key']] = 'required|integer';
        }

        // Build unique rule for name based on parents, ignoring current ID
        $tableName = (new $config['model'])->getTable();
        $uniqueRule = \Illuminate\Validation\Rule::unique($tableName, 'name')->ignore($id);
        
        foreach ($config['parents'] as $parent) {
            if ($request->has($parent['key'])) {
                $uniqueRule->where($parent['key'], $request->get($parent['key']));
            }
        }
        $rules['name'] = ['required', 'string', 'max:255', $uniqueRule];

        $request->validate($rules);

        $data = $request->except('_token', '_method');
        $item->update($data);

        return redirect()->route('locations.index', $type)->with('success', $config['title'] . ' Updated Successfully!');
    }

    public function destroy($type, $id)
    {
        $config = $this->getModelConfig($type);
        if (!$config) abort(404);

        $item = $config['model']::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', $config['title'] . ' Deleted Successfully!');
    }

    public function unionsByUpazila(Request $request)
    {
        $unions = Union::where('upazila_id', $request->upazila_id)
            ->orderBy('name')
            ->get(['id', 'name', 'bn_name']);
        return response()->json($unions);
    }
}
