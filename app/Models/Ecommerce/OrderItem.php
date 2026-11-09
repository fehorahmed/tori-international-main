<?php
 
namespace App\Models\Ecommerce;
 
use App\Models\Settings\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class OrderItem extends Model
{
    use HasFactory;
 
    protected $table = 'order_items';
 
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total_price',
        'attributes',
        'custom_note',
        'status',
    ];
 
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
 
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
