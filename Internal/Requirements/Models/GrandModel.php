<?php namespace ZN\Requirements\Models;

use BaseController, DB, DBTool, DBForge, Arrays, GeneralException, Config, Support;

class GrandModel extends BaseController
{
    //--------------------------------------------------------------------------------------------------------
    //
    // Author     : Ozan UYKUN <ozanbote@gmail.com>
    // Site       : www.znframework.com
    // License    : The MIT License
    // Copyright  : (c) 2012-2016, znframework.com
    //
    //--------------------------------------------------------------------------------------------------------

    //--------------------------------------------------------------------------------------------------------
    // Variable Grand Table
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string: empty
    //
    //--------------------------------------------------------------------------------------------------------
    protected $grandTable = '';

    //--------------------------------------------------------------------------------------------------------
    // Variable Connect
    //--------------------------------------------------------------------------------------------------------
    //
    // @var resource
    //
    //--------------------------------------------------------------------------------------------------------
    protected $connect;

    //--------------------------------------------------------------------------------------------------------
    // Variable Connect Tool
    //--------------------------------------------------------------------------------------------------------
    //
    // @var resource
    //
    //--------------------------------------------------------------------------------------------------------
    protected $connectTool;

    //--------------------------------------------------------------------------------------------------------
    // Variable Connect Forge
    //--------------------------------------------------------------------------------------------------------
    //
    // @var resource
    //
    //--------------------------------------------------------------------------------------------------------
    protected $connectForge;

    //--------------------------------------------------------------------------------------------------------
    // Variable Tables
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $tables;

    //--------------------------------------------------------------------------------------------------------
    // Variable Status
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string
    //
    //--------------------------------------------------------------------------------------------------------
    protected $status;

    //--------------------------------------------------------------------------------------------------------
    // Variable Get
    //--------------------------------------------------------------------------------------------------------
    //
    // @var get object
    //
    //--------------------------------------------------------------------------------------------------------
    protected $get;

    //--------------------------------------------------------------------------------------------------------
    // Constructor
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        $staticConnection   = defined('static::connection') ? static::connection : NULL;
        $this->connect      = DB::differentConnection($staticConnection);
        $this->connectTool  = DBTool::differentConnection($staticConnection);
        $this->connectForge = DBForge::differentConnection($staticConnection);
        $this->tables       = $this->connectTool->listTables();

        if( defined('static::table') )
        {
            $grandTable = static::table;
        }
        else
        {
            $grandTable = \Strings::divide(str_ireplace([INTERNAL_ACCESS, 'Grand'], '', get_called_class()), '\\', -1);
        }

        $this->grandTable = strtolower($grandTable);
    }

    //--------------------------------------------------------------------------------------------------------
    // Magic Call
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $method
    // @param array  $parameters
    //
    //--------------------------------------------------------------------------------------------------------
    public function __call($method, $parameters)
    {
        if( $return = $this->_callColumn($method, $parameters, 'row') )
        {
            return $return;
        }
        elseif( $return = $this->_callColumn($method, $parameters, 'result') )
        {
            return $return;
        }
        elseif( $return = $this->_callColumn($method, $parameters, 'update') )
        {
            return $return;
        }
        elseif( $return = $this->_callColumn($method, $parameters, 'delete') )
        {
            return $return;
        }

        Support::classMethod(get_called_class(), $method);
    }

    //--------------------------------------------------------------------------------------------------------
    // Destructor
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function __destruct()
    {
        if( ! Arrays::valueExistsInsensitive($this->tables, $this->grandTable) && $this->status !== 'create' )
        {
            try
            {
                throw new GeneralException(\Lang::select('Database', 'tableNotExistsError', 'Grand: '.$this->grandTable));
            }
            catch( GeneralException $e )
            {
                $e->continue();
            }
        }

        $this->status = NULL;
    }

    //--------------------------------------------------------------------------------------------------------
    // Insert
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $data: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function insert($data) : Bool
    {
        $this->_postGet($table, $data);

        return $this->connect->insert($table, $data);
    }

    //--------------------------------------------------------------------------------------------------------
    // Insert ID
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function insertID() : Int
    {
        return $this->connect->insertID();
    }

    //--------------------------------------------------------------------------------------------------------
    // Is Exists
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $column
    // @param string $value
    //
    //--------------------------------------------------------------------------------------------------------
    public function isExists(String $column, String $value) : Bool
    {
        return $this->connect->isExists($this->grandTable, $column, $value);
    }

    //--------------------------------------------------------------------------------------------------------
    // Select
    //--------------------------------------------------------------------------------------------------------
    //
    // @param mixed $select: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function select(...$select)
    {
        $this->connect->select(...$select);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Update
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $data: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function update($data, String $column = NULL, String $value = NULL) : Bool
    {
        $this->_postGet($table, $data);

        if( $column !== NULL )
        {
            $this->connect->where($column, $value);
        }

        return $this->connect->update($table, $data);
    }

    //--------------------------------------------------------------------------------------------------------
    // Delete
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $column: empty
    // @param string $value : empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function delete(String $column = NULL, String $value = NULL) : Bool
    {
        if( $column !== NULL )
        {
            $this->connect->where($column, $value);
        }

        return $this->connect->delete($this->grandTable);
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Get
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _get()
    {
        return $this->get = $this->connect->get($this->grandTable);
    }

    //--------------------------------------------------------------------------------------------------------
    // Columns
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function columns() : Array
    {
        return $this->_get()->columns();
    }

    //--------------------------------------------------------------------------------------------------------
    // Total Columns
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function totalColumns() : Int
    {
        return $this->_get()->totalColumns();
    }

    //--------------------------------------------------------------------------------------------------------
    // Row
    //--------------------------------------------------------------------------------------------------------
    //
    // @param mixed $printable: false
    //
    //--------------------------------------------------------------------------------------------------------
    public function row($printable = false)
    {
        return $this->_get()->row($printable);
    }

    //--------------------------------------------------------------------------------------------------------
    // Result
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $type: object
    //
    //--------------------------------------------------------------------------------------------------------
    public function result(String $type = 'object')
    {
        return $this->_get()->result($type);
    }

    //--------------------------------------------------------------------------------------------------------
    // Increment
    //--------------------------------------------------------------------------------------------------------
    //
    // @param mixed $columns  : empty
    // @param int   $increment: 1
    //
    //--------------------------------------------------------------------------------------------------------
    public function increment($columns, Int $increment = 1) : Bool
    {
        return $this->connect->increment($this->grandTable, $columns, $increment);
    }

    //--------------------------------------------------------------------------------------------------------
    // Decrement
    //--------------------------------------------------------------------------------------------------------
    //
    // @param mixed $columns  : empty
    // @param int   $decrement: 1
    //
    //--------------------------------------------------------------------------------------------------------
    public function decrement($columns, Int $decrement = 1) : Bool
    {
        return $this->connect->decrement($this->grandTable, $columns, $decrement);
    }

    //--------------------------------------------------------------------------------------------------------
    // Status
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $type: row
    //
    //--------------------------------------------------------------------------------------------------------
    public function status(String $type = 'row')
    {
        return $this->connect->status($this->grandTable)->$type();
    }

    //--------------------------------------------------------------------------------------------------------
    // Total Rows
    //--------------------------------------------------------------------------------------------------------
    //
    // @param bool $status: false
    //
    //--------------------------------------------------------------------------------------------------------
    public function totalRows(Bool $status = false) : Int
    {
        return $this->_get()->totalRows($status);
    }

    //--------------------------------------------------------------------------------------------------------
    // Where
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $column : empty
    // @param string $value  : empty
    // @param string $logical: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function where($column, String $value = NULL, String $logical = NULL)
    {
        $this->connect->where($column, $value, $logical);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Having
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $column : empty
    // @param string $value  : empty
    // @param string $logical: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function having($column, String $value = NULL, String $logical = NULL)
    {
        $this->connect->having($column, $value, $logical);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Where Group
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array ...$args
    //
    //--------------------------------------------------------------------------------------------------------
    public function whereGroup(...$args)
    {
        $this->connect->whereGroup(...$args);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Having Group
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array ...$args
    //
    //--------------------------------------------------------------------------------------------------------
    public function havingGroup(...$args)
    {
        $this->connect->havingGroup(...$args);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Inner Join
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $table      : empty
    // @param string $otherColumn: empty
    // @param string $operator   : empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function innerJoin(String $table, String $otherColumn, String $operator = '=')
    {
        $this->connect->innerJoin($table, $otherColumn, $operator);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Outer Join
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $table      : empty
    // @param string $otherColumn: empty
    // @param string $operator   : empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function outerJoin(String $table, String $otherColumn, String $operator = '=')
    {
        $this->connect->outerJoin($table, $otherColumn, $operator);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Left Join
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $table      : empty
    // @param string $otherColumn: empty
    // @param string $operator   : empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function leftJoin(String $table, String $otherColumn, String $operator = '=')
    {
        $this->connect->leftJoin($table, $otherColumn, $operator);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Right Join
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $table      : empty
    // @param string $otherColumn: empty
    // @param string $operator   : empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function rightJoin(String $table, String $otherColumn, String $operator = '=')
    {
        $this->connect->rightJoin($table, $otherColumn, $operator);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Join
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $table    : empty
    // @param string $condition: empty
    // @param string $type     : empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function join(String $table, String $condition, String $type = NULL)
    {
        $this->connect->join($table, $condition, $type);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Duplicate Check
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string ...$args
    //
    //--------------------------------------------------------------------------------------------------------
    public function duplicateCheck(...$args)
    {
        $this->connect->duplicateCheck(...$args);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Duplicate Check Update
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string ...$args
    //
    //--------------------------------------------------------------------------------------------------------
    public function duplicateCheckUpdate(...$args)
    {
        $this->connect->duplicateCheckUpdate(...$args);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Order By
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $condition: empty
    // @param string $type     : empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function orderBy($condition, String $type = NULL)
    {
        $this->connect->orderBy($condition, $type);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Group By
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string ...$args
    //
    //--------------------------------------------------------------------------------------------------------
    public function groupBy(...$args)
    {
        $this->connect->groupBy(...$args);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Limit
    //--------------------------------------------------------------------------------------------------------
    //
    // @param mixed $start: 0
    // @param int   $limit: 0
    //
    //--------------------------------------------------------------------------------------------------------
    public function limit($start = 0, Int $limit = 0)
    {
        $this->connect->limit($start, $limit);

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Pagination
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $url     : empty
    // @param array  $settings: empty
    // @param bool   $output  : true
    //
    //--------------------------------------------------------------------------------------------------------
    public function pagination(String $url = NULL, Array $settings = [], Bool $output = true)
    {
        if( ! empty($this->get) )
        {
            $get = $this->get;
        }
        else
        {
            $get = $this->_get();
        }

        return $get->pagination($url, $settings, $output);
    }

    //--------------------------------------------------------------------------------------------------------
    // Create
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array  $data : empty
    // @param string $extra: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function create(Array $data, $extra = NULL) : Bool
    {
        $this->status = 'create';

        return $this->connectForge->createTable($this->grandTable, $data, $extra);
    }

    //--------------------------------------------------------------------------------------------------------
    // Drop
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function drop() : Bool
    {
        return $this->connectForge->dropTable($this->grandTable);
    }

    //--------------------------------------------------------------------------------------------------------
    // Truncate
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function truncate() : Bool
    {
        return $this->connectForge->truncate($this->grandTable);
    }

    //--------------------------------------------------------------------------------------------------------
    // Rename
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $newName: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function rename(String $newName) : Bool
    {
        return $this->connectForge->renameTable($this->grandTable, $newName);
    }

    //--------------------------------------------------------------------------------------------------------
    // Add Column
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $column: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function addColumn(Array $column) : Bool
    {
        return $this->connectForge->addColumn($this->grandTable, $column);
    }

    //--------------------------------------------------------------------------------------------------------
    // Drop Column
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $column: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function dropColumn($column) : Bool
    {
        return $this->connectForge->dropColumn($this->grandTable, $column);
    }

    //--------------------------------------------------------------------------------------------------------
    // Modify Column
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $column: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function modifyColumn(Array $column) : Bool
    {
        return $this->connectForge->modifyColumn($this->grandTable, $column);
    }

    //--------------------------------------------------------------------------------------------------------
    // Rename Column
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $column: empty
    //
    //--------------------------------------------------------------------------------------------------------
    public function renameColumn(Array $column) : Bool
    {
        return $this->connectForge->renameColumn($this->grandTable, $column);
    }

    //--------------------------------------------------------------------------------------------------------
    // Optimize
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function optimize() : String
    {
        return $this->connectTool->optimizeTables($this->grandTable);
    }

    //--------------------------------------------------------------------------------------------------------
    // Repair
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function repair() : String
    {
        return $this->connectTool->repairTables($this->grandTable);
    }

    //--------------------------------------------------------------------------------------------------------
    // Backup
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $fileName: empty
    // @param string $path    : const STORAGE_DIR
    //
    //--------------------------------------------------------------------------------------------------------
    public function backup(String $fileName = NULL, String $path = STORAGE_DIR) : String
    {
        return $this->connectTool->backup($this->grandTable, $fileName, $path);
    }

    //--------------------------------------------------------------------------------------------------------
    // Error
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function error()
    {
        if( $error = $this->connectForge->error() )
        {
            return $error;
        }
        elseif( $error = $this->connectTool->error() )
        {
            return $error;
        }
        elseif( $error = $this->connect->error() )
        {
            return $error;
        }
        else
        {
            return false;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Error
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function stringQuery()
    {
        if( $string = $this->connectForge->stringQuery() )
        {
            return $string;
        }
        elseif( $string = $this->connectTool->stringQuery() )
        {
            return $string;
        }
        elseif( $string = $this->connect->stringQuery() )
        {
            return $string;
        }
        else
        {
            return false;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Post Get
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string &$table, &$data
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _postGet(&$table, &$data)
    {
        $table = $this->grandTable;

        if( is_string($data) )
        {
            $table = $data . ':' . $table;
            $data  = [];
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Call Column
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $method
    // @param array  $params
    // @param string $type
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _callColumn($method, $params, $type)
    {
        if( stristr($method, $type) )
        {
            $func = $type;
            $col  = substr($method, strlen($type));
            $data = NULL;

            if( $func === 'update' )
            {
                if( ! isset($params[1]) )
                {
                    return false;
                }

                return $this->where($col, $params[1])->$func($params[0]);
            }

            return $this->where($col, $params[0])->$func();
        }
    }
}

class_alias('ZN\Requirements\Models\GrandModel', 'GrandModel');
