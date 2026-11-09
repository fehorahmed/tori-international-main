<?php

namespace App\Http\Controllers;

use App\Models\Account\AccountHead;
use App\Models\Attachment;
use App\Models\Crm\Market;
use App\Models\Inventory\Inventory;
use App\Models\Settings\Product;
use App\Models\Settings\Category;
use App\Models\Sales\SaleOrder;
use App\Models\Supplier;
use App\Enumeration\SupplierType;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use App\Models\Pourashava;
use App\Models\UserManagement\UserDeviceToken;
use App\Models\Village;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CommonController extends Controller
{

    public function getStockInfo(Request $request)
    {
        $inventory = Inventory::with('product')
                    ->where('sister_concern_id',auth()->user()->activeConcernId())
                    ->where('warehouse_id',$request->warehouseId)
                    ->where('product_id',$request->product_id)
                    ->where('quantity','>',0)
                    ->first();
        $product = Product::with('unit')
                ->where('id',$request->product_id)
                ->first();
        if ($inventory){
            $inventory->selling_price = $inventory->product->selling_price ?? 0;
            $inventory->wholesale_price = $inventory->product->wholesale_price ?? 0;
            return response()->json([
                'status'=>true,
                'inventory'=>$inventory,
                'product'=>$product,
            ]);
        }else{
            return response()->json([
                'status'=>false,
                'message'=>'No Available Stock',
            ]);
        }


    }
    public function getSalesOrders(Request $request)
    {
        $salesOrders = SaleOrder::with('customer')
                    ->where('due','>',0)
                    ->where('distribution_order_id',$request->orderId)
                    ->get();

        return response()->json($salesOrders);
    }
    public function getSalesOrderDetails(Request $request)
    {
        $salesOrder = SaleOrder::where('id',$request->orderId)
                    ->first();

        return response()->json($salesOrder);
    }

    public function getAccountHead(Request $request)
    {
        $accountHead = AccountHead::where('id',$request->id)->first();
        return response()->json($accountHead);
    }
    public function getProductDetails(Request $request)
    {
        $product = Product::where('id',$request->id)
            ->with(['inventory'=>function($query){
                $query->where('sister_concern_id',auth()->user()->activeConcernId());
            }])->first();
        return response()->json($product);
    }
    public function getProductBatch(Request $request){
        $batches = Inventory::with('product')
                        ->where('sister_concern_id',auth()->user()->activeConcernId())                
                        ->where('product_id',$request->id)
                        ->where('warehouse_id',$request->warehouseId)
                        ->where('quantity','>',0)
                        ->orderBy('batch_no')
                        ->get();

        $batches->map(function($batch) {
            $batch->selling_price = $batch->product->selling_price ?? 0;
            $batch->wholesale_price = $batch->product->wholesale_price ?? 0;
            return $batch;
        });

        return response()->json($batches);                
    }
    public function getProductStockDetails(Request $request)
    {
        $inventory = Inventory::with('product')->where('id',$request->inventoryId)->first();
        if ($inventory) {
            $inventory->selling_price = $inventory->product->selling_price ?? 0;
            $inventory->wholesale_price = $inventory->product->wholesale_price ?? 0;
        }
        return response()->json([
            'status' => (bool)$inventory,
            'inventory' => $inventory,
            'message' => $inventory ? '' : 'Stock details not found'
        ]);
    }

    public function attachmentDelete(Request $request)
    {
        try {

            $attachment = Attachment::where('id',$request->id)->first();
            if (file_exists($attachment->file)){
                unlink($attachment->file);
            }

            $attachment->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Attachment deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' =>$e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
    public function attachmentTitleUpdate(Request $request)
    {

        try {
            $attachment = Attachment::where('id',$request->id)->first();
            $attachment->title = $request->title;
            $attachment->save();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Attachment title updated successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Attachment not found: '.$e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
    public function getSisterConcernWiseSupplier(Request $request)
    {
        $suppliers = Supplier::whereNotNull('id')->where('type',1)->get();
        return response()->json($suppliers);
    }
    public function getSisterConcernWiseCustomer(Request $request)
    {
        $customers = Supplier::whereNotNull('id')->where('type',2)->get();
        return response()->json($customers);
    }
    public function getFilteredCustomers(Request $request)
    {
        $query = Supplier::whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$CORPORATE_CUSTOMER, SupplierType::$DEALER])
            ->where('status', 1)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

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

        $customers = $query->orderBy('name')->get();
        return response()->json($customers);
    }
    public function getFilteredSuppliers(Request $request)
    {
        $query = Supplier::where('type', SupplierType::$SUPPLIER)
            ->where('status', 1)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

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

        $suppliers = $query->orderBy('name')->get();
        return response()->json($suppliers);
    }
    public function getDistricts(Request $request)
    {
        $divisionId = $request->division_id ?? $request->divisionId;
        $districts = District::where('division_id', $divisionId)->orderBy('name')->get();
        return response()->json($districts);
    }
    public function getUpazilas(Request $request)
    {
        $districtId = $request->district_id ?? $request->districtId;
        $upazilas = Upazila::where('district_id', $districtId)->orderBy('name')->get();
        return response()->json($upazilas);
    }
    public function getUnions(Request $request)
    {
        $upazilaId = $request->upazila_id ?? $request->upazilaId;
        $unions = Union::where('upazila_id', $upazilaId)->orderBy('name')->get();
        return response()->json($unions);
    }
    public function getMarkets(Request $request)
    {
        $upazilaId = $request->upazila_id ?? $request->upazilaId;
        $markets = Market::where('upazila_id', $upazilaId)->orderBy('name')->get();
        return response()->json($markets);
    }
    public function getPourashavas(Request $request)
    {
        $districtId = $request->district_id ?? $request->districtId;
        $pourashavas = Pourashava::where('district_id', $districtId)->orderBy('name')->get();
        return response()->json($pourashavas);
    }
    public function getVillages(Request $request)
    {
        $unionId = $request->union_id ?? $request->unionId;
        $pourashavaId = $request->pourashava_id ?? $request->pourashavaId;

        // villages table à¦ pourashava_id column à¦¨à§‡à¦‡, à¦¤à¦¾à¦‡ pourashava select à¦¹à¦²à§‡ empty return
        if ($pourashavaId && !$unionId) {
            return response()->json([]);
        }

        if (!$unionId) {
            return response()->json([]);
        }

        $villages = Village::where('union_id', $unionId)
            ->orderBy('name')
            ->get()
            ->map(function ($village) {
                if ($village->bn_name) {
                    $village->name = $village->name . ' (' . $village->bn_name . ')';
                }
                return $village;
            });

        return response()->json($villages);
    }
    public function getCategoryGroupWiseProduct(Request $request)
    {
        $products = Product::where('status', 1)
            ->where('product_type', 1)
            ->get();
        return response()->json($products);
    }
    public function getCategoryGroupWiseCategory(Request $request)
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function getSupplierAddressDetails(Request $request)
    {
        $supplier = Supplier::with('division', 'district', 'upazila', 'union', 'pourashava', 'village')
            ->find($request->id);

        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Supplier not found']);
        }

        $html = '<table class="table table-sm table-borderless">';
        $html .= '<tr><th width="35%"><i class="fa fa-map-marker-alt text-primary mr-2"></i> Division</th><td>: ' . ($supplier->division->name ?? '') . '</td></tr>';
        $html .= '<tr><th><i class="fa fa-city text-info mr-2"></i> District</th><td>: ' . ($supplier->district->name ?? '') . '</td></tr>';
        $html .= '<tr><th><i class="fa fa-building text-warning mr-2"></i> Upazila</th><td>: ' . ($supplier->upazila->name ?? '') . '</td></tr>';

        if ($supplier->pourashava) {
            $html .= '<tr><th><i class="fa fa-landmark text-success mr-2"></i> Pourashava</th><td>: ' . ($supplier->pourashava->name ?? '') . '</td></tr>';
        }
        if ($supplier->union) {
            $html .= '<tr><th><i class="fa fa-users text-purple mr-2"></i> Union</th><td>: ' . ($supplier->union->name ?? '') . '</td></tr>';
        }

        $html .= '<tr><th><i class="fa fa-home text-success mr-2"></i> Village</th><td>: ' . ($supplier->village->name ?? 'N/A') . '</td></tr>';
        $html .= '</table>';

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function getEmployeeAddressDetails(Request $request)
    {
        $employee = \App\Models\Hrm\Employee::with('currentDivision', 'currentDistrict', 'currentUpazila', 'currentUnion', 'currentPourashava', 'currentVillage', 'permanentDivision', 'permanentDistrict', 'permanentUpazila', 'permanentUnion', 'permanentPourashava', 'permanentVillage')
            ->find($request->id);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found']);
        }

        $html = '<div class="row">';
        
        // Current Address Column
        $html .= '<div class="col-md-6 mb-3">';
        $html .= '<div class="card shadow-sm border-info" style="border-radius: 10px;">';
        $html .= '<div class="card-header bg-info text-white" style="border-radius: 10px 10px 0 0;">';
        $html .= '<h6 class="mb-0 font-weight-bold"><i class="fa fa-map-marker-alt mr-2"></i> Current Address</h6>';
        $html .= '</div>';
        $html .= '<div class="card-body p-3 bg-light" style="border-radius: 0 0 10px 10px;">';
        $html .= '<table class="table table-sm table-borderless mb-0 text-dark">';
        $html .= '<tr><th width="40%" class="text-muted"><i class="fa fa-map text-info mr-1"></i> Division</th><td class="font-weight-bold">: ' . ($employee->currentDivision->name ?? '<span class="text-danger">N/A</span>') . '</td></tr>';
        $html .= '<tr><th class="text-muted"><i class="fa fa-city text-primary mr-1"></i> District</th><td class="font-weight-bold">: ' . ($employee->currentDistrict->name ?? '<span class="text-danger">N/A</span>') . '</td></tr>';
        $html .= '<tr><th class="text-muted"><i class="fa fa-building text-warning mr-1"></i> Upazila</th><td class="font-weight-bold">: ' . ($employee->currentUpazila->name ?? '<span class="text-danger">N/A</span>') . '</td></tr>';
        
        if ($employee->currentPourashava) {
            $html .= '<tr><th class="text-muted"><i class="fa fa-landmark text-success mr-1"></i> Pourashava</th><td class="font-weight-bold">: ' . ($employee->currentPourashava->name ?? '') . '</td></tr>';
        }
        if ($employee->currentUnion) {
            $html .= '<tr><th class="text-muted"><i class="fa fa-users text-purple mr-1"></i> Union</th><td class="font-weight-bold">: ' . ($employee->currentUnion->name ?? '') . '</td></tr>';
        }
        
        $html .= '<tr><th class="text-muted"><i class="fa fa-home text-danger mr-1"></i> Village</th><td class="font-weight-bold">: ' . ($employee->currentVillage->name ?? '<span class="text-danger">N/A</span>') . '</td></tr>';
        $html .= '</table>';
        $html .= '</div></div></div>';

        // Permanent Address Column
        $html .= '<div class="col-md-6 mb-3">';
        $html .= '<div class="card shadow-sm border-success" style="border-radius: 10px;">';
        $html .= '<div class="card-header bg-success text-white" style="border-radius: 10px 10px 0 0;">';
        $html .= '<h6 class="mb-0 font-weight-bold"><i class="fa fa-home mr-2"></i> Permanent Address</h6>';
        $html .= '</div>';
        $html .= '<div class="card-body p-3 bg-light" style="border-radius: 0 0 10px 10px;">';
        $html .= '<table class="table table-sm table-borderless mb-0 text-dark">';
        $html .= '<tr><th width="40%" class="text-muted"><i class="fa fa-map text-info mr-1"></i> Division</th><td class="font-weight-bold">: ' . ($employee->permanentDivision->name ?? '<span class="text-danger">N/A</span>') . '</td></tr>';
        $html .= '<tr><th class="text-muted"><i class="fa fa-city text-primary mr-1"></i> District</th><td class="font-weight-bold">: ' . ($employee->permanentDistrict->name ?? '<span class="text-danger">N/A</span>') . '</td></tr>';
        $html .= '<tr><th class="text-muted"><i class="fa fa-building text-warning mr-1"></i> Upazila</th><td class="font-weight-bold">: ' . ($employee->permanentUpazila->name ?? '<span class="text-danger">N/A</span>') . '</td></tr>';
        
        if ($employee->permanentPourashava) {
            $html .= '<tr><th class="text-muted"><i class="fa fa-landmark text-success mr-1"></i> Pourashava</th><td class="font-weight-bold">: ' . ($employee->permanentPourashava->name ?? '') . '</td></tr>';
        }
        if ($employee->permanentUnion) {
            $html .= '<tr><th class="text-muted"><i class="fa fa-users text-purple mr-1"></i> Union</th><td class="font-weight-bold">: ' . ($employee->permanentUnion->name ?? '') . '</td></tr>';
        }
        
        $html .= '<tr><th class="text-muted"><i class="fa fa-home text-danger mr-1"></i> Village</th><td class="font-weight-bold">: ' . ($employee->permanentVillage->name ?? '<span class="text-danger">N/A</span>') . '</td></tr>';
        $html .= '</table>';
        $html .= '</div></div></div>';

        $html .= '</div>'; // End Row

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function getSubCategories(Request $request)
    {
        $subCategories = \App\Models\Settings\SubCategory::where('category_id', $request->categoryId)
            ->where('status', 1)
            ->get();
        return response()->json($subCategories);
    }

    public function ckeditorUpload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();

            // File name unique kora
            $fileName = $fileName . '_' . time() . '.' . $extension;

            // Public folder-e move kora
            $request->file('upload')->move(public_path('media'), $fileName);

            $url = asset('media/' . $fileName);

            return response()->json([
                'fileName' => $fileName,
                'uploaded' => 1,
                'url' => $url
            ]);
        }
    }

    public function saveDeviceToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string',
        ]);

        if (auth()->check()) {
            UserDeviceToken::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'token' => $request->token,
                ],
                [
                    'device_type' => $request->device_type ?? 'web',
                ]
            );
            return response()->json(['status' => true, 'message' => 'Token saved successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
    }

    public function firebaseMessagingSw()
    {
        $setting = \App\Models\Hrm\HrmSetting::first();
        if (!$setting || !$setting->fcm_api_key) {
            return response('console.log("Firebase is not configured");', 200)
                ->header('Content-Type', 'application/javascript');
        }

        $js = "
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

firebase.initializeApp({
    apiKey: '{$setting->fcm_api_key}',
    authDomain: '{$setting->fcm_auth_domain}',
    projectId: '{$setting->fcm_project_id}',
    storageBucket: '{$setting->fcm_storage_bucket}',
    messagingSenderId: '{$setting->fcm_messaging_sender_id}',
    appId: '{$setting->fcm_app_id}'
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/themes/backend/dist/img/AdminLTELogo.png'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
        ";

        return response($js, 200)->header('Content-Type', 'application/javascript');
    }
}
