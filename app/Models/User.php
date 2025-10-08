<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $staff
 * @method \Illuminate\Support\Collection<int, int> getRelevantUsersIds()
 * @method bool isDirector()
 * @method bool isManager()
 */

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'manager_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Một nhân viên chỉ có một quản lý
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Một quản lý có thể có nhiều nhân viên
    public function staff()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    // Một người dùng có thể có nhiều công việc
    public function tasks()
    {
        return $this->hasMany(Task::class, 'user_id', 'id');
    }

    /**
     * Helper: Kiểm tra nếu là director (cấp cao nhất)
     * Dựa trên role enum từ migration
     */
    public function isDirector()
    {
        return $this->role === 'director';
    }

    /**
     * Helper: Kiểm tra nếu là manager (có quyền xem staff)
     */
    public function isManager()
    {
        return $this->role === 'manager';
    }

    
    public function getRelevantUsersIds()
    {
        $ids = collect([$this->id]); // Luôn bao gồm bản thân

        if ($this->isDirector()) {
            // Director: Tất cả users có tasks (hiệu suất tốt hơn lấy tất cả)
            $ids = DB::table('users')->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tasks')
                      ->whereColumn('tasks.user_id', 'users.id');
            })->pluck('id');
        } elseif ($this->isManager()) {
            // Manager: Thêm staff trực thuộc
            $ids = $ids->merge($this->staff->pluck('id'));
        }
        // Staff: Chỉ bản thân (không merge gì)

        return $ids->unique()->values(); // Unique và reset keys
    }
}