<?php

namespace Modules\ServiceManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProsAndCon extends Model
{
    use HasUuid;

    protected $table = 'service_pros_and_cons';

    protected $fillable = [
        'service_id',
        'title',
        'prod_or_con',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public const TYPE_PROS = 'pros';
    public const TYPE_CON = 'con';

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}
