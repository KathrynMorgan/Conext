<?php

namespace Base;

use RedBeanPHP\R;

class Model extends \Prefab
{
    /*
     * @var $entity
     */
    protected $entity;

    public function __construct($entity = null)
    {
        // set entity/table
        $this->entity = $entity;

        // connect to redbean
        if (!R::testConnection()) {

            // get framework
            $f3 = \Base::instance();

            $db = $f3->get('db');

            if (!empty($db['dsn'])) {
                R::addDatabase(
                    'connection',
                    $db['dsn'],
                    $db['username'],
                    $db['password']
                );
            } else {
                R::addDatabase(
                    'connection',
                    'mysql:host='.$db['host'].';dbname='.$db['name'],
                    $db['username'],
                    $db['password']
                );
            }

            R::selectDatabase('connection');
            R::freeze($db['freeze']);
            R::debug($db['debug'], 2);
        }
    }

    /**
     * Create.
     */
    public function create($data = [], $fields = null)
    {
        if ($this->entity === null) {
            return;
        }

        $row = R::dispense($this->entity);
        if ($fields === null) {
            $row->import($data);
        } else {
            $row->import($data, implode(',', $fields));
        }

        return $row;
    }

    /**
     * Find.
     */
    public function find($where = null, $params = null)
    {
        if ($this->entity === null) {
            return;
        }

        if ($where !== null && $params !== null) {
            return R::find($this->entity, $where, $params);
        } elseif ($where !== null && $params === null) {
            return R::find($this->entity, $where);
        } else {
            return R::find($this->entity);
        }
    }

    /**
     * Find All.
     */
    public function findAll($where = null, $params = null)
    {
        if ($this->entity === null) {
            return;
        }

        if ($where !== null && $params !== null) {
            return R::findAll($this->entity, $where, $params);
        } elseif ($where !== null && $params === null) {
            return R::findAll($this->entity, $where);
        } else {
            return R::findAll($this->entity);
        }
    }

    /**
     * Find One.
     */
    public function findOne($where = null, $params = null)
    {
        if ($this->entity === null) {
            return;
        }

        if ($where !== null && $params !== null) {
            return R::findOne($this->entity, $where, $params);
        } elseif ($where !== null && $params === null) {
            return R::findOne($this->entity, $where);
        } else {
            return R::findOne($this->entity);
        }
    }

    /**
     * Get all.
     */
    public function getAll($where = null, $params = null)
    {
        if ($where !== null && $params !== null) {
            return R::getAll($where, $params);
        } elseif ($where !== null && $params === null) {
            return R::getAll($where);
        } else {
            return [];
        }
    }

    /**
     * Get row.
     */
    public function getRow($where = null, $params = null)
    {
        if ($where !== null && $params !== null) {
            return R::getRow($where, $params);
        } elseif ($where !== null && $params === null) {
            return R::getRow($where);
        } else {
            return [];
        }
    }

    /**
     * Get col.
     */
    public function getCol($where = null, $params = null)
    {
        if ($where !== null && $params !== null) {
            return R::getCol($where, $params);
        } elseif ($where !== null && $params === null) {
            return R::getCol($where);
        } else {
            return;
        }
    }

    /**
     * Get cell.
     */
    public function getCell($where = null, $params = null)
    {
        if ($where !== null && $params !== null) {
            return R::getCell($where, $params);
        } elseif ($where !== null && $params === null) {
            return R::getCell($where);
        } else {
            return;
        }
    }

    /**
     * findOrCreate.
     */
    public function findOrCreate($params = [])
    {
        return R::findOrCreate($this->entity, $params);
    }

    /**
     * Count.
     */
    public function count($where = null, $params = null)
    {
        if ($this->entity === null) {
            return;
        }

        if ($where !== null && $params !== null) {
            return R::count($this->entity, $where, $params);
        } elseif ($where !== null && $params === null) {
            return R::count($this->entity, $where);
        } else {
            return R::count($this->entity);
        }
    }

    /**
     * Load - by id.
     */
    public function load($id)
    {
        if ($this->entity === null) {
            return;
        }

        return R::load($this->entity, (int) $id);
    }

    /**
     * Export.
     */
    public function export(\RedBeanPHP\OODBBean $row, $parents = false)
    {
        $return = R::exportAll($row, $parents);

        // if single then export first
        if (!isset($return[1])) {
            return $return[0];
        }

        // return multi row export
        return $return;
    }

    /**
     * Store.
     */
    public function store(\RedBeanPHP\OODBBean $row)
    {
        return R::store($row);
    }

    /**
     * Store.
     */
    public function exec($query, $params = [])
    {
        return R::exec($query, $params);
    }

    /**
     * Trash bean.
     */
    public function trash(\RedBeanPHP\OODBBean $row)
    {
        return R::trash($row);
    }
}
