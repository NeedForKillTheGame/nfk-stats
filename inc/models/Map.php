<?php
namespace models;
class Map {
    public $map_id;
    public $name;
    public $hash;
    public $data;

    static function load($id) {
        $sql = sf('select * from nfkLive_maps where map_id = %d', $id);
        $models = \db::$inst->loadModels(get_class(), $sql);
        if (!isset($models[0])) throw new \Exception(sf('Model "%s" is not found', $id));
        return $models[0];
    }

    static function findAll() {
        $sql = sf('select * from nfkLive_maps');
        $models = \db::$inst->loadModels(get_class(), $sql);
        return $models;
    }
}