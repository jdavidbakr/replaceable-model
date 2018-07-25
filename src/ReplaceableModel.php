<?php

namespace jdavidbakr\ReplaceableModel;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use DB;

trait ReplaceableModel
{

    /**
     * Performs a 'replace' query with the data
     * @param  array  $attributes 
     * @return bool   t/f for success/failure
     */
    public static function replace(array $attributes = [])
    {
        return static::executeQuery('replace', $attributes);
    }

    /**
     * performs an 'insert ignore' query with the data
     * @param  array  $attributes 
     * @return bool   t/f for success/failure
     */
    public static function insertIgnore(array $attributes = [])
    {
        $model = new static();
        $driver = $model->GetConnection()->GetDriverName();
        switch($driver) {
            case 'sqlite':
                return static::executeQuery('insert or ignore', $attributes);
                break;
            default:
                return static::executeQuery('insert ignore', $attributes);
                break;
        }
    }

    protected static function executeQuery($command, array $attributes)
    {
        if(!count($attributes)) {
            return true;
        }
        $model = new static();

        if ($model->fireModelEvent('saving') === false) {
            return false;
        }

        $attributes = collect($attributes);
        $first = $attributes->first();
        if(!is_array($first)) {
            $attributes = collect([$attributes->toArray()]);
        }

        // Check for timestamps
        // Note that because we are actually deleting the record in the case of replace, we don't have reference to the original created_at timestamp;
        // If you need to retain that, you shouldn't be using this package and should be using the standard eloquent system.
        if($model->timestamps) {
            foreach($attributes as $key=>$set) {
                if(empty($set[static::CREATED_AT])) {
                    $set[static::CREATED_AT] = Carbon::now();
                }
                if(! is_null($model::UPDATED_AT) && empty($set[static::UPDATED_AT])) {
                    $set[static::UPDATED_AT] = Carbon::now();
                }
                $attributes[$key] = $set;
            }
        }

        $keys = collect($attributes->first())->keys()
            ->transform(function($key) {
                return "`".$key."`";
            });

        $bindings = [];
        $query = $command . " into " . DB::connection($model->getConnectionName())->getTablePrefix() . $model->getTable()." (".$keys->implode(",").") values ";
        $inserts = [];
        foreach($attributes as $data) {
            $qs = [];
            foreach($data as $value) {
                $qs[] = '?';
                $bindings[] = $value;
            }
            $inserts[] = '('.implode(",",$qs).')';
        }
        $query .= implode(",",$inserts);

        DB::connection($model->getConnectionName())->insert($query, $bindings);

        $model->fireModelEvent('saved', false);
    }
}
