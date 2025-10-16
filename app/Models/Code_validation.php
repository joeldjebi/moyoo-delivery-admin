<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Code_validation extends Model
{
    protected $table = 'code_validations';

    protected $fillable = [
        'code',
        'verifier',
        'livraison_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'verifier' => 'boolean'
    ];

    /**
     * Relations
     */
    public function livraison()
    {
        return $this->belongsTo(Livraison::class);
    }
}
