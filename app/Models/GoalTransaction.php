<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalTransaction extends Model
{
    protected $fillable = ['goal_id', 'amount', 'balance_after', 'date'];

    public function goal() {
        return $this->belongsTo(Goal::class);
    }
}
