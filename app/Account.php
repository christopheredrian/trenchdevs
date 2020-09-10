<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property $id
 * @property $business_name
 * Class Account
 * @package App
 */
class Account extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts';

    const TRENCHDEVS_BUSINESS_NAME = 'TrenchDevs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'owner_user_id', 'business_name', 'application_type_id',
    ];

    /**
     * @param string $businessName
     * @return mixed
     */
    public static function findByBusinessName(string $businessName)
    {
        return Account::where('business_name', $businessName)
            ->first();
    }

    public static function getTrenchDevsAccount()
    {
        return self::findByBusinessName(self::TRENCHDEVS_BUSINESS_NAME);
    }
}
