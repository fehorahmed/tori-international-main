@foreach ($saleItems as $index => $item)
    <tr class="product-item">
        <td class="text-center">
            <span class="product_sl">{{ $index+1 }}</span>
        </td>
        <td class="text-left">
            <span class="product_name">{{ $item->product->name??'' }}</span>
            <input type="hidden" name="product_name_val[]" value="{{ $item->product->name??'' }}" class="product_name_val">
            <input type="hidden" name="product_id[]" value="{{ $item->product->id??'' }}" class="product_id">
            <input type="hidden" name="product_code[]" value="{{ $item->product->name??'' }}" class="product_code">
            <input type="hidden" name="inventory_id[]" value="{{ $item->inventory->id??'' }}" class="inventory_id">
            <input type="hidden" name="sale_order_item_id[]" value="{{ $item->id }}" class="sale_order_item_id">
        </td>
        <td class="text-right">
            {{ $item->quantity }}
            <input type="hidden" value="{{ $item->quantity }}" name="order_quantity[]" class="order_quantity">
        </td>
        <td class="text-right"> 
            <div class="form-group mb-0">
                <input type="number" step="any" value="0" class="form-control text-right return_quantity" name="return_quantity[]">
            </div>
        </td>
        <td class="text-right">
            {{ $item->selling_unit_price }}
            <input type="hidden" value="{{ $item->selling_unit_price }}" name="product_unit_price[]" class="product_unit_price">
        </td>
        <td class="text-right total-sale-cost"></td>
        <td class="text-right total-return-cost"></td>
        <td class="text-center">
            {{-- <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i
                    class="fa fa-trash-alt"></i></button> --}}
        </td>
    </tr>
@endforeach
