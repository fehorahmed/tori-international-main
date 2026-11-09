<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\SupplierType;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Sales\SaleOrder;
use App\Models\Supplier;
use App\Models\Division;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use App\Models\Nominee;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Models\ShomriddhiAdvance\ShomriddhiGroup;

class MemberController extends Controller
{
    use UploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $divisions = Division::orderBy('name')->get();
        $groups = ShomriddhiGroup::where('status', 1)->orderBy('code')->get();
        return view('shomriddhi_advance.members.index', compact('divisions', 'groups'));
    }

    public function printEmptyForm()
    {
        if (!auth()->user()->hasPermissionTo('customer_create')) {
            abort(403, 'Unauthorized');
        }
        $pageTitle = 'Customer Data Collection Form (Blank)';
        return view('shomriddhi_advance.members.print_empty_form', compact('pageTitle'));
    }
    public function dataTable(Request $request)
    {
        $query = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', SupplierType::$CUSTOMER)
            ->with('division', 'district', 'upazila', 'union', 'shomriddhiGroup')
            ->orderBy('id', 'desc');

        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }
        if ($request->upazila_id) {
            $query->where('upazila_id', $request->upazila_id);
        }
        if ($request->union_id) {
            if (strpos($request->union_id, 'union_') !== false) {
                $query->where('union_id', str_replace('union_', '', $request->union_id));
            } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                $query->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
            }
        }
        if ($request->village_id) {
            $query->where('village_id', $request->village_id);
        }
        if ($request->shomriddhi_group_id) {
            $query->where('shomriddhi_group_id', $request->shomriddhi_group_id);
        }
        if ($request->shomriddhi_group_id) {
            $query->where('shomriddhi_group_id', $request->shomriddhi_group_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Supplier $supplier) {
                $btn = '';
                $btn .= '<a href="' . route('shomriddhi-advance.members.show', ['member' => $supplier->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-eye"></i></a>';
                
                $hasEdit = auth()->user()->can('customer_edit');
                $hasDelete = auth()->user()->can('customer_delete');
                $isOrigin = auth()->user()->activeConcernId() == $supplier->origin_sister_concern_id;

                if ($hasEdit || $hasDelete) {
                    if ($isOrigin) {
                        if ($hasEdit) {
                            $btn .= ' <a href="' . route('shomriddhi-advance.members.edit', ['member' => $supplier->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                        }
                        if ($hasDelete) {
                            if ($supplier->hasShomriddhiAccount()) {
                                $btn .= ' <button type="button" class="btn btn-secondary btn-sm" disabled title="Locked: Shomriddhi Account exists"><i class="fa fa-lock"></i></button>';
                            } else {
                                $btn .= ' <a role="button" data-id="' . $supplier->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                            }
                        }
                    } else {
                        $btn .= ' <button type="button" class="btn btn-secondary btn-sm" disabled title="Locked: Created by another branch"><i class="fa fa-lock"></i></button>';
                    }
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('group_name', function(Supplier $supplier) {
                return $supplier->shomriddhiGroup ? $supplier->shomriddhiGroup->code . ' - ' . $supplier->shomriddhiGroup->name_en : '—';
            })
            ->addColumn('member_image', function(Supplier $supplier) {
                return renderZoomGalleryImage($supplier->image, $supplier->name);
            })
            ->addColumn('address', function(Supplier $supplier) {
                return '<button class="btn btn-info btn-sm btn-view-address" data-id="'.$supplier->id.'">View Address</button>';
            })
            ->rawColumns(['action','address','member_image'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('customer_create')) {
            abort(403, 'Unauthorized');
        }
        $divisions = Division::orderBy('name')->get();
        $groups = ShomriddhiGroup::where('status', 1)->orderBy('code')->get();
        return view('shomriddhi_advance.members.create', compact('divisions', 'groups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('customer_create')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'father_name' =>['nullable','max:255'],
            'mother_name' =>['nullable','max:255'],
            'spouse_name' =>['nullable','max:255'],
            'gender' =>['nullable','in:Male,Female,Other'],
            'blood_group' =>['nullable','in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'nid_number' =>['nullable','max:255'],
            'dob' =>['nullable','date_format:d-m-Y,Y-m-d'],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('suppliers')
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
            ],
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'union' => 'nullable',
            'village_id' => 'nullable|integer',
            'shomriddhi_group_id' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Supplier record in the database
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['type'] = SupplierType::$CUSTOMER;
            $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$CUSTOMER);
            $validatedData['division_id'] = $request->division;
            $validatedData['district_id'] = $request->district;
            $validatedData['upazila_id'] = $request->upazila;
            $validatedData['shomriddhi_group_id'] = $request->shomriddhi_group_id;

            if ($request->union) {
                if (strpos($request->union, 'union_') !== false) {
                    $validatedData['union_id'] = str_replace('union_', '', $request->union);
                } elseif (strpos($request->union, 'pourashava_') !== false) {
                    $validatedData['pourashava_id'] = str_replace('pourashava_', '', $request->union);
                }
            }
            $validatedData['village_id'] = $request->village_id;
            $validatedData['status'] = 1;
            if($request->dob) {
                $validatedData['dob'] = \Carbon\Carbon::parse($request->dob)->format('Y-m-d');
            }
            $validatedData['password'] = Hash::make($request->mobile_no);

            if ($request->hasFile('image')) {
                $validatedData['image'] = $this->uploadImage($request->file('image'), 'customer');
            }

            unset($validatedData['division'], $validatedData['district'], $validatedData['upazila'], $validatedData['union']);
            unset($validatedData['opening_balance']);
            $member = Supplier::create($validatedData);

            // Create a new Account Head record in the database
            $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)->first();

            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id',$member->id)->first();
            if (!$accountHead){
                $code = generateAccountCode($receivablesGroup->top_reserved_id);
                $accountHead = new AccountHead();
                $accountHead->sister_concern_id = null;
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = $receivablesGroup->id;
            $accountHead->supplier_id = $member->id;
            $accountHead->name = $request->name.'(Client)';

            $accountHead->nature = 'Debit';
            $accountHead->is_system = 1;
            $accountHead->is_control_account = 0;
            $accountHead->is_editable = 0;
            $accountHead->save();

            if ($request->nominee_name) {
                $nomineeImagePath = null;
                if ($request->hasFile('nominee_image')) {
                    $nomineeImagePath = $this->uploadImage($request->file('nominee_image'), 'nominee');
                }

                $nomineeUnionId = null;
                $nomineePourashavaId = null;
                if ($request->nominee_union) {
                    if (strpos($request->nominee_union, 'union_') !== false) {
                        $nomineeUnionId = str_replace('union_', '', $request->nominee_union);
                    } elseif (strpos($request->nominee_union, 'pourashava_') !== false) {
                        $nomineePourashavaId = str_replace('pourashava_', '', $request->nominee_union);
                    }
                }

                Nominee::create([
                    'customer_id' => $member->id,
                    'name'        => $request->nominee_name,
                    'relation'    => $request->nominee_relation,
                    'nid_number'  => $request->nominee_nid,
                    'phone'       => $request->nominee_phone,
                    'image'       => $nomineeImagePath,
                    'division_id' => $request->nominee_division,
                    'district_id' => $request->nominee_district,
                    'upazila_id'  => $request->nominee_upazila,
                    'union_id'    => $nomineeUnionId,
                    'pourashava_id' => $nomineePourashavaId,
                    'village_id'  => $request->nominee_village_id,
                ]);
            }

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('shomriddhi-advance.members.index')->with('success', 'Client created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('shomriddhi-advance.members.create')
                ->withInput()
                ->with('error', 'An error occurred while creating the Customer: '.$e->getMessage());
        }
    }
    public function storeDirect(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('pos')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $validatedData = $request->validate([
            'customer_name' =>[
                'required','max:255'
            ],
            'mobile_no' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
            ],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Supplier record in the database
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['type'] = SupplierType::$CUSTOMER;
            $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$CUSTOMER);
            $validatedData['status'] = 1;
            $validatedData['opening_balance'] = 0;
            $validatedData['name'] = $validatedData['customer_name'];
            $validatedData['password'] = Hash::make($request->mobile_no);
            unset($validatedData['customer_name']);
            $member = Supplier::create($validatedData);

            $member_info = $member->name;
            if ($member->mobile_no != ''){
                $member_info .= ' - '.$member->mobile_no;
            }
            $member->customer_info = $member_info;

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return response()->json([
                'status'=>true,
                'customer'=>$member,
                'message'=>'Customer created successfully',
            ]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage(),
            ]);
        }
    }

    public function show(Supplier $member)
    {
        if ($member->type != SupplierType::$CUSTOMER) {
            abort(404);
        }
        $member->load('division', 'district', 'upazila', 'union', 'pourashava');
        $nominee = Nominee::where('customer_id', $member->id)->first();
        return view('shomriddhi_advance.members.show', compact('member', 'nominee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $member)
    {
        if (!auth()->user()->hasPermissionTo('customer_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($member->type != SupplierType::$CUSTOMER) {
            abort(404);
        }
        if (auth()->user()->activeConcernId() != $member->origin_sister_concern_id) {
            abort(403, 'You can only edit clients created by your branch.');
        }

        try {
            $divisions = Division::orderBy('name')->get();
            $groups = ShomriddhiGroup::where('status', 1)->orderBy('code')->get();
            return view('shomriddhi_advance.members.edit', compact('member', 'divisions', 'groups'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Supplier is not found
            return redirect()->route('shomriddhi-advance.members.index')->with('error', 'Client not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $member)
    {
        if (!auth()->user()->hasPermissionTo('customer_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($member->type != SupplierType::$CUSTOMER) {
            abort(404);
        }
        if (auth()->user()->activeConcernId() != $member->origin_sister_concern_id) {
            abort(403, 'You can only edit clients created by your branch.');
        }

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'father_name' =>['nullable','max:255'],
            'mother_name' =>['nullable','max:255'],
            'spouse_name' =>['nullable','max:255'],
            'gender' =>['nullable','in:Male,Female,Other'],
            'blood_group' =>['nullable','in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'nid_number' =>['nullable','max:255'],
            'dob' =>['nullable','date_format:d-m-Y,Y-m-d'],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('suppliers')
                    ->ignore($member)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
                    ->ignore($member)
            ],
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'union' => 'nullable',
            'village_id' => 'nullable|integer',
            'shomriddhi_group_id' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the Supplier record in the database
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['type'] = SupplierType::$CUSTOMER;
            if ($member->member_no == null){
                $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$CUSTOMER);
            }
            $validatedData['division_id'] = $request->division;
            $validatedData['district_id'] = $request->district;
            $validatedData['upazila_id'] = $request->upazila;
            $validatedData['shomriddhi_group_id'] = $request->shomriddhi_group_id;
            if ($request->union) {
                if (strpos($request->union, 'union_') !== false) {
                    $validatedData['union_id'] = str_replace('union_', '', $request->union);
                    $validatedData['pourashava_id'] = null;
                } elseif (strpos($request->union, 'pourashava_') !== false) {
                    $validatedData['pourashava_id'] = str_replace('pourashava_', '', $request->union);
                    $validatedData['union_id'] = null;
                }
            } else {
                $validatedData['union_id'] = null;
                $validatedData['pourashava_id'] = null;
            }
            $validatedData['village_id'] = $request->village_id;
            if($request->dob) {
                $validatedData['dob'] = \Carbon\Carbon::parse($request->dob)->format('Y-m-d');
            }

            if ($request->hasFile('image')) {
                $this->deleteFile($member->image);
                $validatedData['image'] = $this->uploadImage($request->file('image'), 'customer');
            }

            unset($validatedData['division'], $validatedData['district'], $validatedData['upazila'], $validatedData['union']);
            unset($validatedData['opening_balance']);

            $member->update($validatedData);

            // Create/update Account Head record
            $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)
                ->first();

            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $member->id)->first();
            if (!$accountHead) {
                $code = generateAccountCode($receivablesGroup->top_reserved_id);
                $accountHead = new AccountHead();
                $accountHead->sister_concern_id = null;
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = $receivablesGroup->id;
            $accountHead->supplier_id = $member->id;
            $accountHead->name = $request->name.'(Client)';

            $accountHead->nature = 'Debit';
            $accountHead->is_system = 1;
            $accountHead->is_control_account = 0;
            $accountHead->is_editable = 0;
            $accountHead->save();

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('shomriddhi-advance.members.index')->with('success', 'Client updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('shomriddhi-advance.members.edit', ['member' => $member->id])
                ->withInput()
                ->with('error', 'An error occurred while updating the Customer: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $member)
    {
        try {
            if (!auth()->user()->hasPermissionTo('customer_delete')) {
                abort(403, 'Unauthorized');
            }
            if (auth()->user()->activeConcernId() != $member->origin_sister_concern_id) {
                return response()->json(['success' => false, 'message' => 'You can only delete clients created by your branch.'], Response::HTTP_OK);
            }
            
            if ($member->hasShomriddhiAccount()) {
                return response()->json(['success' => false, 'message' => 'Has Shomriddhi Account. Cannot delete.'], Response::HTTP_OK);
            }
            
            // Check for Sales
            if (SaleOrder::where('customer_id', $member->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Has sales records. Cannot delete.'], Response::HTTP_OK);
            }

            // Check for Accounting Transactions
            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $member->id)->first();
            if ($accountHead && Transaction::where('account_head_id', $accountHead->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Has accounting transactions. Cannot delete.'], Response::HTTP_OK);
            }

            $accountSavingsHead = AccountHead::where('head_type', 'savings')->where('supplier_id', $member->id)->first();
            if ($accountSavingsHead && Transaction::where('account_head_id', $accountSavingsHead->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Has savings transactions. Cannot delete.'], Response::HTTP_OK);
            }

            DB::beginTransaction();
            if ($accountHead) {
                AccountHead::where('supplier_id', $member->id)->delete();
            }
            $member->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Client deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], Response::HTTP_OK);
        }
    }
}
