<?php

/**
 * Copyright (c) 2008, SARL Adaltas. All rights reserved.
 * Code licensed under the BSD License:
 * http://www.php-pop.org/pop_hbase/license.html
 */

/**
 * Wrap an Hbase table row.
 *
 * @author		David Worms info(at)adaltas.com
 */
namespace PopHbase;

class PopHbaseRow{
	
	public $hbase;
	public $key;
	
	/**
	 * Contruct a new row instance.
	 * 
	 * The identified row does not have to be yet persisted in HBase, it
	 * will automatically be created if not yet present.
	 * 
	 * @param PopHbase $hbase
	 * @param string $table
	 * @param string $key
	 */
	public function __construct(PopHbase $hbase,$table,$key){
		$this->hbase = $hbase;
		$this->table = $table;
		$this->key = $key;
	}
	
	public function __get($column){
		return $this->get($column);
	}
	
	/**
	 * Deletes an entire row, a entire column family, or specific cell(s).
	 * 
	 * Delete a entire row
	 *    $hbase
	 *        ->table('my_table')
	 *            ->row('my_row')
	 *                ->delete();
	 * 
	 * Delete a entire column family
	 *    $hbase
	 *        ->table('my_table')
	 *            ->row('my_row')
	 *                ->delete('my_column_family');
	 * 
	 * Delete all the cells in a column
	 *    $hbase
	 *        ->table('my_table')
	 *            ->row('my_row')
	 *                ->delete('my_column_family:my_column');
	 * 
	 * Delete a specific cell
	 *    $hbase
	 *        ->table('my_table')
	 *            ->row('my_row')
	 *                ->delete('my_column_family:my_column','my_timestamp');
	 */
	public function delete(){
		$args = func_get_args();
		$url = $this->table .'/'.$this->key;
		switch(count($args)){
			case 1;
				// Delete a column or a column family
				$url .= '/'.$args[0];
			case 2:
				// Delete a specific cell
				$url .= '/'.$args[1];
		}
		return $this->hbase->request->delete($url);
	}
	
	
	/**
	 * Retrieve a value from a column row.
	 * 
	 * Usage:
	 * 
	 *    $hbase
	 *        ->table('my_table')
	 *            ->row('my_row')
	 *                ->get('my_column_family:my_column');
	 */
	public function get($column, $timestamp=null){
        $getUrl = $this->table .'/'.$this->key.'/'.$column;
        $getUrl .= (isset($timestamp)) ? '/'.($timestamp+1).','.($timestamp+2) : '';

		$body = $this->hbase->request->get($getUrl)->body;

		if(is_null($body)){
			return null;
		}

        if (isset($timestamp)) {
            $result_arr = $this->parseMultiRowBody($body);
            if (empty($result_arr)) {
                return null;
            }
            return $result_arr[0]['data'];
        }

		return base64_decode($body['Row'][0]['Cell'][0]['$']);
	}

    public function multiGet($column, $count = 100)
    {
        $body = $this->hbase->request->get($this->table .'/'.$this->key.'/'.$column . '/?v=' . $count)->body;
        if(is_null($body)){
            return null;
        }
        return $this->parseMultiRowBody($body);
    }

    private function parseMultiRowBody($body)
    {
        $bodyArray = json_decode($body, true);

        $result = array();

        if (isset($bodyArray['Row'][0]['Cell']) && $bodyArray['Row'][0]['Cell'] > 0) {
            foreach ($bodyArray['Row'][0]['Cell'] as $key => $value) {
                $result[] = array(
                    'timestamp' => $value['timestamp'],
                    'data'      => base64_decode($value['$'])
                );
            }
        }

        return $result;
    }

	/**
	 * Create or update a column row.
	 * 
	 * Usage:
	 * 
	 *    $hbase
	 *        ->table('my_table')
	 *            ->row('my_row')
	 *                ->put('my_column_family:my_column','my_value');
	 * 
	 * Note, in HBase, creation and modification of a column value is the same concept.
	 */
	public function put($column,$value,$timestamp=null){
        if (!isset($timestamp)) {
            $value = array(
                'Row' => array(array(
                    'key' => base64_encode($this->key),
                    'Cell' => array(array(
                        'column' => base64_encode($column),
                        '$' => base64_encode($value)
                    ))
                ))
            );
        }
		$this->hbase->request->put($this->table .'/'.$this->key.'/'.$column,$value,$timestamp);
		return $this;
	}

}
