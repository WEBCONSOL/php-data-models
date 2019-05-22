<?php

namespace WC\Models;

use WC\Utilities\Logger;

class JoomlaDBService
{
    public static function fetchTableData($dbo, string $tb, string $condition): array {
        if ($dbo instanceof \JDatabaseDriver || $dbo instanceof \FOFDatabaseDriver) {
            $query = 'SELECT * FROM '.$tb.' WHERE '.$condition;
            try {
                return $dbo->setQuery($query)->loadAssocList();
            }
            catch (\RuntimeException $e) {
                Logger::error($e->getMessage());
            }
        }
        return [];
    }

    public static function fetchRow($dbo, string $query): array {
        if ($dbo instanceof \JDatabaseDriver || $dbo instanceof \FOFDatabaseDriver) {
            try {
                return $dbo->setQuery($query)->loadAssoc();
            }
            catch (\RuntimeException $e) {
                Logger::error($e->getMessage());
            }
        }
        return [];
    }

    public static function fetchRows($dbo, string $query): array {
        if ($dbo instanceof \JDatabaseDriver || $dbo instanceof \FOFDatabaseDriver) {
            try {
                return $dbo->setQuery($query)->loadAssocList();
            }
            catch (\RuntimeException $e) {
                Logger::error($e->getMessage());
            }
        }
        return [];
    }

    public static function delete($dbo, string $tb, string $condition): bool {
        if ($dbo instanceof \JDatabaseDriver || $dbo instanceof \FOFDatabaseDriver) {
            $query = 'DELETE FROM '.$tb.' WHERE '.$condition;
            try {
                $dbo->setQuery($query)->execute();
                return true;
            }
            catch (\RuntimeException $e) {
                Logger::error($e->getMessage());
            }
        }
        return false;
    }

    public static function insert($dbo, string $tb, array $values, string $primaryKey='') {
        if ($dbo instanceof \JDatabaseDriver || $dbo instanceof \FOFDatabaseDriver) {
            $fields = $dbo->getTableColumns($tb);
            $queryFields = [];
            $queryValues = [];
            foreach ($fields as $f=>$t) {
                if (($primaryKey && $f !== $primaryKey) || !$primaryKey) {
                    if (isset($values[$f])) {
                        $queryFields[] = $dbo->quoteName($f);
                        $queryValues[] = $dbo->quote($values[$f]);
                    }
                    else {
                        $v = self::getDefaultValueByFieldType(strtolower($t));
                        if ($v !== null) {
                            $queryFields[] = $dbo->quoteName($f);
                            $queryValues[] = $dbo->quote($v);
                        }
                    }
                }
            }
            $query = 'INSERT INTO '.$tb.'('.implode(',', $queryFields).') VALUES('.implode(',', $queryValues).')';
            try {
                $dbo->setQuery($query)->execute();
                return $dbo->insertid();
            }
            catch (\RuntimeException $e) {
                Logger::error($e->getMessage());
            }
        }
        return 0;
    }

    private static function getDefaultValueByFieldType(string $type) {
        if (in_array($type, ['float','decimal','double','real']) || strpos($type, 'int') !== false) {
            return 0;
        }
        if (in_array($type, ['varchar', 'text', 'char']) || strpos($type, 'text') !== false) {
            return '';
        }
        return null;
    }
}