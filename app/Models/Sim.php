<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sim extends Model
{
    use SoftDeletes;
    
    protected $table = 'sims';

    protected $fillable = [
        'phone_number',
        'cost_price',
        'selling_price',
        'network_operator',
        'upper_trigram',
        'lower_trigram',
        'upper_trigram_name',
        'lower_trigram_name',
        'moving_line',
        'que_id',
        'que_bien_id',
        'five_element',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'upper_trigram' => 'integer',
        'lower_trigram' => 'integer',
        'moving_line' => 'integer',
        'que_id' => 'integer', // Cast to integer
        'que_bien_id' => 'integer', // Cast to integer
    ];

    /**
     * Get the Que64 record associated with this sim
     */
    public function que64()
    {
        return $this->belongsTo(Que64::class, 'que_id', 'id');
    }

    /**
     * Get the Que64 record for que bien
     */
    public function queBien()
    {
        return $this->belongsTo(Que64::class, 'que_bien_id', 'id');
    }

    /**
     * Get the que name (via relationship)
     */
    public function getQueNameAttribute()
    {
        return $this->que64?->name;
    }

    /**
     * Scope for filtering by network operator
     */
    public function scopeByNetworkOperator($query, $operator)
    {
        return $query->where('network_operator', $operator);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
