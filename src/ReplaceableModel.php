<?php

namespace jdavidbakr\ReplaceableModel;

use Illuminate\Support\ServiceProvider;

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
        return static::executeQuery('insert ignore', $attributes);
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
        $keys = collect($attributes->first())->keys()
            ->transform(function($key) {
                return "`".$key."`";
            });

        $bindings = [];
        $query = $command . " into " . \DB::getTablePrefix() . $model->getTable()." (".$keys->implode(",").") values ";
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

        \DB::connection($model->getConnectionName())->insert($query, $bindings);

        $model->fireModelEvent('saved', false);
    }
}