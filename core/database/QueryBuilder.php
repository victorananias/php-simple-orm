<?php

namespace App\Core\Database;

use App\Core\Database\Queriable\Select;
use App\Core\Database\Queriable\Where;
use App\Core\Database\Queriable\Update;
use App\Core\Database\Queriable\Insert;
use App\Core\Database\Queriable\OrderBy;
use App\Core\Database\Queriable\Join;
use \PDO;
use App\Core\Database\Queriable\GroupBy;
use App\Core\Database\Queriable\Delete;

class QueryBuilder
{
    protected $pdo;

    protected $table;
    protected $tableAlias;
    protected $sql = '';

    protected $stmt;
    protected $where;
    protected $select;
    protected $orderBy;
    protected $joins = [];

    protected $limit = null;
    protected $params = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

        $this->stmt = new Statement($pdo);

        $this->select = new Select();
        $this->where = new Where();
        $this->orderBy = new OrderBy();
    }

    public function testing()
    {
        $this->stmt->setTesting();
        return $this;
    }

    /**
     * set table name
     *
     * @param string $name
     * @return void
     */
    public function from($table = null, $alias = null)
    {
        return $this->table($table, $alias);
    }

    /**
     *
     * set table name
     *
     * @param string $name
     * @return $this
     */
    public function table($table = null, $alias = null)
    {
        if (!$table) {
            die('Table name not specified.');
        }

        $this->table = $alias ? $table . ' as ' . $alias : $table;

        return $this;
    }

    // public function limit($limit = 10)
    // {
    //     $this->limit = $limit;
    //     return $this;
    // }

    /**
     * add where conditions
     *
     * @param mixed ...$data
     * @return \App\Core\Database\QueryBuilder
     */
    public function where(...$data)
    {
        // Multiple Where
        if (count($data) == 1 && is_array($data[0])) {
            $this->where->addMultiple($data[0]);
            return $this;
        }

        $this->where->add(...$data);

        return $this;
    }

    /**
     * add where like condition
     *
     * @param string $column
     * @param string $value
     * @return \App\Core\Database\QueryBuilder
     */
    public function whereLike($column, $value)
    {
        $this->where->add($column, 'like', $value);
        return $this;
    }

    /**
     * add where null condition
     *
     * @param string $column
     * @return \App\Core\Database\QueryBuilder
     */
    public function whereNull($column)
    {
        $this->where->add("{$column} is null");
        return $this;
    }

    /**
     * add where not null condition
     *
     * @param string $column
     * @param string $value
     * @return \App\Core\Database\QueryBuilder
     */
    public function whereNotNull($column)
    {
        $this->where->add("{$column} is not null");
        return $this;
    }

    /**
     * fetch all the rows on the given table
     *
     * @return void
     */
    public function all()
    {
        $this->select->from($this->table);
        return $this->stmt->setQuery("$this->select")->fetchAll();
    }

    public function groupBy(...$columns)
    {
        $this->select->groupBy(new GroupBy(...$columns));
        return $this;
    }

    /**
     * adds the columns to the select query
     *
     * @param mixed ...$columns
     * @return $this
     */
    public function select(...$columns)
    {
        $this->select->columns(...$columns);
        return $this;
    }

    public function pluck($column)
    {
        // $this->sql = $this->select->prepare($this->table, $this->where);

        // $result = $this->stmt->setQuery($this->sql)->fetchAll($this->select->params());

        // if (count($result) == 0) {
        //     return [];
        // }

        // return array_map(function ($i) use ($column) {
        //     return $i->$column;
        // }, $result);
    }

    /**
     *
     */
    public function get()
    {
        $this->select
            ->from($this->table)
            ->where($this->where)
            ->orderBy($this->orderBy);

        foreach ($this->joins as $join) {
            $this->select->join($join);
        }

        return $this->stmt->setQuery("$this->select")->fetchAll($this->select->params());
    }

    public function first()
    {
        // $this->limit = 1;

        // $this->sql = $this
        //     ->select
        //     ->prepare($this->table, $this->where, $this->joins, 1, $this->order, $this->groupBy);

        // return $this->stmt->setQuery($this->sql)->fetch();
    }

    /**
     * join
     *
     * @param string ...$params
     * @return QueryBuilder
     */
    public function join(...$params)
    {
        $join = new Join($params[0]);

        if (count($params) == 2) {
            $params[1]($join);
        }

        if (count($params) == 3) {
            $join->on($params[1], $params[2]);
        }

        if (count($params) == 4) {
            $join->on($params[1], $params[2], $params[3]);
        }

        $this->joins[] = $join;

        return $this;
    }

    /**
     * records the given data on the given table
     *
     * @param array $data
     * @return void
     */
    public function create($data = [])
    {
        $insert = new Insert($this->table, $data);
        return $this->stmt->setQuery("$insert")->execute($insert->params());
    }

    public function orderBy($column, $type = null)
    {
        if (!$this->orderBy) {
            $this->orderBy = new OrderBy($column, $type);
            return $this;
        }

        $this->orderBy->add($column, $type);

        return $this;
    }

    public function update($data = [])
    {
        $update = new Update();

        $update->table($this->table);
        $update->where($this->where);
        $update->set($data);

        return $this->stmt->setQuery("$update")->execute($update->params());
        
    }

    public function raw($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    public function delete()
    {
        $delete = new Delete();
        $delete->from($this->table)->where($this->where);
        return $this->stmt->setQuery("$delete")->execute($delete->params());
    }
    
    // public function count()
    // {
    //     $this->select->setColumns(['count(*)'])->prepare($this->table, $this->where);

    //     return $this->stmt->setQuery("$this->select")->fetchColumn($this->select->params());
    // }
}
