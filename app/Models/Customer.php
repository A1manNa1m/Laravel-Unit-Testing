<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'email',
        'address',
        'city',
        'state',
        'postal_code'
    ];

    public function invoices() {
        return $this -> hasMany(Invoice::class);
    }

    public function payments() {
        return $this -> hasMany(Payment::class);
    }

    // protected static function booted()
    // {
    //     static::deleting(function ($customer) {
    //         $customer->invoices()->delete();
    //     });
    // }
}
