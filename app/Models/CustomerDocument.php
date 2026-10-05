<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CustomerDocument extends Model
{
    use HasFactory, BelongsToOrganization;

    protected $fillable = [
        'uuid',
        'organization_id',
        'customer_id',
        'uploaded_by',
        'document_type',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $base = log($bytes, 1024);
        $floor = (int) floor($base);

        return round(pow(1024, $base - $floor), 1) . ' ' . ($units[$floor] ?? 'MB');
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'national_id' => 'National ID / Passport',
            'utility_bill' => 'Proof of Address / Utility Bill',
            'caf_form' => 'Customer Application Form (CAF)',
            'installation_signoff' => 'Installation & CPE Sign-Off',
            'sla_contract' => 'Corporate SLA Contract',
            default => 'General Document',
        };
    }
}
