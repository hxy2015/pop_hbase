<?php
/**
 * Copyright (c) 2008, SARL Adaltas. All rights reserved.
 * Code licensed under the BSD License:
 * http://www.php-pop.org/porte/license.html
 */
use PopHbase\PopHbase;

/**
 * BasicTest
 *
 * @author     David Worms info(at)adaltas.com
 * @copyright  2008-2009 Adaltas
 */
abstract class PopHbaseTestCase extends PHPUnit_Framework_TestCase
{
    public function __get($key)
    {
        switch ($key) {
            case 'config':
                return $this->config();
                break;
            case 'hbase':
                return $this->hbase();
        }
    }

    public function config()
    {
        $config = dirname(__FILE__) . '/config.php';
        if (file_exists($config)) {
            $config = require $config;
        } else {
            echo 'First running the test?' . PHP_EOL;
            echo 'A new file config.php was created in tests directory.' . PHP_EOL;
            echo 'Modify it to feet your HBase installation, then the tests again.' . PHP_EOL;
            exit;
        }
        return $config;
    }

    public function hbase()
    {
        if (isset($this->hbase)) return $this->hbase;
        return $this->hbase = new PopHbase($this->config);
    }

    public function setUp()
    {
        if (!$this->hbase->tables->exists('pop_hbase')) {
            $this->hbase->tables->create('pop_hbase', 'column_test');
        }
    }

    public function tearDown()
    {
    }
}
?>
