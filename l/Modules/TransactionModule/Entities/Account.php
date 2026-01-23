<?php

namespace Modules\TransactionModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Mail;
use Modules\BusinessSettingsModule\Emails\CashInHandOverflowMail;
use Modules\ProviderManagement\Emails\AccountUnsuspendMail;
use Modules\ProviderManagement\Entities\Provider;

class Account extends Model
{
    use HasFactory;
    use HasUuid;

    protected $casts = [
        'balance_pending' => 'float',
        'received_balance' => 'float',
        'account_payable' => 'float',
        'account_receivable' => 'float',
        'total_withdrawn' => 'float',
    ];

    protected $fillable = ['user_id','balance_pending','received_balance','account_payable','account_receivable','total_withdrawn'];

    protected static function newFactory()
    {
        return \Modules\TransactionModule\Database\factories\AccountFactory::new();
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {

        });

        self::created(function ($model) {

        });

        self::updating(function ($model) {
            // ... code here
        });

        self::updated(function ($model) {
            if ($model->isDirty('account_payable') || $model->isDirty('account_receivable')) {
                $limit_status = provider_warning_amount_calculate($model->account_payable, $model->account_receivable);
                $provider = Provider::where('user_id', $model->user_id)->first();


                if ($limit_status == '100_percent') {
                    if ($provider){
                        $provider->is_suspended = 1;
                        $provider->save();

                        try {
                            Mail::to($provider?->owner?->email)->send(new CashInHandOverflowMail($provider));
                        } catch (\Exception $exception) {
                            info($exception);
                        }
                    }
                }else{
                    if ($provider){
                        $provider->is_suspended = 0;
                        $provider->save();

                        try {
                            Mail::to($provider?->owner?->email)->send(new AccountUnsuspendMail($provider));
                        } catch (\Exception $exception) {
                            info($exception);
                        }
                    }
                }
            }
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }
}
