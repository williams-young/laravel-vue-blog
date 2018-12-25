<?php

namespace App\Models;

use App\Notifications\GotVote;
use App\Traits\FollowTrait;
use Jcc\LaravelVote\Vote;
use App\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, Vote, FollowTrait;

    const STATE_DISABLED = 0;
    const STATE_ENABLED = 1;

    const STATES = [
        0 => '已禁用',
        1 => '启用中',
    ];

    protected $fillable = [
        'username', 'password', 'name', 'email', 'avatar_url', 'remember_token', 'login_ip', 'login_at', 'state'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function stateName()
    {
        return array_key_exists($this->state, static::STATES) ? static::STATES[$this->state] : '';
    }

    public function detail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function token()
    {
        return $this->hasMany(Token::class);
    }

    public function scopeFilter(Builder $query, $filters)
    {
        $query->where(function (Builder $query) use ($filters) {
            empty($filters['username']) ?: $query->where('username', $filters['username']);
            empty($filters['state']) ?: $query->where('state', $filters['state']);
            empty($filters['login_after']) ?: $query->where('login_at', '>', $filters['login_after']);
        });
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
//    public static function boot()
//    {
//        parent::boot();
//
//        static::addGlobalScope(new StatusScope());
//    }

    /**
     * Get the discussions for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function discussions()
    {
        return $this->hasMany(Discussion::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the comments for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function isSuperAdmin()
    {
        return ($this->id == config('blog.super_admin')) ? 1 : 0;
    }

    /**
     * Get the avatar and return the default avatar if the avatar is null.
     *
     * @param string $value
     * @return string
     */
    public function getAvatarAttribute($value)
    {
        return isset($value) ? $value : config('blog.default_avatar');
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        if (auth()->id() != $this->id && $this->email_notify_enabled == 'yes' && config('blog.mail_notification')) {
            return $this->email;
        }
    }

    /**
     * Up vote or down vote item.
     *
     * @param  \App\Models\User $user
     * @param  \Illuminate\Database\Eloquent\Model $target
     * @param  string $type
     *
     * @return boolean
     */
    public static function upOrDownVote($user, $target, $type = 'up')
    {
        $hasVoted = $user->{'has' . ucfirst($type) . 'Voted'}($target);

        if ($hasVoted) {
            $user->cancelVote($target);
            return false;
        }

        if ($type == 'up') {
            $target->user->notify(new GotVote($type . '_vote', $user, $target));
        }

        $user->{$type . 'Vote'}($target);

        return true;
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
}
