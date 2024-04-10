<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\CvRole;
use App\CvFile;
use App\CvContractor;
use App\CvMonitoringByFlow;
use App\CvQuota;
use App\PlayerIdOneSignal;
use App\CvCategory;
use App\CvTask;
use App\CvTaskOpen;
use  App\CvTaskExecutionUser;

class User extends Authenticatable {

    use HasApiTokens,
        Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'rol_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //*** Consultar usuario por rol ***//

    public static function consultUserByRol($role_id) {

        $users = User::where("role_id", $role_id)->get();

        if (empty($users)) {
            return [
                "message" => "Los usuarios con el " . $role_id . " no existe en el sistema",
                "code" => 500
            ];
        }
        
        return $users;
    }

    //*** Relaciones ***//

    public function task() {
        return $this->belongsToMany(CvTask::class, 'cv_task_by_user', 'user_id', 'task_id');
    }

    public function taskOpen() {
        return $this->hasMany(CvTaskOpen::class);
    }

    public function role() {
        return $this->belongsTo(CvRole::class);
    }

    public function contractor() {
        return $this->hasMany(CvContractor::class);
    }

    public function contractorOne() {
        return $this->hasOne(CvContractor::class);
    }

    public function monitoringbyflow() {
        return $this->hasOne(CvMonitoringByFlow::class, 'user_id', 'id');
    }

    public function quota() {
        return $this->belongsTo(CvQuota::class);
    }

    public function playerId() {
        return $this->hasOne(PlayerIdOneSignal::class);
    }

    public function playerIds() {
        return $this->hasMany(PlayerIdOneSignal::class);
    }

    //*** Archivos por usuario ***//

    public function files() {
        return $this->belongsToMany(CvFile::class, 'cv_users_by_files', 'user_id', 'cv_file_id');
    }

    public function AuthAcessToken() {
        return $this->hasMany(OauthAccessToken::class);
    }

    public function category() {
        return $this->belongsToMany(CvCategory::class, 'cv_category_by_contractors', 'users_id', 'categories_id');
    }

    public function taskExecutionByUser(){
        return $this->hasMany(CvTaskExecutionUser::class);
    }

    public function potentialPropertiesByUser(){
        return $this->hasMany(CvPropertyByUser::class);
    }

    public function usertaskOpen(){
        return $this->hasMany(CvTaskOpen::class,'user_id');
    }
    public function usertaskExecution(){
        return $this->hasMany(CvTaskExecutionUser::class,'user_id');
    }


}
