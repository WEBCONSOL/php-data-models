<?php

namespace WC\Models;

use mysqli;

class DBUtil
{
    public static function remove(array $config, string $component='', string $env='')
    {
        if ($component && $env && isset($config[$component]) && isset($config[$component][$env])) {
            $config = $config[$component][$env];
            foreach ($config as $service=>$data) {
                self::_remove($data);
            }
        }
        else if ($component && isset($config[$component])) {
            $config = $config[$component];
            foreach ($config as $env=>$data1) {
                foreach ($data1 as $service=>$data2) {
                    self::remove($data2);
                }
            }
        }
        else {
            foreach ($config as $comp=>$data1) {
                foreach ($data1 as $env=>$data2) {
                    foreach ($data2 as $service=>$data3) {
                        self::remove($data3);
                    }
                }
            }
        }
    }

    private static function _remove(array $data) {
        if (isset($data['host']) && isset($data['port']) && isset($data['dbname']) && isset($data['username']) && isset($data['password'])) {
            try {
                $conn = new \PDO('mysql:host='.$data['host'].';port='.$data['port'], $data['username'], $data['password']);
                if ($conn) {
                    $sql = 'DROP DATABASE '.$data['dbname'];
                    $conn->prepare($sql)->execute();
                }
            }
            catch (\Exception $e) {}
        }
    }
}