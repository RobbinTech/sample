<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->activation_token = str_random(30);
        });
    }

    //获取头像
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    //发送密码重置邮件
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    //关联数据模型，指明一个用户拥有多条微博
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    //取出当前用户的所有微博按时间倒序
    public function feed()
    {
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids, Auth::user()->id);
        return Status::whereIn('user_id', $user_ids)
                              ->with('user')
                              ->orderBy('created_at', 'desc');
    }

/*
 * followers 表
 * id     user_id     follower_id
 * 1       2             3         // 用户3关注了用户2。也就是说用户3是用户2 的粉丝。
 * 2       4             2         // 用户2关注了用户4。也就是说用户2是用户4的粉丝。
 * 3       3             2         // 和第一条相反。两人互相关注。 用户2也是用户3的粉丝。
 *
 *
 * belongsToMany(1,2,3,4)
 * 四个参数意思：
 *  1、目标model的class全称呼。
 *  2、中间表名
 *  3、中间表中当前model对应的关联字段
 *  4、中间表中目标model对应的关联字段
 *
 *   获取粉丝：（重点：这里粉丝也是用户。所以就把User 模型也当粉丝模型来用）
 *  eg: belongsToMany(User::class,'followers','user_id','follower_id');
 *      粉丝表,中间表,当前model在中间表中的字段,目标model在中间表中的字段。


*/

    //关联数据模型 一个用户可以拥有多个粉丝
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id')
                    ->withTimestamps();
    }


    //关联数据模型 一个用户可以关注多个用户
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id')
                    ->withTimestamps();
    }

    //关注用户操作
    public function follow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    //取消关注用户操作
    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    //判断当前登录的用户 A 是否关注了用户 B
    public function isFollowing($user_id)
    {

        return $this->followings->contains($user_id);
    }

    /*
        这里需要注意的是 Auth::user()->followings 的用法。我们在 User 模型里定义了关联方法 followings()，关联关系定义好后，我们就可以通过访问 followings 属性直接获取到关注用户的 集合。这是 Laravel Eloquent 提供的「动态属性」属性功能，我们可以像在访问模型中定义的属性一样，来访问所有的关联方法。

        还有一点需要注意的是 $user->followings 与 $user->followings() 调用时返回的数据是不一样的， $user->followings 返回的是 Eloquent：集合 。而 $user->followings() 返回的是 数据库请求构建器 ，followings() 的情况下，你需要使用：

        $user->followings()->get()

        或者 ：

        $user->followings()->paginate()

        方法才能获取到最终数据。可以简单理解为 followings 返回的是数据集合，而 followings() 返回的是数据库查询语句。如果使用 get() 方法的话：

        $user->followings == $user->followings()->get() // 等于 true
    */
}
