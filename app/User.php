<?php

namespace App;

use Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email', 'password',
    // ];
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Automatically creates hash for the user password.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    

    /**
     * Get the user's full name
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->surname} {$this->oname}";
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function user_type() {
        return $this->belongsTo('App\UserType');
    }

    public function set_last_access()
    {
        $this->last_access = date('Y-m-d H:i:s');
        $this->save();
    }

    public function allocationCommittee()
    {
        return $this->where('user_type_id', 12);
    }
}
