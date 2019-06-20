<?php

namespace WC\Models;

use WC\Utilities\DateTimeFormat;
use WC\Utilities\EncodingUtil;
use WC\Utilities\Logger;

class DataService
{
    protected $dbo;
    private $userId = 0;

    public function __construct($dbo) {$this->dbo = $dbo;}

    public function getDBPrefix(): string {return method_exists($this->dbo, 'getPrefix') ? $this->dbo->getPrefix() : '';}

    public static function copyTable(DataService $dbService1, DataService $dbService2, string $tb1, string $primaryKey, array $primaryKeyValues, string $tb2='') {

        $tb1 = $dbService1->tablePrefix($tb1);
        $tb2 = $tb2 ? $dbService2->tablePrefix($tb2) : $dbService2->tablePrefix($tb1);

        if ($dbService1->tableExists($tb1) && $primaryKey && sizeof($primaryKeyValues) > 0)
        {
            $condition = $primaryKey.' IN("'.implode('","', $primaryKeyValues).'")';

            $rows1 = [];
            $rows2 = [];
            if (!$dbService2->tableExists($tb2)) {
                $stm = str_replace($dbService1->quoteName($tb1), $dbService2->quoteName($tb2), $dbService1->getCreateTableStatement($tb1));
                $dbService2->executeQuery($stm);
                $stm = 'SELECT * FROM '.$tb1.' WHERE '.$condition;
                $rows1 = $dbService1->fetchRows($stm);
            }
            else {
                $stm1 = 'SELECT * FROM '.$tb1.' WHERE '.$condition;
                $rows1 = $dbService1->fetchRows($stm1);
                $stm2 = 'SELECT * FROM '.$tb2.' WHERE '.$condition;
                $rows2 = $dbService2->fetchRows($stm2);
            }

            if (!empty($rows1)) {
                $tb1Columns = $dbService1->getTableColumns($tb1);
                $fields = '`' . implode('`,`', $tb1Columns['fields']) . '`';
                $updateQueries = [];
                $insertQueries = [];
                if (!empty($rows2)) {
                    foreach ($rows1 as $k1=>$value1) {
                        foreach ($rows2 as $k2=>$value2) {
                            if ($value1[$primaryKey] !== $value2[$primaryKey]) {
                                $values = [];
                                foreach ($value1 as $value) {
                                    $values[] = $dbService2->quote(is_array($value)||is_object($value)?json_encode($value):$value);
                                }
                                $insertQueries[] = '('.implode(',', $values).')';
                            }
                            else {
                                $values = [];
                                foreach ($value1 as $field=>$value) {
                                    $values[] = $field.'='.$dbService2->quote(is_array($value)||is_object($value)?json_encode($value):$value);
                                }
                                $updateQueries[] = 'UPDATE '.$tb2.' SET '.implode(',', $values).' WHERE '.$primaryKey.'='.$dbService2->quote($value1[$primaryKey]);
                            }
                        }
                    }
                }
                else {
                    foreach ($rows1 as $k=>$value) {
                        $values = [];
                        foreach ($value as $v) {
                            $values[] = $dbService1->quote(is_array($v)||is_object($v)?json_encode($v):$v);
                        }
                        $insertQueries[] = '('.implode(',', $values).')';
                    }
                }

                if (!empty($insertQueries)) {
                    $query = 'INSERT INTO '.$tb2.'('.$fields.') VALUES'.implode(',', $insertQueries);
                    $dbService2->executeQuery($query);
                }
                if (!empty($updateQueries)) {
                    foreach ($updateQueries as $query) {
                        $dbService2->executeQuery($query);
                    }
                }
            }
        }
    }

    public function setUserId($id) {$this->userId = $id;}

    public function getData(string $tb, array $options=[], bool $isSingle=false) {
        $sql = 'SELECT * FROM '.$this->tablePrefix($tb).
            (isset($options['condition'])&&$options['condition']?' WHERE '.$options['condition']:'').
            (isset($options['order_by'])&&$options['order_by']?' ORDER BY '.$options['order_by']:'').
            (isset($options['group_by'])&&$options['group_by']?' GROUP BY '.$options['group_by']:'').
            (isset($options['limit'])&&$options['limit']?' LIMIT '.$options['limit']:'');
        return $isSingle ? $this->fetchRow($sql) : $this->fetchRows($sql);
    }

    public function executeQuery(string $sql): bool {
        try {
            $exec = $this->dbo->setQuery($this->tablePrefix($sql))->execute();
            if (!empty($exec) || (is_bool($exec) && $exec) || (is_resource($exec) && $exec)) {
                return true;
            }
        }
        catch (\RuntimeException $e) {
            Logger::error($e->getMessage());
        }
        return false;
    }

    public function fetchRow(string $sql): array {
        try {
            $row = $this->dbo->setQuery($this->tablePrefix($sql))->loadAssoc();
            if (!empty($row)) {
                $this->jsonDecode($row);
                return $row;
            }
        }
        catch (\RuntimeException $e) {
            Logger::error($e->getMessage());
        }
        return [];
    }

    public function fetchRows(string $sql): array {
        try {
            $rows = $this->dbo->setQuery($this->tablePrefix($sql))->loadAssocList();
            if (!empty($rows)) {
                $this->jsonDecode($rows);
                return $rows;
            }
        }
        catch (\RuntimeException $e) {
            Logger::error($e->getMessage());
        }
        return [];
    }

    public function getTableColumns(string $tb): array {
        try {
            $rows = $this->fetchRows('DESCRIBE ' . $this->tablePrefix($tb));
            if (!empty($rows)) {
                $newRows = ['fields'=>[], 'primary'=>[]];
                foreach ($rows as $i=>$row) {
                    $newRows['fields'][] = $row['Field'];
                    if (strtoupper($row['Key']) === 'PRI') {
                        $newRows['primary'][] = $row['Field'];
                    }
                }
                return $newRows;
            }
        }
        catch (\RuntimeException $e) {
            Logger::error($e->getMessage());
        }
        return [];
    }

    public function getTablePrimaryKeys(string $tb): array {
        try {
            $rows = $this->fetchRows('DESCRIBE ' . $this->quoteName($this->tablePrefix($tb)));
            if (!empty($rows)) {
                $newRows = array();
                foreach ($rows as $i=>$row) {
                    if (strtoupper($row['Key']) === 'PRI') {
                        $newRows[] = $row['Field'];
                    }
                }
                return $newRows;
            }
        }
        catch (\RuntimeException $e) {
            Logger::error($e->getMessage());
        }
        return [];
    }

    public function getTables(): array {
        try {
            $query = 'SHOW TABLES';
            $rows = $this->fetchRows($query);
            if (!empty($rows)) {
                $newRows = [];
                foreach ($rows as $value) {
                    $value = array_values($value);
                    $newRows[] = $value[0];
                }
                return $newRows;
            }
        }
        catch (\Exception $e) {
            Logger::error($e->getMessage());
        }
        return [];
    }

    public function tableExists(string $tb): bool {
        try {
            $tbs = $this->getTables();
            if (!empty($tbs)) {
                $tb = $this->tablePrefix($tb);
                return in_array($tb, $tbs);
            }
        }
        catch (\Exception $e) {
            Logger::error($e->getMessage());
        }
        return false;
    }

    public function createTableFromAnotherTable(string $tb1, string $tb2) {
        $tb1 = $this->tablePrefix($tb1);
        $tb2 = $this->tablePrefix($tb2);
        if ($this->tableExists($tb1) && !$this->tableExists($tb2)) {
            $sql = str_replace($this->quoteName($tb1), $this->quoteName($tb2), $this->getCreateTableStatement($tb1));
            $this->executeQuery($sql);
        }
        return true;
    }

    public function getCreateTableStatement(string $tb): string {
        $query = 'SHOW CREATE TABLE '.$this->tablePrefix($tb);
        $rows = $this->fetchRows($query);
        if (!empty($rows) && isset($rows[0]) && isset($rows[0]['Create Table'])) {
            return $rows[0]['Create Table'];
        }
        return '';
    }

    public function store(string $tb, array $valuePairs) {
        $update = false;
        $fields = $this->getTableColumns($tb);
        $columns = isset($fields['fields']) ? $fields['fields'] : [];
        $primaryKeys = isset($fields['primary']) ? $fields['primary'] : [];
        $primaryKeysCondition = [];
        if (!empty($primaryKeys)) {
            foreach ($valuePairs as $key=>$value) {
                if (in_array($key, $primaryKeys) && $value) {
                    $primaryKeysCondition[] = $this->quoteName($key).'='.$this->quote($value);
                }
            }
            if (!empty($primaryKeysCondition)) {
                $sql = 'SELECT * FROM '.$tb.' WHERE '.implode(' AND ', $primaryKeysCondition);
                $row = $this->fetchRow($sql);
                if (!empty($row)) {
                    $update = true;
                }
            }
        }

        $sql = null;
        $values = array();
        if ($update) {
            foreach ($columns as $column) {
                if (!in_array($column, $primaryKeys)) {
                    if (isset($valuePairs[$column])) {
                        $values[] = $this->quoteName($column) . '=' . $this->quote($valuePairs[$column]);
                    }
                    else if ($column === 'modified_on') {
                        $values[] = $this->quoteName($column) . '=' . $this->quote(DateTimeFormat::getFormatUnix());
                    }
                    else if ($column === 'modified_by') {
                        $values[] = $this->quoteName($column) . '=' . $this->quote($this->userId);
                    }
                }
            }
            $sql = 'UPDATE '.$tb.' SET ' . implode(',', $values) . ' WHERE ' . implode(' AND ', $primaryKeysCondition);
        }
        else {
            $fields = array();
            foreach ($columns as $column) {
                if (isset($valuePairs[$column])) {
                    $values[] = $this->quote($valuePairs[$column]);
                    $fields[] = $this->quoteName($column);
                }
                else if ($column === 'modified_on' || $column === 'created_on') {
                    $values[] = $this->quote(DateTimeFormat::getFormatUnix());
                    $fields[] = $this->quoteName($column);
                }
                else if ($column === 'modified_by' || $column === 'created_by') {
                    $values[] = $this->quote($this->userId);
                    $fields[] = $this->quoteName($column);
                }
            }
            $sql = 'INSERT INTO '.$tb.'('.implode(',', $fields).') VALUES('.implode(',', $values).')';
        }
        
        if ($sql !== null) {
            if ($this->executeQuery($this->tablePrefix($sql)) && !$update) {
                if (method_exists($this->dbo, 'insertid')) {
                    return $this->dbo->insertid();
                }
                else if (method_exists($this->dbo, 'lastInsertId')) {
                    return $this->dbo->lastInsertId();
                }
            }
        }

        return null;
    }

    public function deleteData(string $tb, array $valuePairs): bool {
        $cond = [];
        foreach ($valuePairs as $k=>$v) {
            $cond[] = $this->quoteName($k).'='.$this->quote($v);
        }
        if (!empty($cond)) {
            return $this->executeQuery('DELETE FROM '.$this->tablePrefix($tb).' WHERE '.implode(' AND ', $cond));
        }
        return false;
    }

    public function deleteTables(array $tb) {
        if (!empty($tb)) {
            $sql = 'DROP TABLE IF EXISTS '.implode(',', $tb);
            return $this->executeQuery($this->tablePrefix($sql));
        }
        return false;
    }

    public function quote($o): string {return $this->dbo->quote(is_array($o)||is_object($o)?json_encode($o):(!empty($o)?$o:''));}

    public function quoteName(string $str): string {return method_exists($this->dbo, 'quoteName') ? $this->dbo->quoteName($str) : '`'.$str.'`';}

    public function jsonDecode(array &$arr) {
        foreach ($arr as $i=>$v) {
            if (is_array($v)) {
                $this->jsonDecode($arr[$i]);
            }
            else if (!is_numeric($v) && EncodingUtil::isValidJSON($v)) {
                $arr[$i] = json_decode($v, true);
                $this->jsonDecode($arr[$i]);
            }
        }
    }

    public function tablePrefix(string $tb): string {
        if (method_exists($this->dbo, 'getPrefix')) {
            return str_replace('#__', $this->dbo->getPrefix(), $tb);
        }
        return $tb;
    }

    public function import($sqlDump, $user, $pwd, $db, $host, $dbprefix) {(new DBImporter())->executeByCredentials($sqlDump, $user, $pwd, $db, $host, $dbprefix);}

    private function field($field) {
        if (is_string($field)) {
            return $this->quoteName($field);
        }
        else if (is_array($field)) {
            return '`'.implode('`,`', $field).'`';
        }
        return '*';
    }
}