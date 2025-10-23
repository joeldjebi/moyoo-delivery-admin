<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone_activite extends Model
{
	use SoftDeletes;

	protected $table = 'zone_activites';

	protected $fillable = [
		'code',
		'libelle',
		'created_by',
		'entreprise_id'
	];

	protected $casts = [
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
		'deleted_at' => 'datetime'
	];

	public function entreprise()
	{
		return $this->belongsTo(Entreprise::class);
	}

	public function scopeByEntreprise($query, $entrepriseId)
	{
		return $query->where('entreprise_id', $entrepriseId);
	}
}
