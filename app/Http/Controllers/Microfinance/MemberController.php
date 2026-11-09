<?php

namespace App\Http\Controllers\Microfinance;

use App\Enumeration\SupplierType;
use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Microfinance\MfMember;
use App\Models\Microfinance\MfNominee;
use App\Models\Microfinance\MfSomiti;
use App\Models\Supplier;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Enumeration\ReservedAccountGroup;
use App\Traits\UploadTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MemberController extends Controller
{
    use UploadTrait;

    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member'), 403, 'Unauthorized');
        $pageTitle = 'Microfinance Members';
        $somitis = MfSomiti::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->orderBy('name')->get();
        return view('microfinance.member.index', compact('pageTitle', 'somitis'));
    }

    public function getNominees($id)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member'), 403, 'Unauthorized');
        $member = MfMember::where('sister_concern_id', auth()->user()->activeConcernId())->findOrFail($id);
        $nominees = MfNominee::where('mf_member_id', $member->id)->get();
        return response()->json($nominees);
    }

    public function printEmptyForm()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member'), 403, 'Unauthorized');
        $pageTitle = 'Member Admission Form (Blank)';
        return view('microfinance.member.print_empty_form', compact('pageTitle'));
    }

    public function dataTable(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member'), 403, 'Unauthorized');
        $query = MfMember::with('somiti')
            ->withCount([
                'accounts as active_accounts_count' => function ($q) {
                    $q->where('status', '!=', 0);
                },
                'loans as loans_count'
            ])
            ->where('sister_concern_id', auth()->user()->activeConcernId());

        if ($request->somiti_id) {
            $query->where('somiti_id', $request->somiti_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (MfMember $member) {
                $hasActiveAccounts = $member->active_accounts_count > 0 || $member->loans_count > 0;
                
                $btn = '<a href="' . route('microfinance.member.show', $member->id) . '" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> Profile</a>';
                
                if ($hasActiveAccounts) {
                    $btn .= ' <button class="btn btn-secondary btn-sm" disabled title="Cannot edit: Member has active accounts/loans"><i class="fa fa-edit"></i></button>';
                } else {
                    $btn .= ' <a href="' . route('microfinance.member.edit', $member->id) . '" class="btn btn-info btn-sm"><i class="fa fa-edit"></i></a>';
                }
                return $btn;
            })
            ->editColumn('photo', function (MfMember $member) {
                $photo = $member->photo ? asset($member->photo) : asset('img/no-image.webp');
                return '<img src="' . $photo . '" height="50px" style="border-radius: 5px;">';
            })
            ->editColumn('status', function (MfMember $member) {
                return $member->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->rawColumns(['action', 'photo', 'status'])
            ->toJson();
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member_create'), 403, 'Unauthorized');
        $pageTitle = 'Member Admission Form (নতুন সদস্য ভর্তি ফরম)';
        $divisions = Division::orderBy('name')->get();
        $somitis = MfSomiti::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->orderBy('name')->get();
        // Existing customers without mf_member link (for optional re-linking)
        $existingCustomers = Supplier::whereNotNull('id')
            ->whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$CORPORATE_CUSTOMER, SupplierType::$DEALER])
            ->whereNotIn('id', function($sub) {
                $sub->select('supplier_id')
                    ->from('mf_members')
                    ->where('sister_concern_id', auth()->user()->activeConcernId())
                    ->whereNotNull('supplier_id')
                    ->whereNull('deleted_at');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'mobile_no', 'type']);

        return view('microfinance.member.create', compact('pageTitle', 'divisions', 'somitis', 'existingCustomers'));
    }



    private function handleCustomerLink(Request $request, $customerOption)
    {
        if ($customerOption === 'existing' && $request->supplier_id) {
            return $request->supplier_id;
        }

        if ($customerOption === 'auto_create') {
            // Check if customer already exists by mobile number (Globally)
            $existingCustomer = Supplier::where('mobile_no', $request->mobile_no)
                ->where('type', SupplierType::$CUSTOMER)
                ->first();

            if ($existingCustomer) {
                return $existingCustomer->id;
            }

            // Create new Supplier
            $newCustomer = Supplier::create([
                'origin_sister_concern_id' => auth()->user()->activeConcernId(),
                'name'              => $request->name,
                'mobile_no'         => $request->mobile_no,
                'email'             => $request->email ?? null,
                'type'              => SupplierType::$CUSTOMER,
                'member_no'         => generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$CUSTOMER),
                'status'            => 1,
                'division_id'       => $request->present_division_id ?? null,
                'district_id'       => $request->present_district_id ?? null,
                'upazila_id'        => $request->present_upazila_id ?? null,
            ]);

            // Create Account Head
            $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)->first();

            if ($receivablesGroup) {
                $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $newCustomer->id)->first();
                if (!$accountHead){
                    $code = generateAccountCode($receivablesGroup->top_reserved_id);
                    $accountHead = new AccountHead();
                    $accountHead->sister_concern_id = null;
                    $accountHead->code = $code;
                }
                $accountHead->account_group_id = $receivablesGroup->id;
                $accountHead->supplier_id = $newCustomer->id;
                $accountHead->name = $request->name . '(Client)';
                $accountHead->nature = 'Debit';
                $accountHead->is_system = 1;
                $accountHead->is_control_account = 0;
                $accountHead->is_editable = 0;
                $accountHead->save();
            }

            return $newCustomer->id;
        }

        return null;
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member_create'), 403, 'Unauthorized');
        $request->validate([
            'somiti_id'       => 'required|exists:mf_somitis,id',
            'member_category' => 'required|integer|in:1,2,3',
            'admission_date'  => 'required|string',
            'name'            => 'required|string|max:255',
            'mobile_no'       => ['required', 'string', 'max:20', Rule::unique('mf_members', 'mobile_no')],
            'religion'        => 'nullable|string',
            'education'       => 'nullable|string',
            'dob'             => 'nullable|string',
            'gender'          => 'required|string',
            'marital_status'  => 'required|integer|in:1,2,3,4',
            'photo'           => 'nullable|image|max:5120',
            'admission_fee'   => 'nullable|numeric|min:0',
            'form_fee'        => 'nullable|numeric|min:0',
            'passbook_fee'    => 'nullable|numeric|min:0',
            // Customer linking
            'customer_option' => 'nullable|in:auto_create,existing,none',
            'supplier_id'     => 'nullable|required_if:customer_option,existing|exists:suppliers,id',
        ]);

        // Auto-link logic inside handleCustomerLink replaces this validation.

        try {
            DB::beginTransaction();

            $data = $request->except(['nominees', 'photo', 'signature_photo', 'nid_photo_front', 'nid_photo_back', 'document']);
            $data['sister_concern_id'] = auth()->user()->activeConcernId();
            $data['status'] = 1;

            // Format Dates
            if ($request->admission_date) {
                $data['admission_date'] = Carbon::createFromFormat('d-m-Y', $request->admission_date)->format('Y-m-d');
            }
            if ($request->dob) {
                $data['dob'] = Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d');
            }

            // Handle File Uploads using Trait
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->uploadImage($request->file('photo'), 'microfinance/member/photos', 300, 300);
            }
            if ($request->hasFile('signature_photo')) {
                $data['signature_photo'] = $this->uploadImage($request->file('signature_photo'), 'microfinance/member/signatures', 300, 100);
            }
            if ($request->hasFile('nid_photo_front')) {
                $data['nid_photo_front'] = $this->uploadImage($request->file('nid_photo_front'), 'microfinance/member/nid', 600);
            }
            if ($request->hasFile('nid_photo_back')) {
                $data['nid_photo_back'] = $this->uploadImage($request->file('nid_photo_back'), 'microfinance/member/nid', 600);
            }
            if ($request->hasFile('document')) {
                $data['document'] = $this->uploadFile($request->file('document'), 'microfinance/member/documents');
            }

            $data = $this->parseAddressFields($data, $request);

            // ── Customer (Supplier) Linking ──────────────────────────
            $customerOption = $request->input('customer_option', 'auto_create');
            $data['supplier_id'] = $this->handleCustomerLink($request, $customerOption);
            
            if ($data['supplier_id']) {
                $linkedSupplier = Supplier::find($data['supplier_id']);
                if ($linkedSupplier) {
                    $data['member_no'] = $linkedSupplier->member_no;
                }
            }
            // ─────────────────────────────────────────────────────────

            $member = MfMember::create($data);

            // Nominees Logic...
            if ($request->nominees) {
                foreach ($request->nominees as $key => $nData) {
                    if (empty($nData['name'])) continue;
                    $nomineeData = $nData;
                    unset($nomineeData['photo'], $nomineeData['signature_photo'], $nomineeData['nid_photo_front'], $nomineeData['nid_photo_back']);
                    $nomineeData['mf_member_id'] = $member->id;
                    
                    // Format DOB if present
                    if (!empty($nData['dob'])) {
                        $nomineeData['dob'] = date('Y-m-d', strtotime($nData['dob']));
                    }

                    if ($request->hasFile("nominees.$key.photo")) {
                        $nomineeData['photo'] = $this->uploadImage($request->file("nominees.$key.photo"), 'microfinance/nominee/photos', 300, 300);
                    }
                    if ($request->hasFile("nominees.$key.signature_photo")) {
                        $nomineeData['signature_photo'] = $this->uploadImage($request->file("nominees.$key.signature_photo"), 'microfinance/nominee/signatures', 300, 100);
                    }
                    if ($request->hasFile("nominees.$key.nid_photo_front")) {
                        $nomineeData['nid_photo_front'] = $this->uploadImage($request->file("nominees.$key.nid_photo_front"), 'microfinance/nominee/nid', 600);
                    }
                    if ($request->hasFile("nominees.$key.nid_photo_back")) {
                        $nomineeData['nid_photo_back'] = $this->uploadImage($request->file("nominees.$key.nid_photo_back"), 'microfinance/nominee/nid', 600);
                    }
                    $nomineeData = $this->parseAddressFields($nomineeData, $nData, true);
                    MfNominee::create($nomineeData);
                }
            }

            // Auto open general savings account for the member
            $savingsService = app(\App\Services\Microfinance\SavingsAccountService::class);
            $savingsService->autoOpenGeneralSavingsAccount($member);

            DB::commit();
            return redirect()->route('microfinance.member.index')->with('success', 'Member added successfully. Member No: ' . $memberNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function show(MfMember $member)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member'), 403, 'Unauthorized');
        abort_if($member->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        $member->load('nominees', 'somiti', 'accounts');
        $pageTitle = 'Member Profile: ' . $member->name;
        return view('microfinance.member.show', compact('pageTitle', 'member'));
    }

    public function edit(MfMember $member)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member_edit'), 403, 'Unauthorized');
        abort_if($member->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        // Check if member has active/approved accounts or loans
        if ($member->accounts()->where('status', '!=', 0)->count() > 0 || $member->loans()->count() > 0) {
            return redirect()->route('microfinance.member.index')->with('error', 'Cannot edit member profile because they already have active savings accounts or loans.');
        }

        $member->load('nominees', 'customer');
        $pageTitle = 'Edit Member Profile';
        $divisions = Division::orderBy('name')->get();
        $somitis = MfSomiti::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->orderBy('name')->get();

        // Existing customers not linked to any other member (or linked to this member)
        $existingCustomers = Supplier::whereNotNull('id')
            ->whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$CORPORATE_CUSTOMER, SupplierType::$DEALER])
            ->where(function($q) use ($member) {
                $q->whereNotIn('id', function($sub) use ($member) {
                    $sub->select('supplier_id')
                        ->from('mf_members')
                        ->where('sister_concern_id', auth()->user()->activeConcernId())
                        ->whereNotNull('supplier_id')
                        ->where('id', '!=', $member->id)
                        ->whereNull('deleted_at');
                })
                ->orWhere('id', $member->supplier_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'mobile_no', 'type']);

        return view('microfinance.member.edit', compact('pageTitle', 'member', 'divisions', 'somitis', 'existingCustomers'));
    }

    public function update(Request $request, MfMember $member)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member_edit'), 403, 'Unauthorized');
        abort_if($member->sister_concern_id !== auth()->user()->activeConcernId(), 403);
        // Security check
        if ($member->accounts()->where('status', '!=', 0)->count() > 0 || $member->loans()->count() > 0) {
            return redirect()->route('microfinance.member.index')->with('error', 'Update denied: Member has active accounts or loans.');
        }

        $request->validate([
            'somiti_id' => 'required|exists:mf_somitis,id',
            'member_category' => 'required|integer|in:1,2,3',
            'admission_date' => 'required|string',
            'name' => 'required|string|max:255',
            'mobile_no' => ['required', 'string', 'max:20', Rule::unique('mf_members', 'mobile_no')->ignore($member->id)],
            'religion' => 'nullable|string',
            'education' => 'nullable|string',
            'dob' => 'nullable|string',
            'gender' => 'required|string',
            'marital_status' => 'required|integer|in:1,2,3,4',
            'photo' => 'nullable|image|max:5120',
            'admission_fee' => 'nullable|numeric|min:0',
            'form_fee' => 'nullable|numeric|min:0',
            'passbook_fee' => 'nullable|numeric|min:0',
            // Customer linking
            'customer_option' => 'nullable|in:auto_create,existing,none,keep_current',
            'supplier_id'     => 'nullable|required_if:customer_option,existing|exists:suppliers,id',
        ]);

        // Validate customer mobile uniqueness based on customer option during update
        $customerOption = $request->input('customer_option');
        if ($customerOption === 'existing' && $request->supplier_id) {
            $request->validate([
                'mobile_no' => [
                    Rule::unique('suppliers', 'mobile_no')
                        ->where('sister_concern_id', auth()->user()->activeConcernId())
                        ->whereNull('deleted_at')
                        ->ignore($request->supplier_id)
                ]
            ], [
                'mobile_no.unique' => 'Another Customer with this mobile number already exists. Cannot update/link to this customer.'
            ]);
        } elseif ($customerOption !== 'none' && $member->supplier_id) {
            // Keeping current linked customer
            $request->validate([
                'mobile_no' => [
                    Rule::unique('suppliers', 'mobile_no')
                        ->whereNull('deleted_at')
                        ->ignore($member->supplier_id)
                ]
            ], [
                'mobile_no.unique' => 'Another Customer with this mobile number already exists. Cannot update mobile number.'
            ]);
        }

        try {
            DB::beginTransaction();

            $data = $request->except(['photo', 'signature_photo', 'nid_photo_front', 'nid_photo_back', 'document', 'nominees']);
            
            // Format Dates
            if ($request->admission_date) {
                $data['admission_date'] = Carbon::createFromFormat('d-m-Y', $request->admission_date)->format('Y-m-d');
            }
            if ($request->dob) {
                $data['dob'] = Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d');
            }
            // Handle File Uploads
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->uploadImage($request->file('photo'), 'microfinance/member/photos', 300, 300);
            }
            if ($request->hasFile('signature_photo')) {
                $data['signature_photo'] = $this->uploadImage($request->file('signature_photo'), 'microfinance/member/signatures', 300, 100);
            }
            if ($request->hasFile('nid_photo_front')) {
                $data['nid_photo_front'] = $this->uploadImage($request->file('nid_photo_front'), 'microfinance/member/nid', 600);
            }
            if ($request->hasFile('nid_photo_back')) {
                $data['nid_photo_back'] = $this->uploadImage($request->file('nid_photo_back'), 'microfinance/member/nid', 600);
            }
            if ($request->hasFile('document')) {
                $data['document'] = $this->uploadFile($request->file('document'), 'microfinance/member/documents');
            }

            $data = $this->parseAddressFields($data, $request);

            // ── Customer (Supplier) Linking on Update ──────────────────
            $customerOption = $request->input('customer_option');

            if ($customerOption === 'keep_current' || $customerOption === null) {
                if ($member->supplier_id && $member->customer) {
                    $member->customer->update([
                        'name'      => $request->name,
                        'mobile_no' => $request->mobile_no,
                    ]);
                }
            } else {
                $data['supplier_id'] = $this->handleCustomerLink($request, $customerOption);
            }
            // ─────────────────────────────────────────────────────────

            $member->update($data);

            // Handle Nominees: Simplest way is to delete existing and recreate
            // (Note: In a production app, you might want to match IDs to preserve files)
            if ($request->has('nominees')) {
                $member->nominees()->delete();
                foreach ($request->nominees as $key => $nData) {
                    if (empty($nData['name'])) continue;
                    $nomineeData = $nData;
                    
                    // Format DOB
                    if (!empty($nData['dob'])) {
                        $nomineeData['dob'] = date('Y-m-d', strtotime($nData['dob']));
                    }

                    // Handle existing/new photos
                    $nomineeData['photo'] = $nData['old_photo'] ?? null;
                    $nomineeData['signature_photo'] = $nData['old_signature_photo'] ?? null;
                    $nomineeData['nid_photo_front'] = $nData['old_nid_photo_front'] ?? null;
                    $nomineeData['nid_photo_back'] = $nData['old_nid_photo_back'] ?? null;

                    if ($request->hasFile("nominees.$key.photo")) {
                        $nomineeData['photo'] = $this->uploadImage($request->file("nominees.$key.photo"), 'microfinance/nominee/photos', 300, 300);
                    }
                    if ($request->hasFile("nominees.$key.signature_photo")) {
                        $nomineeData['signature_photo'] = $this->uploadImage($request->file("nominees.$key.signature_photo"), 'microfinance/nominee/signatures', 300, 100);
                    }
                    if ($request->hasFile("nominees.$key.nid_photo_front")) {
                        $nomineeData['nid_photo_front'] = $this->uploadImage($request->file("nominees.$key.nid_photo_front"), 'microfinance/nominee/nid', 600);
                    }
                    if ($request->hasFile("nominees.$key.nid_photo_back")) {
                        $nomineeData['nid_photo_back'] = $this->uploadImage($request->file("nominees.$key.nid_photo_back"), 'microfinance/nominee/nid', 600);
                    }

                    unset($nomineeData['old_photo'], $nomineeData['old_signature_photo'], $nomineeData['old_nid_photo_front'], $nomineeData['old_nid_photo_back']);
                    $nomineeData['mf_member_id'] = $member->id;
                    $nomineeData = $this->parseAddressFields($nomineeData, $nData, true);
                    MfNominee::create($nomineeData);
                }
            }

            // Sync member nominees to the auto-opened general savings account
            $savingsService = app(\App\Services\Microfinance\SavingsAccountService::class);
            $savingsService->syncGeneralSavingsNominees($member);

            DB::commit();
            return redirect()->route('microfinance.member.index')->with('success', 'Member updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function toggleStatus(MfMember $member)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member_edit'), 403, 'Unauthorized');
        abort_if($member->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $member->update(['status' => $member->status == 1 ? 0 : 1]);
        $msg = $member->status == 1 ? 'Member activated.' : 'Member deactivated.';
        return response()->json(['success' => true, 'message' => $msg, 'status' => $member->status]);
    }

    public function transfer(Request $request, MfMember $member)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_member_edit'), 403, 'Unauthorized');
        abort_if($member->sister_concern_id !== auth()->user()->activeConcernId(), 403);

        $request->validate([
            'somiti_id' => 'required|exists:mf_somitis,id',
        ]);

        $newSomiti = MfSomiti::where('id', $request->somiti_id)
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('status', 1)
            ->firstOrFail();

        if ($newSomiti->id === $member->somiti_id) {
            return response()->json(['success' => false, 'message' => 'Member is already in this somiti.'], 422);
        }

        $member->update(['somiti_id' => $newSomiti->id]);

        return response()->json(['success' => true, 'message' => 'Member transferred to ' . $newSomiti->name . '.']);
    }

    private function parseAddressFields($data, $source, $isNominee = false)
    {
        $prefixes = $isNominee ? [''] : ['present_', 'permanent_'];
        
        foreach ($prefixes as $prefix) {
            $key = $prefix . 'union_id';
            if (isset($source[$key])) {
                $value = $source[$key];
                if (strpos($value, '_') !== false) {
                    list($type, $id) = explode('_', $value);
                    if ($type == 'union') {
                        $data[$prefix . 'union_id'] = $id;
                        $data[$prefix . 'pourashava_id'] = null;
                    } elseif ($type == 'pourashava') {
                        $data[$prefix . 'pourashava_id'] = $id;
                        $data[$prefix . 'union_id'] = null;
                    }
                } else {
                    $data[$prefix . 'union_id'] = $value;
                    $data[$prefix . 'pourashava_id'] = null;
                }
            }
        }
        return $data;
    }
}
