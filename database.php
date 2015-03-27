<?php

/**
 * PDO static wrapper for db - basics
 * © Tom Barbořík 2015
 */

class Database {
    private static $user = null;
    private static $password = null;
    private static $name = null;
    private static $driver = 'mysql';
    private static $host = null;
    private static $pdo = null;
    private static $timezone = 'GMT';

    private static $int = array('longlong', 'long', 'integer', 'int', 'byte', 'int24', 'short', 'tiny');
    private static $string = array('string', 'var_string', 'blob');
    private static $date_time = array('date', 'datetime', 'timestamp', 'time', 'year');

    const ASCENDING = 'ASC'; //  česky = vzestupně
    const DESCENDING = 'DESC'; // česky = sestupně

    /**
     * Adds config values
     * @param string $user
     * @param string $password
     * @param string $name
     * @param string $host
     * @param string $driver
     * @param string $timezone
     */
    private static function ini_cofig($user, $password, $name, $host, $driver = "mysql", $timezone = 'GMT') {
        self::$user = $user;
        self::$password = $password;
        self::$name = $name;
        self::$host = $host;
        self::$driver = $driver;
        self::$timezone = $timezone;
    }

    /**
     * Adds config values from config file
     * @param string $config_name
     */
    private static function ini_cofig_from_file($config_name = 'database') {
        $path = dirname(__FILE__).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$config_name.'.php';
        $config_array = @include($path);
        foreach ($config_array as $name => $config) {
            self::$$name = $config;
        }
    }

    /**
     * Makes connection to DB and than calls method if available
     * @param string $after
     */
    private static function connect($after = '')
    {
        if (is_null(self::$user))
            self::ini_cofig_from_file();
        self::$pdo = new PDO(self::$driver . ':host='.self::$host.';dbname='.self::$name, self::$user, self::$password);

        if (is_callable($after))
            $after(self::$pdo);
    }

    /**
     * Does query
     * @param string $query
     * @return mixed
     */
    public static function query($query)
    {
        if (is_null(self::$pdo))
            self::connect();
        return self::$pdo->query($query);
    }

    /**
     * Selectes all rows and chosen columns from table and returns these
     * with some restrictions you give it if you want
     * @param string $table_name
     * @param array $col_names
     * @param array $where
     * @param int $limit
     * @param int $offset
     * @param string $order_by
     * @param string $type
     * @return array
     */
    public static function select($table_name, $col_names = array(), $where = array(), $limit = 0, $offset = 0, $order_by = '', $type = self::ASCENDING)
    {
        if (is_null(self::$pdo))
            self::connect();
        $sql = "SELECT";

        $first = true;
        foreach ($col_names as $name) {
            if (!$first)
                $sql .= ",";
            $sql .= ' `'.mysql_real_escape_string($name).'`';
            $first = false;
        }
        if (empty($col_names))
            $sql .= " *";
        $sql .= " FROM `$table_name`";
        self::add_attributes($sql, $where, $limit, $offset, $order_by, $type);

        $select = self::$pdo->query($sql);

        return self::process_select($select);
    }

    /**
     * Inserts given values into table
     * @param string $table_name
     * @param array $values
     * @return mixed
     */
    public static function insert($table_name, $values)
    {
        if (is_null(self::$pdo))
            self::connect();

        $columns = self::get_columns($table_name);
        $insert_array = array();
        $sql = "INSERT INTO `$table_name` (";

        $insert = '';
        $first = true;

        foreach ($columns as $column) {
            if (isset($values[$column])) {
                $value = $values[$column];
                if (!$first) {
                    $sql .= ', ';
                    $insert .= ', ';
                }

                $sql .= "`$column`";
                $insert .= ":$column";

                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                $insert_array[":$column"] = $value;

                $first = false;
            }
        }
        $sql .= ") VALUES ($insert)";
        $q = self::$pdo->prepare($sql);
        return $q->execute($insert_array);
    }

    /**
     * Updates records in db with values by conditions
     * @param string $table_name
     * @param array $values
     * @param array $where
     * @param int $limit
     * @param int $offset
     * @param string $order_by
     * @param string $type
     * @return mixed
     */
    public static function update($table_name, $values, $where = array(), $limit = 0, $offset = 0, $order_by = '', $type = self::ASCENDING)
    {
        if (is_null(self::$pdo))
            self::connect();
        $columns = self::get_columns($table_name);
        $update_array = array();
        $sql = "UPDATE `$table_name` SET";

        $first = true;
        foreach ($columns as $column) {
            if (isset($values[$column])) {
                $value = $values[$column];
                if (!$first) {
                    $sql .= ',';
                }
                $sql .= " `$column` = ?";

                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                $update_array[] = $value;

                $first = false;
            }
        }
        self::add_attributes($sql, $where, $limit, $offset, $order_by, $type);
        $q = self::$pdo->prepare($sql);
        return $q->execute($update_array);
    }

    /**
     * Deletes all rows which match conditions
     * @param $table_name
     * @param array $where
     * @param int $limit
     * @param int $offset
     * @param string $order_by
     * @param string $type
     * @return bool
     */
    public static function delete($table_name, $where = array(), $limit = 0, $offset = 0, $order_by = '', $type = self::ASCENDING)
    {
        if (is_null(self::$pdo))
            self::connect();

        $sql = "DELETE FROM `$table_name`";
        self::add_attributes($sql, $where, $limit, $offset, $order_by, $type);

        $query = self::$pdo->prepare($sql);
        return $query->execute();
    }

    /**
     * Gets column names of table
     * @param string $table_name
     * @return array
     */
    public static function get_columns($table_name)
    {
        if (is_null(self::$pdo))
            self::connect();

        $sql = "DESCRIBE `$table_name`";
        $query = self::$pdo->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Adds attributes to sql you gave it
     * @param string $sql
     * @param array $where
     * @param int $limit
     * @param int $offset
     * @param string $order_by
     * @param string $type
     * @return string
     */
    private static function add_attributes(&$sql, $where = array(), $limit = 0, $offset = 0, $order_by = '', $type = self::ASCENDING)
    {
        if (!empty($where)) {
            $sql .= ' WHERE';

            $first = true;
            foreach ($where as $type => $statement) {
                if (!$first)
                    if (is_numeric($type) || ($type != 'AND' && $type != 'OR'))
                        $sql .= ' AND';
                    else
                        $sql .= ' '.$type;

                $sql .= ' '.$statement;

                $first = false;
            }
        }

        if (!empty($order_by))
            $sql .= " ORDER BY $order_by $type";
        if ($limit > 0)
            $sql .= " LIMIT $offset, $limit";
    }

    /**
     * Transforms results of selection to array with key as numbers or column name
     * and makes them data types what they supposed to be
     * 95% works only with mysql
     * @param $select
     * @param bool $cast
     * @param bool $assoc
     * @return array
     */
    private static function process_select($select, $cast = true, $assoc = true)
    {
        $return = array();
        $i = 0;
        foreach ($select as $row) {
            $j = 0;
            foreach ($row as $col_name => $col_value) {
                if ($assoc && is_numeric($col_name))
                    continue;
                elseif (!$assoc && !is_numeric($col_name))
                    continue;
                $value = $col_value;

                if ($cast) {
                    $meta = $select->getColumnMeta($j);
                    $type = $meta['pdo_type'];
                    $native = strtolower($meta['native_type']);
                    $length = $meta["len"];

                    if (in_array($native, self::$int) || $type == PDO::PARAM_INT)
                        if ($length == 1)
                            $value = (bool) $value;
                        else
                            $value = (int) $value;
                    elseif (in_array($native, self::$date_time))
                        $value = new DateTime($value, new DateTimeZone(self::$timezone));
                    elseif (in_array($native, self::$string) || $type == PDO::PARAM_STR)
                        $value = (string) $value;
                    elseif ($type == PDO::PARAM_BOOL)
                        $value = (bool) $value;
                }

                $return[$i][$col_name] = $value;
                $j++;
            }
            $i++;
        }

        return $return;
    }
}