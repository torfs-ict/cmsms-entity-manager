<?php

namespace EntityManager;

use ContentOperations;
use EntityManager;
use PDO;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class used to perform SELECT queries on entities.
 */
final class EntityQuery {
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    const FIELD_ALIAS = 'content_alias';
    const FIELD_CREATION_DATE = 'create_date';
    const FIELD_HIERARCHY = 'hierarchy';
    const FIELD_ID = 'content_id';
    const FIELD_PARENT = 'parent_id';
    // TODO: create constants for all fields
    private $from;
    private $where = [];
    private $parameters = [];
    private $offset = null;
    private $limit = null;
    private $order = ['`hierarchy` ASC'];
    private $rndOrder = false;
    private $rndResult = false;
    private $activeOnly = true;
    public static function Factory($from = null) {
        $ret = new EntityQuery();
        if (!empty($from)) $ret->From($from);
        return $ret;
    }
    public function ActiveOnly($activeOnly = true) {
        $this->activeOnly = (bool)$activeOnly;
        return $this;
    }
    public function From($entity) {
        $this->from = $entity;
        return $this;
    }

    /**
     * @param string $condition
     * @param mixed ...$parameters
     * @return $this
     */
    public function Where($condition) {
        $parameters = func_get_args();
        $condition = array_shift($parameters);
        $this->where[] = $condition;
        $this->parameters = array_merge($this->parameters, $parameters);
        return $this;
    }
    public function Offset($offset = null) {
        if (is_null($offset)) $this->offset = null;
        else $this->offset = (int)$offset;
        return $this;
    }
    public function Limit($limit) {
        if (is_null($limit)) $this->limit = null;
        else $this->limit = (int)$limit;
        return $this;
    }
    public function Order($field, $direction = self::ORDER_ASC, $append = false) {
        if ($append !== true) $this->order = [];
        $this->order[] = sprintf('`%s` %s', $field, ($direction == self::ORDER_DESC ? self::ORDER_DESC : self::ORDER_ASC));
        return $this;
    }
    public function RandomizeOrder($randomize = false) {
        $this->rndOrder = (bool)$randomize;
        return $this;
    }
    public function RandomizeResult($randomize = false) {
        $this->rndResult = (bool)$randomize;
        return $this;
    }

    private function Generate(&$query, &$parameters, $count = false) {
        $query = [];
        $ref = new ReflectionClass($this->from);
        // Joins for entity properties
        $parameters = [];
        foreach($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $property = $property->getName();
            $parameters[] = $property;
            $query[] = sprintf('LEFT JOIN `#__content_props` AS `%1$s` ON `%1$s`.`content_id` = `c`.`content_id` AND `%1$s`.`prop_name` = ?', $property);
        }
        // FROM
        array_unshift($query, 'FROM `#__content` AS `c`');
        // SELECT clause including all properties
        foreach($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $property = $property->getName();
            array_unshift($query, sprintf(', `%1$s`.`content` AS `%1$s`', $property));
        }
        array_unshift($query, '`c`.`content_id` AS `__id`, `c`.*');
        array_unshift($query, 'SELECT');
        // Filter type
        $query[] = 'WHERE `c`.`type` = ?';
        $parameters[] = $this->from;
        // Active only filter
        if ($this->activeOnly) {
            $query[] = 'AND `c`.`active` = ?';
            $parameters[] = 1;
        }
        // User filter
        if (!empty($this->where)) {
            $query[] = sprintf('HAVING %s', implode(' AND ', $this->where));
            $parameters = array_merge($parameters, $this->parameters);
        }
        if ($count !== true) {
            // Order
            if ((bool)$this->rndOrder === false && !empty($this->order)) {
                $query[] = sprintf('ORDER BY %s', implode(', ', $this->order));
            } elseif ((bool)$this->rndOrder === true) {
                $query[] = 'ORDER BY RAND()';
            }
            // Offset
            if (!is_null($this->offset)) {
                $query[] = sprintf('LIMIT %d, %d', $this->offset, $this->limit);
            } elseif (!is_null($this->limit)) {
                $query[] = sprintf('LIMIT %d', $this->limit);
            }
            // Randomize
            if ((bool)$this->rndResult === true) {
                array_unshift($query, 'SELECT * FROM (');
                $query[] = ') AS `query`';
                $query[] = 'ORDER BY RAND()';
            }
            if ((bool)$this->rndOrder === true) {
                array_unshift($query, 'SELECT * FROM (');
                $query[] = ') AS `query`';
                if (!empty($this->order)) {
                    $query[] = sprintf('ORDER BY %s', implode(', ', $this->order));
                }
            }
            $query = implode("\n", $query);
        } else {
            $query = 'SELECT COUNT(*) FROM (' . implode("\n", $query) . ') AS `tmp`';
        }
    }

    /**
     * @return Entity[]
     */
    public function Query() {
        $this->Generate($query, $parameters);
        $stmt = EntityManager::GetInstance()->MySQL()->query($query, $parameters);
        #var_dump("\n" . $stmt->interpolateQuery($parameters) . ";\n");
        $ret = $stmt->fetchAll(PDO::FETCH_COLUMN);
        array_walk($ret, function(&$item) {
            $item = ContentOperations::get_instance()->LoadContentFromId($item);
        });
        return $ret;
    }

    public function Total(&$result) {
        $this->Generate($query, $parameters, true);
        $stmt = EntityManager::GetInstance()->MySQL()->query($query, $parameters);
        #var_dump("\n" . $stmt->interpolateQuery($parameters) . ";\n");
        $result = (int)$stmt->fetchColumn();
        return $this;
    }
}