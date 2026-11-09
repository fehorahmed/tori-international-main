<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AccountGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('accounts.account_group.index');
    }
    public function dataTable()
    {
        $query = AccountGroup::select('account_groups.*')->with('accountGroup');
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(AccountGroup $accountGroup) {
                $btn = '';
                if($accountGroup->is_editable && auth()->user()->can('account_group_edit')){
                    $btn .= '<a href="'.route('account-group.edit',['account_group'=>$accountGroup->id]).'" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if($accountGroup->is_editable && auth()->user()->can('account_group_delete')){
                    $btn .= ' <a role="button" data-id="'.$accountGroup->id.'" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;
            })
            ->addColumn('account_group_name', function(AccountGroup $accountGroup) {
                return $accountGroup->accountGroup->name ?? '';
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(403);
        $accountGroups = AccountGroup::all();
        return view('accounts.account_group.create',compact('accountGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(403);

        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('account_groups'),
            ],
            'account_group'=>'nullable',
            'note_no'=>'nullable|numeric',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $validatedData['account_group_id'] = $request->account_group;
            unset($validatedData['account_group']);
            // Create a new Product record in the database
            AccountGroup::create($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('account-group.index')->with('success', 'Account group created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('account-group.create')->withInput()
                ->with('error', 'An error occurred while creating the Account group: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountGroup $account_group)
    {
        abort(403);
        try {
            $accountGroups = AccountGroup::all();
            // If the Product exists, display the edit view
            return view('accounts.account_group.edit', compact('account_group','accountGroups'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Account group is not found
            return redirect()->route('account-group.index')->with('error', 'Account group  not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,AccountGroup $account_group)
    {
        abort(403);
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('account_groups')
                    ->ignore($account_group),
            ],
            'account_group'=>'nullable',
            'note_no'=>'nullable|numeric',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $validatedData['account_group_id'] = $request->account_group;
            unset($validatedData['account_group']);
            // Update the $account_group record in the database
            $account_group->update($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('account-group.index')->with('success', 'Account group updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('account-group.edit',['account_group'=>$account_group->id])->with('error', 'An error occurred while updating the Unit: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountGroup $account_group)
    {
        abort(403);
        try {
            $accountHead = AccountHead::where('account_group_id',$account_group->id)->first();
            if ($accountHead) {
                // If a related Supplier exists, return an error message
                return response()->json(['success' => false, 'message' => 'It has account head logs. Cannot delete.'], Response::HTTP_OK);
            }
            // Delete the Product record
            $account_group->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Account group deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Account group not found: '.$e->getMessage()], Response::HTTP_NOT_FOUND);

        }
    }
}
