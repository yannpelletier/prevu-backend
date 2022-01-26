<?php

namespace App;

use Carbon\Carbon;
use App\Events\UserCreated;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * Default values for attributes
     * @var  array an array with attribute as key and default as value
     */
    protected $attributes = [
        'first_name' => "",
        'last_name' => "",
        'password' => "",
        'stripe_connect_id' => "",
        'stripe_customer_id' => "",
        'send_sale_notifications' => true,
        'send_ip_notifications' => true,
        'analytics_currency' => "USD",
        'sale_currency' => "USD",
        'country' => 'US',
        'first_time_login' => true,
        'password_reset_token' => "",
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'send_sale_notifications',
        'send_ip_notifications',
        'analytics_currency',
        'sale_currency',
        'first_time_login',
        'password_reset_token'
    ];

    protected $casts = [
        'id' => 'integer',
        'send_sale_notifications' => 'boolean',
        'send_ip_notifications' => 'boolean',
        'first_time_login'=> 'boolean'
    ];

    protected $dispatchesEvents = [
        'created' => UserCreated::class
    ];

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'users.' . $this->id;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function watermarks()
    {
        return $this->hasMany(Watermark::class)->orWhere("user_id", "=", null);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'buyer_id');
    }

    public function sales()
    {
        return $this->hasMany(Purchase::class, 'seller_id');
    }

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function getConfirmedAttribute(): bool
    {
        return $this->stripe_connect_id !== '';
    }

    public function generateNewPasswordResetToken()
    {
        $this->attributes['password_reset_token'] = Str::random(191);
        $this->save();
    }

    /**
     * Ensures that password is Hashed whenever assigned.
     *
     * @var string $password clear-text string password
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function getAnalyticsAttribute()
    {
        $grossVolume = [];
        $itemsSold = [];
        $visits = [];

        $sales = $this->sales()->get()->toArray();
        for ($i = 365 - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();

            $itemsSold[] += array_reduce($sales, function ($carry, $item) use ($date) {
                $creationDate = Carbon::parse($item['created_at'])->toDateString();
                if ($creationDate == $date) {
                    return $carry + 1;
                }
                return $carry;
            }, 0);

            $grossVolume[] += array_reduce($sales, function ($carry, $item) use ($date) {
                $creationDate = Carbon::parse($item['created_at'])->toDateString();
                if ($creationDate == $date) {
                    return $carry + $item['price'];
                }
                return $carry;
            });
        }
        return ['gross_volume' => $grossVolume, 'items_sold' => $itemsSold, 'visits' => $visits];
    }

}
