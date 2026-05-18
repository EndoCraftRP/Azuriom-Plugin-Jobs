<?php

namespace Azuriom\Plugin\Jobs\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasTablePrefix;

    protected $prefix = 'jobs_apply_';

    protected $fillable = ['name', 'slug', 'description', 'is_open', 'max_pending', 'order', 'show_applications_count', 'published_at', 'closed_at', 'keywords'];

    protected $casts = ['is_open' => 'boolean', 'show_applications_count' => 'boolean', 'published_at' => 'datetime', 'closed_at' => 'datetime', 'keywords' => 'array'];

    public function fields()
    {
        return $this->hasMany(Field::class)->orderBy('order');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function isAcceptingApplications(): bool
    {
        if (!$this->is_open) {
            return false;
        }
        if ($this->published_at !== null && now()->lt($this->published_at)) {
            return false;
        }
        if ($this->closed_at !== null && now()->gt($this->closed_at)) {
            return false;
        }

        if ($this->max_pending === null) {
            return true;
        }

        return $this->applications()
                ->whereIn('status', ['pending', 'reviewing'])
                ->count() < $this->max_pending;
    }

    public function translatedName(): string
    {
        $key = 'jobs::messages.defaults.' . $this->slug . '.name';
        $value = trans($key);

        return $value === $key ? $this->name : $value;
    }

    public function translatedDescription(): string
    {
        if ($this->description !== null && trim($this->description) !== '') {
            return $this->description;
        }

        $key = 'jobs::messages.defaults.' . $this->slug . '.description';
        $value = trans($key);

        return $value === $key ? '' : $value;
    }
}
