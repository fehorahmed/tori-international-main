<?php

namespace App\Http\Controllers\Sales\Service;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Inventory\Inventory;
use App\Models\Sales\Service\Service;
use App\Models\Sales\Service\ServiceUtilizedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TechnicianWorkController extends Controller
{
    public function index()
    {
        return view('sales.service.panel.index');
    }

    public function dataTable()
    {
        $technicianId = auth()->user()->supplier_id;

        $query = Service::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('technician_id', $technicianId)
            ->with('customer', 'saleOrder');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Service $service) {
                $btn = '';
                if ($service->status < 3) { // Pending or In Progress
                    $btn .= ' <a href="' . route('service.panel.update_form', ['service' => $service->id]) . '" class="btn btn-info bg-gradient-info btn-sm">Update Work</a>';
                }
                $btn .= ' <a href="' . route('service.request.details', ['service' => $service->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-info-circle"></i></a>';
                return $btn;
            })
            ->addColumn('customer_name', function (Service $service) {
                return $service->customer->name ?? '';
            })
            ->addColumn('order_no', function (Service $service) {
                return $service->saleOrder->order_no ?? '';
            })
            ->addColumn('status_text', function (Service $service) {
                $status = [
                    1 => '<span class="badge badge-warning">Pending</span>',
                    2 => '<span class="badge badge-info">In Progress</span>',
                    3 => '<span class="badge badge-primary">Waiting for Approval</span>',
                    4 => '<span class="badge badge-success">Completed</span>',
                    5 => '<span class="badge badge-secondary">Delivered</span>',
                ];
                return $status[$service->status] ?? '';
            })
            ->rawColumns(['action', 'status_text'])
            ->toJson();
    }

    public function updateForm(Service $service)
    {
        abort_if(!auth()->user()->hasPermissionTo('service_panel_update'), 403, 'Unauthorized');

        if ($service->technician_id != auth()->user()->supplier_id) {
            abort(403, 'Unauthorized');
        }

        $service->load('serviceItems.product', 'serviceUtilizedItems.product');
        
        // Mark as In Progress if Pending
        if ($service->status == 1) {
            $service->update(['status' => 2]);
        }

        $paymentMethods = AccountHead::whereIn('payment_mode', [1, 2])
            ->get();

        return view('sales.service.panel.update_work', compact('service', 'paymentMethods'));
    }

    public function submitWork(Service $service, Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('service_panel_update'), 403, 'Unauthorized');

        if ($service->technician_id != auth()->user()->supplier_id) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'service_charge' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_account_head_id' => 'required|exists:account_heads,id',
            'product_id' => 'nullable|array',
            'quantity' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // Update Service
            $service->service_charge = $request->service_charge;
            $service->paid_amount = $request->paid_amount;
            $service->payment_account_head_id = $request->payment_account_head_id;
            $service->notes = $request->notes;
            $service->status = 3; // Waiting for Approval
            $service->save();

            // Clear existing utilized items if any (re-submission)
            ServiceUtilizedItem::where('service_id', $service->id)->delete();

            if ($request->product_id) {
                foreach ($request->product_id as $key => $productId) {
                    $inventoryId = $request->inventory_id[$key];
                    $qty = $request->quantity[$key];
                    
                    $inventory = Inventory::find($inventoryId);
                    if (!$inventory || $inventory->quantity < $qty) {
                        throw new \Exception("Insufficient stock for part: " . ($inventory->product->name ?? 'Unknown'));
                    }

                    $utilizedItem = new ServiceUtilizedItem();
                    $utilizedItem->service_id = $service->id;
                    $utilizedItem->product_id = $productId;
                    $utilizedItem->inventory_id = $inventoryId;
                    $utilizedItem->quantity = $qty;
                    $utilizedItem->unit_price = $inventory->unit_price; // Fetching current cost price
                    $utilizedItem->save();
                }
            }

            DB::commit();
            return redirect()->route('service.panel.index')->with('success', 'Work submitted for approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}
