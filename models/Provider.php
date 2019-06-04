<?php

namespace Clake\UserExtended\Models;

use App;
use Str;
use Model;
use Carbon\Carbon;
use October\Rain\Support\Markdown;

/**
 * Post Model
 */
class Provider extends Model {

    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'clake_userextended_providers';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'provider_id', 'provider_token'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User']
    ];

}
