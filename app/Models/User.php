<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }

   
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    /**
     * このユーザーをフォロー中のユーザー。（Userモデルとの関係を定義）
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }

    /**
     * $userIdで指定されたユーザーをフォローする。
     *
     * @param  int  $userId
     * @return bool
     */
    public function follow(int $userId)
    {
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;
        
        if ($exist || $its_me) {
            return false;
        } else {
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    /**
     * $userIdで指定されたユーザーをアンフォローする。
     * 
     * @param  int $usereId
     * @return bool
     */
    public function unfollow(int $userId)
    {
        $exist = $this->is_following($userId);
        $its_me = $this->id == $userId;
        
        if ($exist && !$its_me) {
            $this->followings()->detach($userId);
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 指定された$userIdのユーザーをこのユーザーがフォロー中であるか調べる。フォロー中ならtrueを返す。
     * 
     * @param  int $userId
     * @return bool
     */
    public function is_following(int $userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }

    // /**
    //  * このユーザーに関係するモデルの件数をロードする。
    //  */
    public function loadRelationshipCounts()
    {
        $this->loadCount(['microposts', 'followings', 'followers', 'favorites']);
    }

    /**
     * このユーザーとフォロー中ユーザーの投稿に絞り込む。
     */
    public function feed_microposts()
    {
        // このユーザーがフォロー中のユーザーのidを取得して配列にする
        $userIds = $this->followings()->pluck('users.id')->toArray();
        // このユーザーのidもその配列に追加
        $userIds[] = $this->id;
        // それらのユーザーが所有する投稿に絞り込む
        return Micropost::whereIn('user_id', $userIds);
    }


    // favorite

        public function favoriteing()
    {
        return $this->belongsToMany(User::class, 'user_favorite', 'user_id', 'favorite_id')->withTimestamps();
    }
    

    /**
     * このユーザーがお気に入り中の投稿。（Userモデルとの関係を定義）
     */
    public function favorites()
    {
        return $this->belongsToMany(User::class, 'user_favorite', 'user_id', 'favorite_id')->withTimestamps();
    }

    //このユーザーがお気に入りにしているポストを取得
    public function fetch_favorite_posts()
    {
        return $this->hasMany(Micropost::class)
            ->join('user_favorite','microposts.id','user_favorite.favorite_id')
            ->where('user_favorite.user_id',$this->id);
    }

    public function favorite(int $post_id)
    {
        $exist = $this->is_favoriteing($post_id);
        if ($exist) {
            return false;
        } 

        $this->favoriteing()->attach($post_id);
        return true;
    }
    
    /**
     * $post_idで指定された投稿をお気に入りから外す。
     * 
     * @param  int $post_id
     * @return bool
     */
    public function unfavorite(int $post_id)
    {
        $exist = $this->is_favoriteing($post_id);
        if ($exist) {
            $this->favoriteing()->detach($post_id);
            return true;
        }

        return false;
    }
    
    /**
     * 指定された$post_idの投稿をこのユーザーがお気に入りであるか調べる。
     * 
     * @param  int $post_id
     * @return bool
     */
    public function is_favoriteing(int $post_id)
    {
        return $this->fetch_favorite_posts()->where('favorite_id','=', $post_id)->exists();
        
        return $this->belongsToMany(User::class, 'user_favorite', 'user_id', 'favorite_id')->where('user_favorite.favorite_id', $post_id)->exists();
    }

}
