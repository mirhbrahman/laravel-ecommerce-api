<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Transformers\TransactionTransformer;

class Transaction extends Model
{
    public $transformer = TransactionTransformer::class;

    protected $fillable = [
        'quantity',
        'buyer_id',
        'product_id',
    ];

    public function buyer()
    {
        return $this->belongsTo('App\Buyer');
    }

    public function product()
    {
        return $this->belongsTo('App\Product');
    }
}
