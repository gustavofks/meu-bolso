<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'category_id',
        'account_id',
        'payment_method_id',
        'description',
        'amount',
        'date'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }

    public function account(): BelongsTo { return $this->belongsTo(Account::class); }

    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class); }

    public function tags(): BelongsToMany { return $this->belongsToMany(Tag::class); }
}
