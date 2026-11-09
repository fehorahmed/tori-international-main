<?php
 
namespace App\Models\Ecommerce;
 
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
 
class Order extends Model
{
    use HasFactory, SoftDeletes;
 
    protected $table = 'orders';
 
    protected $fillable = [
        'user_id',
        'order_number',
        'pi_first_name',
        'pi_last_name',
        'pi_email',
        'pi_phone_no',
        'country',
        'shipping_city',
        'order_notes',
        'total_amount',
        'status',
        'shipping_address',
        'billing_address',
        'ordered_at',
        'payment_method',
        'payable_amount',
    ];
 
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
