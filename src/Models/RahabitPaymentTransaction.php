<?php

namespace Rahabit\Payment\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

use Rahabit\Payment\Traits\PaymentDatabase as DatabaseTrait;

/**
 * An Eloquent Model: 'IranPaymentTransaction'
 *
 * @property integer $id
 * @property integer $amount
 * @property string $gateway
 * @property string $status
 * @property string $full_name
 * @property string $email
 * @property string $mobile
 * @property string $currency
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static Model find(integer $id)
 * @method static Model where($key, $val)
 */
class RahabitPaymentTransaction extends Model
{
    use DatabaseTrait;

    const T_INIT = 0;
    const T_SUCCEED = 1;
    const T_FAILED = 2;
    const T_PENDING = 3;
    const T_VERIFY_PENDING = 4;
    const T_PAID_BACK = 5;
    const T_CANCELED = 6;

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable	= [
        'gateway',
        'amount',
        'currency',
        'tracking_code',
        'reference_number',
        'card_number',
        'user_id',
        'full_name',
        'email',
        'mobile',
        'description',
        'errors',
        'extra',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'gateway_data'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_text',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'extra' => 'array',
        'gateway_data' => 'array'
    ];

    /**
     * Get all of the owning payable models.
     */
    public function payable()
    {
        return $this->morphTo();
    }

    public function getStatusTextAttribute()
    {
        //@TODO::add translation
        switch($this->status) {
            case self::T_INIT:
                return 'ایجاد شده';
            case self::T_SUCCEED:
                return 'موفق';
            case self::T_FAILED:
                return 'ناموفق';
            case self::T_PENDING:
                return 'درجریان';
            case self::T_VERIFY_PENDING:
                return 'در انتظار تایید';
            case self::T_PAID_BACK:
                return 'برگشت وجه';
            case self::T_CANCELED:
                return 'انصراف';
            default:
                return '-';
        }
    }
}
