<?php

/**
 * Copyright (c) 2008, SARL Adaltas. All rights reserved.
 * Code licensed under the BSD License:
 * http://www.php-pop.org/pop_config/license.html
 */

use PopHbase\PopHbaseConnectionCurl;

/**
 * Test the add method.
 * 
 * @author		David Worms info(at)adaltas.com
 *
 */
class HbaseCurlExecuteTest extends PopHbaseTestCase{
	public function testReturn(){
		$connection = new PopHbaseConnectionCurl($this->config);
		$version = $connection->execute('get','version')->getBody();
		$this->assertTrue(is_array($version));
		$this->assertSame(array('REST','JVM','OS','Server','Jersey'),array_keys($version));
		
	}
}
