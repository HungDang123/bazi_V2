<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaSoPdfExport extends Model
{
    use HasUuids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_READY = 'ready';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'params_hash',
        'params',
        'q1_status',
        'q2_status',
        'q1_path',
        'q2_path',
        'q1_error',
        'q2_error',
        'queued_at',
        'q1_ready_at',
        'q2_ready_at',
    ];

    protected function casts(): array
    {
        return [
            'params'      => 'array',
            'queued_at'   => 'datetime',
            'q1_ready_at' => 'datetime',
            'q2_ready_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function storageDir(): string
    {
        return storage_path('app/la-so-pdfs/'.$this->id);
    }

    public function quyenPath(int $quyen): string
    {
        return $this->storageDir().'/quyen-'.$quyen.'.pdf';
    }
}
