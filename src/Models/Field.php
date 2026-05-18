<?php

namespace Azuriom\Plugin\Jobs\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasTablePrefix;

    protected $prefix = 'jobs_apply_';

    protected $fillable = ['position_id', 'label', 'type', 'options', 'is_required', 'order', 'col_md'];

    protected $casts = ['options' => 'array', 'is_required' => 'boolean'];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
