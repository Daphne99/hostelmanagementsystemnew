<?php

namespace App\Models2\HR;

use App\Models2\HR\Role;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'hr_user';
    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id','role_id','user_name','password','status','created_by','updated_by'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role(){
        return $this->hasOne(Role::class,'role_id','role_id');
    }
}
