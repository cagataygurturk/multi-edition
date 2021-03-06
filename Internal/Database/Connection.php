<?php namespace ZN\Database;

use Support, Arrays, Config;
use ZN\Database\Exception\InvalidArgumentException;

class Connection implements ConnectionInterface
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
    // Results
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $results;

    //--------------------------------------------------------------------------------------------------------
    // Drivers
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $drivers =
    [
        'odbc',
        'mysqli',
        'pdo',
        'oracle',
        'postgres',
        'sqlite',
        'sqlserver',
        'pdo:mysql',
        'pdo:postgres',
        'pdo:sqlite',
        'pdo:sqlserver',
        'pdo:odbc'
    ];

    //--------------------------------------------------------------------------------------------------------
    // Config
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $config;

    //--------------------------------------------------------------------------------------------------------
    // Prefix
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string
    //
    //--------------------------------------------------------------------------------------------------------
    protected $prefix;

    //--------------------------------------------------------------------------------------------------------
    // Secure
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $secure = [];

    //--------------------------------------------------------------------------------------------------------
    // Table
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string
    //
    //--------------------------------------------------------------------------------------------------------
    protected $table;

    //--------------------------------------------------------------------------------------------------------
    // Table Name
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string
    //
    //--------------------------------------------------------------------------------------------------------
    protected $tableName;

    //--------------------------------------------------------------------------------------------------------
    // String Query
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string
    //
    //--------------------------------------------------------------------------------------------------------
    protected $stringQuery;

    //--------------------------------------------------------------------------------------------------------
    // Select Functions
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string
    //
    //--------------------------------------------------------------------------------------------------------
    protected $selectFunctions;

    //--------------------------------------------------------------------------------------------------------
    // Column
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $column;

    //--------------------------------------------------------------------------------------------------------
    // Driver
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string
    //
    //--------------------------------------------------------------------------------------------------------
    protected $driver;

    //--------------------------------------------------------------------------------------------------------
    // String
    //--------------------------------------------------------------------------------------------------------
    //
    // @var string
    //
    //--------------------------------------------------------------------------------------------------------
    protected $string;

    //--------------------------------------------------------------------------------------------------------
    // Construct
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $config
    //
    //--------------------------------------------------------------------------------------------------------
    public function __construct(Array $config = [])
    {
        $this->config = array_merge(Config::get('Database', 'database'), $config);
        $this->db = $this->_run();

        $this->prefix       = $this->config['prefix'];
        Properties::$prefix = $this->prefix;

        $this->db->connect($this->config);
    }

    //--------------------------------------------------------------------------------------------------------
    // Different Connection
    //--------------------------------------------------------------------------------------------------------
    //
    // @param mixed $connectName
    //
    //--------------------------------------------------------------------------------------------------------
    public function differentConnection($connectName = NULL) : Connection
    {
        $getCalledClass = get_called_class();

        if( empty($connectName) )
        {
            return new $getCalledClass;
        }

        $config          = Config::get('Database', 'database');
        $configDifferent = $config['differentConnection'];

        if( is_string($connectName) && isset($configDifferent[$connectName]) )
        {
            $connection = $configDifferent[$connectName];
        }
        elseif( is_array($connectName) )
        {
            $connection = $connectName;
        }
        else
        {
            throw new InvalidArgumentException('Error', 'invalidInput', 'Mixed $connectName');
        }

        foreach( $config as $key => $val )
        {
            if( $key !== 'differentConnection' )
            {
                if( ! isset($connection[$key]) )
                {
                    $connection[$key] = $val;
                }
            }
        }

        return new $getCalledClass($connection);
    }

    //--------------------------------------------------------------------------------------------------------
    // Variable Types
    //--------------------------------------------------------------------------------------------------------
    //
    // @param  void
    //
    //--------------------------------------------------------------------------------------------------------
    public function vartypes() : Array
    {
        return $this->db->vartypes();
    }

    //--------------------------------------------------------------------------------------------------------
    // Table
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $table
    //
    //--------------------------------------------------------------------------------------------------------
    public function table(String $table) : Connection
    {
        $this->table       = ' '.$this->prefix.$table.' ';
        $this->tableName   = $this->prefix.$table;
        Properties::$table = $this->tableName;

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Column
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $col
    // @param mixed  $val
    //
    //--------------------------------------------------------------------------------------------------------
    public function column(String $col, $val = NULL) : Connection
    {
        $this->column[$col] = $val;

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Column
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $col
    // @param mixed  $val
    //
    //--------------------------------------------------------------------------------------------------------
    public function string() : Connection
    {
        $this->string = true;

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // String Query
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function stringQuery() : String
    {
        if( ! empty($this->stringQuery) )
        {
            return $this->stringQuery;
        }

        return false;
    }

    //--------------------------------------------------------------------------------------------------------
    // Secure
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $data
    //
    //--------------------------------------------------------------------------------------------------------
    public function secure(Array $data) : Connection
    {
        $this->secure = $data;

        return $this;
    }

    //--------------------------------------------------------------------------------------------------------
    // Func
    //--------------------------------------------------------------------------------------------------------
    //
    // @param variadic $args
    //
    //--------------------------------------------------------------------------------------------------------
    public function func(...$args)
    {
        $array = Arrays::removeFirst($args);
        $math  = $this->_math(isset($args[0]) ? \Autoloader::upper($args[0]) : false, $array);

        if( $math->return === true )
        {
            return $math->args;
        }
        else
        {
            $this->selectFunctions[] = $math->args;

            return $this;
        }
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
        return $this->db->error();
    }

    //--------------------------------------------------------------------------------------------------------
    // Close
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function close()
    {
        return $this->db->close();
    }

    //--------------------------------------------------------------------------------------------------------
    // Version
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function version()
    {
        return $this->db->version();
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Escape String Add Nail
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $column
    // @param string $value
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _excapeStringAddNail($value, $numeric = false)
    {
        if( $numeric === true && is_numeric($value) )
        {
            return $value;
        }

        return presuffix($this->db->realEscapeString($value), "'");
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Exp
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $column
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _exp($column = '', $exp = 'exp')
    {
        return stristr($column, $exp . ':');
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Clear Exp
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $column
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _clearExp($column, $exp = 'exp')
    {
        return str_ireplace($exp . ':', '', $column);
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Clear Nail
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $value
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _clearNail($value)
    {
        return trim($value, '\'');
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Convert Type
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $column
    // @param string $value
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _convertType(&$column = '', &$value = '')
    {
        if( $this->_exp($column, 'int') )
        {
            $value  = (int) $this->_clearNail($value);
            $column = $this->_clearExp($column, 'int');
        }

        if( $this->_exp($column, 'float') )
        {
            $value  = (float) $this->_clearNail($value);
            $column = $this->_clearExp($column, 'float');
        }

        if( $this->_exp($column, 'exp') )
        {
            $column = $this->_clearExp($column);
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Query Security
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $query
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _querySecurity($query)
    {
        if( isset($this->secure) )
        {
            $secure = $this->secure;

            $secureParams = [];

            if( is_numeric(key($secure)) )
            {
                $strex  = explode('?', $query);
                $newstr = '';

                if( ! empty($strex) ) for( $i = 0; $i < count($strex) - 1; $i++ )
                {
                    $sec = $secure[$i] ?? NULL;

                    $newstr .= $strex[$i].$this->db->realEscapeString($sec);
                }

                $query = $newstr;
            }
            else
            {
                foreach( $this->secure as $k => $v )
                {
                    $this->_convertType($k, $v);

                    $secureParams[$k] = $this->db->realEscapeString($v);
                }
            }

            $query = str_replace(array_keys($secureParams), array_values($secureParams), $query);
        }

        if( ($this->config['queryLog'] ?? NULL) === true )
        {
            \Logger::report('DatabaseQueries', $query, 'DatabaseQueries');
        }

        $this->stringQuery = $query;

        $this->secure = [];

        return $query;
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Math
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $type
    // @param array  $args
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _math($type, $args)
    {
        $type    = \Autoloader::upper($type);
        $getLast = Arrays::getLast($args);

        $asparam = ' ';

        if( $getLast === true )
        {
            $args   = Arrays::removeLast($args);
            $return = true;

            $as     = Arrays::getLast($args);

            if( stripos(trim($as), 'as') === 0 )
            {
                $asparam .= $as;
                $args   = Arrays::removeLast($args);
            }
        }
        else
        {
            $return = false;
        }

        if( stripos(trim($getLast), 'as') === 0 )
        {
            $asparam .= $getLast;
            $args     = Arrays::removeLast($args);
        }

        $args = $type.'('.rtrim(implode(',', $args), ',').')'.$asparam;

        return (object)array
        (
            'args'   => $args,
            'return' => $return
        );
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Run
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _run($settings = [])
    {
        $this->driver = explode(':', $this->config['driver'])[0];

        return $this->_drvlib('Driver', $settings);
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Driver Library
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $driver
    // @param string $suffix
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _drvlib($suffix = 'Driver', $settings = [])
    {
        Support::driver($this->drivers, $this->driver);

        $class = 'ZN\Database\Drivers\\'.$this->driver.$suffix;

        return new $class($settings);
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Nail Encode
    //--------------------------------------------------------------------------------------------------------
    //
    // @param  string $data
    //
    //--------------------------------------------------------------------------------------------------------
    protected function nailEncode($data)
    {
        return str_replace(["'", "\&#39;", "\\&#39;"], "&#39;", $data);
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Run Query
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $query
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _runQuery($query)
    {
        if( $this->string === true )
        {
            $this->string = NULL;
            return $query;
        }

        $this->db->query($this->_querySecurity($query), $this->secure);

        return ! (bool) $this->db->error();
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected Run Exec Query
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $query
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _runExecQuery($query)
    {
        if( $this->string === true )
        {
            $this->string = NULL;
            return $query;
        }

        $this->db->exec($this->_querySecurity($query), $this->secure);

        return ! (bool) $this->db->error();
    }

    //--------------------------------------------------------------------------------------------------------
    // Protected P
    //--------------------------------------------------------------------------------------------------------
    //
    // @param var    $p
    // @param string $name
    //
    //--------------------------------------------------------------------------------------------------------
    protected function _p($p = NULL, $name = 'table')
    {
        if( $name === 'prefix' )
        {
            return $this->$name.$p;
        }

        if( $name === 'table' )
        {
            $p = $this->prefix.$p;
        }

        if( ! empty($this->$name) )
        {
            $data = $this->$name;

            $this->$name = NULL;

            return $data;
        }
        else
        {
            return $p;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Desctruct
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function __destruct()
    {
        $this->results = NULL;
        $this->db->close();
        $this->db->closeConnection();
    }
}
