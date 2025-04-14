<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_date',
        'total_price',
        'total_pay',
        'total_return',
        'poin',
        'total_poin',
        'customer_id',
        'staff_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function detailSales()
    {
        return $this->hasMany(DetailSale::class);
    }

    public function createSaleData($pointsUsed, $customer)
    {
        return [
            'poin' => $pointsUsed,
            'total_poin' => $customer ? $customer->poin : 0,
            'customer_id' => $customer ? $customer->id : null,
        ];
    }
}
