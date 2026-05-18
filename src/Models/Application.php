<?php

namespace Azuriom\Plugin\Jobs\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasTablePrefix;
    use HasUser;

    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REFUSED = 'refused';

    protected $prefix = 'jobs_apply_';

    protected $fillable = ['position_id', 'user_id', 'answers', 'status', 'admin_note', 'reviewed_by', 'reviewed_at'];

    protected $casts = ['answers' => 'array', 'reviewed_at' => 'datetime'];

    public static array $statusColors = [
        self::STATUS_PENDING => 'warning',
        self::STATUS_REVIEWING => 'info',
        self::STATUS_ACCEPTED => 'success',
        self::STATUS_REFUSED => 'danger',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(\Azuriom\Models\User::class, 'reviewed_by');
    }

    public function user()
    {
        return $this->belongsTo(\Azuriom\Models\User::class, 'user_id');
    }

    public function statusColor(): string
    {
        return self::$statusColors[$this->status] ?? 'secondary';
    }

    public function statusLabel(): string
    {
        return trans('jobs::messages.status_'.$this->status);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_REVIEWING], true);
    }
}
