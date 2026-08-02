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

    public function getOptionsAttribute($value)
    {
        if ($value === null) {
            return [];
        }
        $options = is_array($value) ? $value : json_decode($value, true);
        if (is_array($options) && array_key_exists('choices', $options)) {
            return $options['choices'] ?? [];
        }
        return is_array($options) ? $options : [];
    }

    public function option(string $key, $default = null)
    {
        $value = $this->attributes['options'] ?? null;
        if ($value === null) {
            return $default;
        }
        $options = is_array($value) ? $value : json_decode($value, true);
        if (! is_array($options)) {
            return $default;
        }
        if (array_key_exists($key, $options)) {
            return $options[$key];
        }
        if ($key === 'choices' && (empty($options) || array_is_list($options))) {
            return $options;
        }
        return $default;
    }
}
