<?php

namespace App\Http\Controllers\SrManagement;

use App\Enumeration\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use Illuminate\Http\Request;
use App\Models\SrManagement\SrSocialEngagement;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\UploadTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SrSocialEngagementController extends Controller
{
    use UploadTrait;

    public function index()
    {
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->get();
        return view('sr_management.social_engagements.index', compact('srs'));
    }

    public function dataTable(Request $request)
    {
        $query = SrSocialEngagement::with('sr', 'user')
            ->where('sister_concern_id', auth()->user()->activeConcernId());

        if (auth()->user()->role == RoleType::$SR) {
            $query->where('sr_id', auth()->user()->employee_id);
        } else {
            if ($request->sr_id) {
                $query->where('sr_id', $request->sr_id);
            }
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('date', [
                Carbon::parse($request->start_date)->format('Y-m-d'), 
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);
        }

        if ($request->platform) {
            $query->where('platform', $request->platform);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('d M, Y');
            })
            ->addColumn('sr_name', function ($row) {
                return $row->sr->name ?? 'N/A';
            })
            ->addColumn('screenshot', function ($row) {
                if ($row->screenshot) {
                    return '<img src="' . asset($row->screenshot) . '" height="50px" class="img-thumbnail" style="cursor:pointer;" onclick="window.open(\'' . asset($row->screenshot) . '\')">';
                }
                return 'N/A';
            })
            ->addColumn('platform_badge', function ($row) {
                if ($row->platform == 'youtube') {
                    return '<span class="badge badge-danger"><i class="fab fa-youtube"></i> YouTube</span>';
                }
                return '<span class="badge badge-primary"><i class="fab fa-facebook"></i> Facebook</span>';
            })
            ->addColumn('gps', function ($row) {
                if ($row->lat && $row->long) {
                    return '<a href="https://www.google.com/maps?q=' . $row->lat . ',' . $row->long . '" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fa fa-map-marker-alt"></i> Map</a>';
                }
                return 'N/A';
            })
            ->addColumn('created_by', function ($row) {
                return $row->user->name ?? 'N/A';
            })
            ->rawColumns(['screenshot', 'platform_badge', 'gps', 'created_by'])
            ->toJson();
    }

    public function create()
    {
        $user = auth()->user();
        $srs = [];
        if ($user->role != RoleType::$SR) {
            $srs = Employee::where('sister_concern_id', $user->activeConcernId())
                ->srs()
                ->get();
        }
        return view('sr_management.social_engagements.create', compact('srs'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $rules = [
            'platform' => 'required|in:youtube,facebook',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'image' => 'required', // Base64 image
            'lat' => 'nullable',
            'long' => 'nullable',
        ];

        if ($user->role != RoleType::$SR) {
            $rules['sr_id'] = 'required';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $image_name = null;
            if ($request->image) {
                $image_name = $this->uploadBase64Image($request->image, 'sr/social', 800);
            }

            // Get location name
            $location_address = null;
            if ($request->lat && $request->long) {
                $location_address = app(\App\Http\Controllers\Crm\LeadController::class)->getLocationName($request->lat, $request->long);
            }

            $sr_id = $user->employee_id;
            if ($user->role != RoleType::$SR) {
                $sr_id = $request->sr_id;
            }

            SrSocialEngagement::create([
                'sister_concern_id' => $user->activeConcernId(),
                'sr_id' => $sr_id,
                'user_id' => $user->id, // Store the logged-in user who created the entry
                'platform' => $request->platform,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'screenshot' => $image_name,
                'date' => Carbon::now()->format('Y-m-d'),
                'lat' => $request->lat,
                'long' => $request->long,
                'location' => $location_address,
            ]);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Entry saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
