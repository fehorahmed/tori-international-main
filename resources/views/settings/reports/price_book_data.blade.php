<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">
                    <input type="checkbox" id="selectAll" checked>
                </th>
                <th>S/L</th>
                <th>Image</th>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Category</th>
                <th class="text-right">DP</th>
                <th class="text-right">MRP</th>
                <th>Program</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-checkbox" checked>
                    </td>
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-center">
                        @if($product->image && file_exists($product->image))
                            <img src="{{ asset($product->image) }}" alt="" style="height: 40px; width: 40px; object-fit: cover; border-radius: 4px;">
                        @else
                            <div style="height: 40px; width: 40px; background: #eee; border-radius: 4px; display: inline-block;"></div>
                        @endif
                    </td>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($product->wholesale_price, 2) }}</td>
                    <td class="text-right">{{ number_format($product->selling_price, 2) }}</td>
                    <td>
                        <input type="text" name="program[{{ $product->id }}]" class="form-control form-control-sm" placeholder="Write program...">
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
