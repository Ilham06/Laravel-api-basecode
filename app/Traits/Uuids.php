<?php
namespace App\Traits;

use Illuminate\Support\Str;

trait Uuids
{
    /**
    * Boot function from Laravel
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->keyType = 'string';
            $model->incrementing = false;
            $model->{$model->getKeyName()} = Str::uuid()->toString();
        });

    }
    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }
}
