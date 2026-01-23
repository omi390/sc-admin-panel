<?php

namespace Modules\BookingModule\Http\Traits;

use Carbon\Carbon;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\SubscribedService;

trait BookingScopes
{

    public function scopeOfBookingStatus($query, $status): void
    {
        $query->where('booking_status', '=', $status);
    }

    public function scopeSearch($query, $keywords, array $searchColumns): mixed
    {
        return $query->when($keywords && $searchColumns, function ($query) use ($keywords, $searchColumns) {
            $keys = explode(' ', $keywords);
            $query->where(function ($query) use ($keys, $searchColumns) {
                foreach ($keys as $key) {
                    foreach ($searchColumns as $column) {
                        $query->orWhere($column, 'LIKE', '%' . $key . '%');
                    }
                }
            });
        });
    }

    public function scopeFilterByZoneId($query, $zoneId): mixed
    {
        return $query->when($zoneId, function ($query) use ($zoneId) {
            $query->where('zone_id', $zoneId);
        });
    }

    public function scopeFilterByZoneIds($query, $zoneIds): mixed
    {
        return $query->when($zoneIds, function ($query) use ($zoneIds) {
            $query->whereIn('zone_id', $zoneIds);
        });
    }

    public function scopeFilterByCategoryIds($query, $categoryIds): mixed
    {
        return $query->when($categoryIds, function ($query) use ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        });
    }

    public function scopeFilterBySubcategoryIds($query, $subCategoryIds): mixed
    {
        return $query->when($subCategoryIds, function ($query) use ($subCategoryIds) {
            $query->whereIn('sub_category_id', $subCategoryIds);
        });
    }

    public function scopeFilterByDateRange($query, $fromDate, $toDate): mixed
    {
        return $query->when($fromDate && $toDate, function ($query) use ($fromDate, $toDate) {
            if (!($fromDate instanceof Carbon)) {
                $fromDate = Carbon::parse($fromDate);
            }
            if (!($toDate instanceof Carbon)) {
                $toDate = Carbon::parse($toDate);
            }

            if ($fromDate->equalTo($toDate)) {
                $query->whereDate('created_at', $fromDate->startOfDay());
            } else {
                $query->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()]);
            }
        });
    }

    public function scopeAdminPendingBookings($query, $maxBookingAmount): mixed
    {
        return $query
            ->where('booking_status', 'pending')
            ->where(function ($query) use ($maxBookingAmount) {
                $query->where('payment_method', '!=', 'cash_after_service')
                    ->orWhere(function ($query) use ($maxBookingAmount) {
                        $query->where('payment_method', 'cash_after_service')
                            ->where('total_booking_amount', '<=', $maxBookingAmount)
                            ->orWhere('is_verified', 1);
                    });
            });
    }

    public function scopeAdminAcceptedBookings($query, $maxBookingAmount): mixed
    {
        return $query
            ->where('booking_status', 'accepted')
            ->where(function ($query) use ($maxBookingAmount) {
                $query->where('payment_method', '!=', 'cash_after_service')
                    ->orWhere(function ($query) use ($maxBookingAmount) {
                        $query->where('payment_method', 'cash_after_service')
                            ->where('total_booking_amount', '<=', $maxBookingAmount)
                            ->orWhere('is_verified', 1);
                    });
            });
    }

    public function scopeProviderPendingBookings($query, Provider $provider, $maxBookingAmount)
    {
        $providerId = $provider->id;
        $packageSubscriber = PackageSubscriber::where('provider_id', $providerId)->first();
        $endDate = optional($packageSubscriber)->package_end_date;
        $canceled = optional($packageSubscriber)->is_canceled;
        $packageEndDate = $endDate ? Carbon::parse($endDate)->endOfDay() : null;
        $currentDate = Carbon::now()->subDay();
        $isPackageEnded = $packageEndDate ? $currentDate->diffInDays($packageEndDate, false) : null;
        $scheduleBookingEligibility = nextBookingEligibility($providerId);

        if ($packageSubscriber) {
            if ($isPackageEnded > 0 && $scheduleBookingEligibility && !$canceled) {
                if ($provider->service_availability && (!$provider->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)) {
                    $zone_id = $provider->zone_id;
                    $subscribedSubCategories = SubscribedService::where(['provider_id' => $provider->id])->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

                    return $query
                        ->ofBookingStatus('pending')
                        ->whereIn('sub_category_id', $subscribedSubCategories)
                        ->where('zone_id', $zone_id)
                        ->when($maxBookingAmount > 0, function ($query) use ($maxBookingAmount) {
                            $query->where(function ($query) use ($maxBookingAmount) {
                                $query->where('payment_method', 'cash_after_service')
                                    ->where(function ($query) use ($maxBookingAmount) {
                                        $query->where('is_verified', 1)
                                            ->orWhere('total_booking_amount', '<=', $maxBookingAmount);
                                    })
                                    ->orWhere('payment_method', '<>', 'cash_after_service');
                            });
                        });
                } else {
                    return $query->whereNull('id');
                }
            } else {
                return $query->whereRaw('1 = 0'); // This ensures no results are returned
            }
        } else {
            if ($provider->service_availability && (!$provider->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)) {
                $zone_id = $provider->zone_id;
                $subscribedSubCategories = SubscribedService::where(['provider_id' => $provider->id])->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

                return $query
                    ->ofBookingStatus('pending')
                    ->whereIn('sub_category_id', $subscribedSubCategories)
                    ->where('zone_id', $zone_id)
                    ->when($maxBookingAmount > 0, function ($query) use ($maxBookingAmount) {
                        $query->where(function ($query) use ($maxBookingAmount) {
                            $query->where('payment_method', 'cash_after_service')
                                ->where(function ($query) use ($maxBookingAmount) {
                                    $query->where('is_verified', 1)
                                        ->orWhere('total_booking_amount', '<=', $maxBookingAmount);
                                })
                                ->orWhere('payment_method', '<>', 'cash_after_service');
                        });
                    });
            } else {
                return $query->whereNull('id');
            }
        }
    }

    public function scopeProviderAcceptedBookings($query, $provider_id, $maxBookingAmount): mixed
    {
        return $query
            ->ofBookingStatus('accepted')
            ->where('provider_id', $provider_id)
            ->when($maxBookingAmount > 0, function ($query) use ($maxBookingAmount) {
                $query->where(function ($query) use ($maxBookingAmount) {
                    $query->where('payment_method', 'cash_after_service')
                        ->where(function ($query) use ($maxBookingAmount) {
                            $query->where('total_booking_amount', '<=', $maxBookingAmount)
                                ->orWhere('is_verified', 1);
                        })
                        ->orWhere('payment_method', '<>', 'cash_after_service');
                });
            });
    }
}
