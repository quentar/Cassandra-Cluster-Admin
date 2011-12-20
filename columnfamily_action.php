<?php
	/*
		Cassandra Cluster Admin
		
		@author Sébastien Giroux
		@copyright All rights reserved - 2011
	*/

	require('include/kernel.inc.php');
	require('include/verify_login.inc.php');
	
	$included_header = false;
	$is_valid_action = false;
	$action = '';
	if (isset($_GET['action'])) $action = $_GET['action'];
	
	/*
		Submit form edit column family
	*/
	
	if (isset($_POST['btn_edit_columnfamily'])) {
		$column_type = $_POST['column_type'];
		
		$comment = $_POST['comment'];
		$row_cache_size = $_POST['row_cache_size'];
		$row_cache_save_period_in_seconds = $_POST['row_cache_save_period_in_seconds'];
		$key_cache_size = $_POST['key_cache_size'];
		$key_cache_save_period_in_seconds = $_POST['key_cache_save_period_in_seconds'];
		$read_repair_chance = $_POST['read_repair_chance'];
		$gc_grace_seconds = $_POST['gc_grace_seconds'];
		$memtable_operations_in_millions = $_POST['memtable_operations_in_millions'];
		$memtable_throughput_in_mb = $_POST['memtable_throughput_in_mb'];
		$memtable_flush_after_mins = $_POST['memtable_flush_after_mins'];
		$default_validation_class = $_POST['default_validation_class'];
		$min_compaction_threshold = $_POST['min_compaction_threshold'];
		$max_compaction_threshold = $_POST['max_compaction_threshold'];		
		
		$keyspace_name = $_POST['keyspace_name'];
		$columnfamily_name = $_POST['columnfamily_name'];
			
		$attrs = array('column_type' => $column_type,
					   'comment' => $comment,
					   'row_cache_size' => $row_cache_size,
					   'row_cache_save_period_in_seconds' => $row_cache_save_period_in_seconds,
					   'key_cache_size' => $key_cache_size,
					   'key_cache_save_period_in_seconds' => $key_cache_save_period_in_seconds,
					   'read_repair_chance' => $read_repair_chance,
					   'gc_grace_seconds' => $gc_grace_seconds,
					   'memtable_operations_in_millions' => $memtable_operations_in_millions,
					   'memtable_throughput_in_mb' => $memtable_throughput_in_mb,
					   'memtable_flush_after_mins' => $memtable_flush_after_mins,
					   'default_validation_class' => $default_validation_class,
					   'min_compaction_threshold' => $min_compaction_threshold,
					   'max_compaction_threshold' => $max_compaction_threshold);
		
		if (isset($_POST['comparator_type']))
			$attrs['comparator_type'] = $_POST['comparator_type'];
		
		if ($column_type == 'Super' && isset($_POST['subcomparator_type'])) {
			$attrs['subcomparator_type'] = $_POST['subcomparator_type'];
		}
		else {
			$attrs['subcomparator_type'] = null;
		}
		
		try {
			$time_start = microtime(true);
			$sys_manager->alter_column_family($keyspace_name, $columnfamily_name, $attrs);
			$time_end = microtime(true);
			
			$vw_vars['success_message'] = displaySuccessMessage('edit_columnfamily',array('columnfamily_name' => $columnfamily_name,'query_time' => getQueryTime($time_start,$time_end)));
		}
		catch(Exception $e) {
			$vw_vars['error_message'] = displayErrorMessage('edit_columnfamily',array('columnfamily_name' => $columnfamily_name,'message' => $e->getMessage()));
		}
	}	
		
	/*
		Edit a column family
	*/
	
	if ($action == 'edit') {	
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
	
		$vw_vars['cluster_name'] = $sys_manager->describe_cluster_name();		
		$vw_vars['keyspace_name'] = $keyspace_name;
		$vw_vars['columnfamily_name'] = $columnfamily_name;
		
		$cf = ColumnFamilyHelper::getCFInKeyspace($keyspace_name,$columnfamily_name);
		
		$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Edit Column Family';
		
		$included_header = true;
		echo getHTML('header.php');
		
		if ($cf) {			
			$vw_vars['column_type'] = $cf->column_type;
			$vw_vars['comparator_type'] = $cf->comparator_type;
			$vw_vars['subcomparator_type'] = $cf->subcomparator_type;
			$vw_vars['comment'] = $cf->comment;
			$vw_vars['row_cache_size'] = $cf->row_cache_size;
			$vw_vars['key_cache_size'] = $cf->key_cache_size;
			$vw_vars['read_repair_chance'] = $cf->read_repair_chance;
			$vw_vars['gc_grace_seconds'] = $cf->gc_grace_seconds;
			$vw_vars['default_validation_class'] = $cf->default_validation_class;
			$vw_vars['id'] = $cf->id;
			$vw_vars['min_compaction_threshold'] = $cf->min_compaction_threshold;
			$vw_vars['max_compaction_threshold'] = $cf->max_compaction_threshold;
			$vw_vars['row_cache_save_period_in_seconds'] = $cf->row_cache_save_period_in_seconds;
			$vw_vars['key_cache_save_period_in_seconds'] = $cf->key_cache_save_period_in_seconds;
			$vw_vars['memtable_flush_after_mins'] = $cf->memtable_flush_after_mins;
			$vw_vars['memtable_throughput_in_mb'] = $cf->memtable_throughput_in_mb;
			$vw_vars['memtable_operations_in_millions'] = $cf->memtable_operations_in_millions;		
			
			if (!isset($vw_vars['success_message'])) $vw_vars['success_message'] = '';
			if (!isset($vw_vars['error_message'])) $vw_vars['error_message'] = '';
			
			$vw_vars['mode'] = 'edit';
			$vw_vars['thrift_api_version'] = $sys_manager->describe_version();
			
			echo getHTML('create_edit_columnfamily.php',$vw_vars);
		}
		else {
			echo displayErrorMessage('columnfamily_doesnt_exists', array('column_name' => $columnfamily_name));
		}
	}
	
	/*
		Drop a column family
	*/	
	
	if ($action == 'drop') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
	
		$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Drop Column Family';
	
		try {
			$sys_manager->drop_column_family($keyspace_name, $columnfamily_name);
			redirect('describe_keyspace.php?keyspace_name='.$keyspace_name.'&deleted_cf=1');
		}
		catch (Exception $e) {			
			$included_header = true;
			echo getHTML('header.php');
			echo displayErrorMessage('drop_columnfamily',array('message' => $e->getMessage()));
		}
	}	
		
	/*
		Submit form create a secondary index
	*/
	
	if (isset($_POST['btn_create_secondary_index'])) {
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}

		$column_name = $_POST['column_name'];		
		$data_type = $_POST['data_type'];
		$index_name = $_POST['index_name'];
		
		try {
			$time_start = microtime(true);
			$sys_manager->create_index($keyspace_name,$columnfamily_name,$column_name,$data_type,$index_name,IndexType::KEYS);		
			$time_end = microtime(true);
			
			$vw_vars['success_message'] = displaySuccessMessage('create_secondary_index',array('column_name' => $column_name,'query_time' => getQueryTime($time_start,$time_end)));
		}
		catch (Exception $e) {
			$vw_vars['error_message'] = displayErrorMessage('create_secondary_index',array('column_name' => $column_name,'message' => $e->getMessage()));
		}
	}
	
	/*
		Create a secondary index
	*/
	
	if ($action == 'create_secondary_index') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
	
		$vw_vars['cluster_name'] = $sys_manager->describe_cluster_name();		
		$vw_vars['keyspace_name'] = $keyspace_name;
		$vw_vars['columnfamily_name'] = $columnfamily_name;
		
		$vw_vars['mode'] = 'create';
	
		$cf = ColumnFamilyHelper::getCFInKeyspace($keyspace_name,$columnfamily_name);
	
		if (!isset($vw_vars['success_message'])) $vw_vars['success_message'] = '';
		if (!isset($vw_vars['error_message'])) $vw_vars['error_message'] = '';
	
		$included_header = true;
		$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Create Secondary Index';
		
		echo getHTML('header.php');
		echo getHTML('create_edit_secondary_index.php',$vw_vars);
	}
	
		
	/*
		Submit form get key
	*/	
	if (isset($_POST['btn_get_key'])) {
		$i = 0;
		$tab_keys = array();
		while (isset($_POST['key_'.$i])) {
			$tab_keys[] = $_POST['key_'.$i];
			$i++;
		}
		
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		$cf = ColumnFamilyHelper::getCFInKeyspace($keyspace_name,$columnfamily_name);
	
		try {		
			$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
			$column_family = new ColumnFamily($pool, $columnfamily_name);
			
			$vw_vars['results'] = '';	
			
			$time_start = microtime(true);
			if (count($tab_keys) == 1) {
				$output = $column_family->get($tab_keys[0]);	
				$output = array($tab_keys[0] => $output);
			}
			else {
				$output = $column_family->multiget($tab_keys);
			}
			
			$time_end = microtime(true);
			
			$cf_def = ColumnFamilyHelper::getCFInKeyspace($keyspace_name,$columnfamily_name);
			
			$results = '';
			foreach ($output as $key => $value) {
				$vw_row_vars['key'] = $key;
				$vw_row_vars['value'] = $value;				
				
				$vw_row_vars['is_super_cf'] = $cf_def->column_type == 'Super';
				
				$vw_row_vars['keyspace_name'] = $keyspace_name;
				$vw_row_vars['columnfamily_name'] = $columnfamily_name;
				$vw_row_vars['show_actions_link'] = true;
				
				$vw_row_vars['is_counter_column'] = false;
				$results .= getHTML('columnfamily_browse_data_row.php',$vw_row_vars);
			}
			
			$vw_vars['results'] = $results;
			
			$vw_vars['success_message'] = displaySuccessMessage('get_key',array('keys' => implode(',',$tab_keys), 'query_time' => getQueryTime($time_start,$time_end)));
		}
		catch (cassandra_NotFoundException $e) {
			$vw_vars['success_message'] = displayInfoMessage('get_key_doesnt_exists',array('key' => $tab_keys[0]));
		}
		catch (Exception $e) {
			$vw_vars['error_message'] = displayErrorMessage('get_key',array('message' => $e->getMessage()));
		}
	}
	
	/*
		Submit query secondary index
	*/
	
	if (isset($_POST['btn_query_secondary_index'])) {
		$nb_rows = 10;
		if (isset($_POST['nb_rows'])) $nb_rows = $_POST['nb_rows'];
		
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		try {		
			$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
			$column_family = new ColumnFamily($pool, $columnfamily_name);
		
			$no_index_expression = 0;
			$arr_index_expression = array();
			
			while (isset($_POST['index_name_'.$no_index_expression]) && isset($_POST['operator_'.$no_index_expression]) && isset($_POST['column_value_'.$no_index_expression])) {
				$index_name = $_POST['index_name_'.$no_index_expression];
				$operator = $_POST['operator_'.$no_index_expression];
				$column_value = $_POST['column_value_'.$no_index_expression];
			
				switch ($operator) {
					case 'eq':
						$operator = cassandra_IndexOperator::EQ;
						break;
						
					case 'gte':
						$operator = cassandra_IndexOperator::GTE;
						break;
					
					case 'gt':
						$operator = cassandra_IndexOperator::GT;
						break;
						
					case 'lte':
						$operator = cassandra_IndexOperator::LTE;
						break;
						
					case 'lt':
						$operator = cassandra_IndexOperator::LT;
						break;
						
					default:
						// Invalid operator
						break;
				}
			
				$arr_index_expression[] = CassandraUtil::create_index_expression($index_name, $column_value, $operator);
				
				$no_index_expression++;
			}
			
			$index_clause = CassandraUtil::create_index_clause($arr_index_expression,'',$nb_rows);
			
			$time_start = microtime(true);
			$result = $column_family->get_indexed_slices($index_clause);
			$time_end = microtime(true);
			
			$vw_row_vars['is_super_cf'] = $column_family->cfdef->column_type == 'Super';     
			$vw_row_vars['is_counter_column'] = $column_family->cfdef->default_validation_class == 'org.apache.cassandra.db.marshal.CounterColumnType';
			
			$vw_vars['results_secondary_index'] = '';
			$vw_vars['results'] = '';
			
			$nb_results = 0;
			
			foreach ($result as $key => $value) {
				$vw_row_vars['key'] = $key;
				$vw_row_vars['value'] = $value;
				
				$vw_row_vars['keyspace_name'] = $keyspace_name;
				$vw_row_vars['columnfamily_name'] = $columnfamily_name;
				
				$vw_row_vars['show_actions_link'] = false;
				
				$vw_vars['results_secondary_index'] .= getHTML('columnfamily_browse_data_row.php',$vw_row_vars);
				
				$nb_results++;
			}		
			
			$vw_vars['error_message_secondary_index'] = displaySuccessMessage('query_secondary_index',array('nb_results' => $nb_results,'query_time' => getQueryTime($time_start,$time_end)));
		}
		catch (Exception $e) {
			$vw_vars['error_message_secondary_index'] = displayErrorMessage('query_secondary_index',array('message' => $e->getMessage()));
		}
	}
	
	/*
		Get a key
	*/
	
	if ($action == 'get_key') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
	
		$vw_vars['cluster_name'] = $sys_manager->describe_cluster_name();		
		$vw_vars['keyspace_name'] = $keyspace_name;
		$vw_vars['columnfamily_name'] = $columnfamily_name;
		
		$vw_vars['mode'] = 'create';
	
		$cf = ColumnFamilyHelper::getCFInKeyspace($keyspace_name,$columnfamily_name);
		
		if (!isset($vw_vars['results'])) $vw_vars['results'] = '';
		if (!isset($vw_vars['success_message'])) $vw_vars['success_message'] = '';
		if (!isset($vw_vars['error_message'])) $vw_vars['error_message'] = '';
		
		if (!isset($vw_vars['results_secondary_index'])) $vw_vars['results_secondary_index'] = '';
		if (!isset($vw_vars['success_message_secondary_index'])) $vw_vars['success_message_secondary_index'] = '';
		if (!isset($vw_vars['error_message_secondary_index'])) $vw_vars['error_message_secondary_index'] = '';
		
		$index_name = array();
		foreach ($cf->column_metadata as $one_si) {
			if ($one_si instanceof cassandra_ColumnDef) {
				$index_name[] = $one_si->name;
			}
		}
		
		$vw_vars['index_name'] = $index_name;
		
		$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Get Key';
		
		$included_header = true;
		echo getHTML('header.php');
		echo getHTML('columnfamily_getkey.php',$vw_vars);
	}
	
		
	/*
		Submit form insert/edit a row
	*/
	
	if (isset($_POST['btn_insert_row'])) {
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		$key = $_POST['key'];
		
		$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
		$column_family = new ColumnFamily($pool, $columnfamily_name);
		
		$no_column = 1;
		
		$data = array();
		
		$no_scf = 1;
				
		while (isset($_POST['column_key_'.$no_scf]) || $no_scf == 1) {
			$column_key_name = '';
			if (isset($_POST['column_key_'.$no_scf])) $column_key_name = $_POST['column_key_'.$no_scf];
						
			$no_column = 1;
			
			while (isset($_POST['column_name_'.$no_scf.'_'.$no_column]) && isset($_POST['column_value_'.$no_scf.'_'.$no_column])) {
				$column_name = $_POST['column_name_'.$no_scf.'_'.$no_column];
				$column_value = $_POST['column_value_'.$no_scf.'_'.$no_column];
				
				if (!empty($_POST['column_name_'.$no_scf.'_'.$no_column]) && !empty($_POST['column_value_'.$no_scf.'_'.$no_column])) {
					// CF
					if ($column_key_name == '') {
						$data[$column_name] = $column_value;
					}
					// SCF
					else {
						$data[$column_key_name][$column_name] = $column_value;
					}
				}
				
				$no_column++;
			}
			
			$no_scf++;
		}
		
		try {
			if (!empty($key)) {				
				if (count($data) > 0) {			
				
					if (isset($_POST['mode']) && $_POST['mode'] == 'edit') {
						$column_family->remove($key);
					}
					
					$time_start = microtime(true);
					$column_family->insert($key,$data);
					$time_end = microtime(true);
					
					// Insert successful
					if (isset($_POST['mode']) && $_POST['mode'] == 'insert') {
						$vw_vars['success_message'] = displaySuccessMessage('insert_row',array('query_time' => getQueryTime($time_start,$time_end)));
					}
					// Edit successful
					else {
						$vw_vars['success_message'] = displaySuccessMessage('edit_row',array('key' => $key,'query_time' => getQueryTime($time_start,$time_end)));
					}
				}
				// Some fields are not filled
				else {
					$vw_vars['error_message'] = displayErrorMessage('insert_row_incomplete_fields');
				}
			}
			// A key must be specified
			else {
				$vw_vars['info_message'] = displayInfoMessage('insert_row_not_empty');
			}
		}
		catch (Exception $e) {
			$vw_vars['error_message'] = displayErrormessage('insert_row',array('message' => $e->getMessage()));
		}
	}
	
	/*
		Insert a row
	*/
	
	if ($action == 'insert_row') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		$vw_vars['cluster_name'] = $sys_manager->describe_cluster_name();
		$vw_vars['keyspace_name'] = $keyspace_name;
		$vw_vars['columnfamily_name'] = $columnfamily_name;
		
		if (!isset($vw_vars['success_message'])) $vw_vars['success_message'] = '';
		if (!isset($vw_vars['info_message'])) $vw_vars['info_message'] = '';
		if (!isset($vw_vars['error_message'])) $vw_vars['error_message'] = '';
		
		$cf_def = ColumnFamilyHelper::getCFInKeyspace($keyspace_name,$columnfamily_name);
		$vw_vars['is_super_cf'] = $cf_def->column_type == 'Super';
		
		$vw_vars['key'] = '';
		$vw_vars['mode'] = 'insert';
		$vw_vars['super_key'] = '';
		
		$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Insert a Row';
		
		$included_header = true;
		echo getHTML('header.php');
		echo getHTML('columnfamily_insert_edit_row.php',$vw_vars);
	}

        /*
		Merge CF with JSON data
	*/
        
	if ($action == 'merge_cf_with_json_data') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		$vw_vars['cluster_name'] = $sys_manager->describe_cluster_name();
		$vw_vars['keyspace_name'] = $keyspace_name;
		$vw_vars['columnfamily_name'] = $columnfamily_name;
		
		if (!isset($vw_vars['success_message'])) $vw_vars['success_message'] = '';
		if (!isset($vw_vars['info_message'])) $vw_vars['info_message'] = '';
		if (!isset($vw_vars['error_message'])) $vw_vars['error_message'] = '';
		
		$cf_def = ColumnFamilyHelper::getCFInKeyspace($keyspace_name,$columnfamily_name);
		$vw_vars['is_super_cf'] = $cf_def->column_type == 'Super';
		
		$vw_vars['key'] = '';
		$vw_vars['mode'] = 'insert';
		$vw_vars['super_key'] = '';
		
		$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Insert a Row';
		
		$included_header = true;
		echo getHTML('header.php');
		echo getHTML('columnfamily_merge_with_json.php',$vw_vars);
	}

        /*
		Submit form of merge cf with JSON data
	*/
	
	if (isset($_POST['btn_merge_cf_with_json_data'])) {
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
                $jsondata = $_POST['jsondata'];
                $json_out = json_decode($jsondata,true);
                
                //echo "<pre>"; print_r($json_out); echo "</pre>";
                echo "<pre>";
                
                
		$key = $_POST['key'];
		
		$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
		$column_family = new ColumnFamily($pool, $columnfamily_name);
		
		$no_column = 1;
		
		$data = array();
		
                
                
		$is_super_cf = $_POST['is_super_cf'];
                
                try {
                $time_start = microtime(true);
                if ($is_super_cf!="")  {    //supercolumn family data merge , should check if required data level is there
                    
		
                    foreach ($json_out as $rowkey=>$rowdata) {
                        echo "Adding SuperColumnFamily row $rowkey\n";
                        print_r($rowdata);
                        $column_family->insert($rowkey,$rowdata);
                    }  
                    
                }                    
                else {    //no supercolumn for this cf family
                    foreach ($json_out as $rowkey=>$rowdata) {
                        echo "Adding row $rowkey\n";
                        print_r($rowdata);
                        $column_family->insert($rowkey,$rowdata);
                    }
                }
                echo "</pre>";
                $time_end = microtime(true);
                $vw_vars['success_message'] = displaySuccessMessage('insert_row',array('query_time' => getQueryTime($time_start,$time_end)));
                }
                catch (Exception $e) {
			$vw_vars['error_message'] = displayErrormessage('insert_row',array('message' => $e->getMessage()));
		}
                                
	}

	
	/*
		Truncate column family
	*/
	
	if ($action == 'truncate') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		try {
			$sys_manager->truncate_column_family($keyspace_name, $columnfamily_name);
			
			redirect('describe_columnfamily.php?keyspace_name='.$keyspace_name.'&columnfamily_name='.$columnfamily_name);
		}
		catch(Exception $e) {
			echo displayErrorMessage('something_wrong_happened',array('message' => $e->getMessage()));
		}
	}
	
	/*
		Browse data - Using get_range()
	*/
	
	if ($action == 'browse_data') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		$vw_vars['cluster_name'] = $sys_manager->describe_cluster_name();
		$vw_vars['keyspace_name'] = $keyspace_name;
		$vw_vars['columnfamily_name'] = $columnfamily_name;
				
		try {		
			$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
			$column_family = new ColumnFamily($pool, $columnfamily_name);
		
			// Increment counter
			if (isset($_GET['increment'])) {
				$row_key = $_GET['row_key'];
				$column = $_GET['column'];
				
				$super_column = null;
				if (isset($_GET['super_column'])) $super_column = $_GET['super_column'];
				
				$column_family->add($row_key, $column, 1,$super_column);
			}
			
			// Decrement counter
			if (isset($_GET['decrement'])) {
				$row_key = $_GET['row_key'];
				$column = $_GET['column'];
				
				$super_column = null;
				if (isset($_GET['super_column'])) $super_column = $_GET['super_column'];
				
				$column_family->add($row_key, $column, -1,$super_column);
			}
		
			$offset_key = '';
					
			if (isset($_GET['pos']) && $_GET['pos'] == 'prev' && isset($_SESSION['browse_data_offset_key']) && is_array($_SESSION['browse_data_offset_key'])) {
				if (count($_SESSION['browse_data_offset_key']) > 1) {
					array_pop($_SESSION['browse_data_offset_key']);
				}
				
				$offset_key = array_pop($_SESSION['browse_data_offset_key']);
			}			
			elseif (isset($_GET['offset_key'])) {
				$offset_key = $_GET['offset_key'];
			}
			
			$vw_vars['current_offset_key'] = $offset_key;
		
			if ($offset_key == '') {
				$_SESSION['browse_data_offset_key'] = array();
				$_SESSION['browse_data_offset_key'][] = '';
			}
			else {
				if (!isset($_SESSION['browse_data_offset_key']) || !is_array($_SESSION['browse_data_offset_key'])) {
					$_SESSION['browse_data_offset_key'] = array();
				}
				
				$pos = '';
				if (isset($_GET['pos'])) $pos = $_GET['pos'];
				
				// Make sure it's not only a refresh of the page AND a previous click
				if (end($_SESSION['browse_data_offset_key']) != $offset_key && $pos != 'prev') {
					$_SESSION['browse_data_offset_key'][] = $offset_key;
					
					// Don't keep more then 100 previous key
					if (count($_SESSION['browse_data_offset_key']) > 100) {
						array_shift($_SESSION['browse_data_offset_key']);
					}
				}
			}
				
			$nb_rows = CCA_BROWSE_DATA_ROWS ;
			if (isset($_GET['nb_rows']) && is_numeric($_GET['nb_rows']) && $_GET['nb_rows'] > 0) $nb_rows = $_GET['nb_rows'];
			$vw_vars['nb_rows'] = $nb_rows;
		
			$describe_keyspace = $sys_manager->describe_keyspace($keyspace_name);
		
			$cf_def = null;
			foreach ($describe_keyspace->cf_defs as $cfdef) {
				if ($cfdef->name == $columnfamily_name) {
					$cf_def = $cfdef;
					break;
				}
			}

			$vw_row_vars['is_super_cf'] = $cf_def->column_type == 'Super';     
			$vw_row_vars['is_counter_column'] = $column_family->cfdef->default_validation_class == 'org.apache.cassandra.db.marshal.CounterColumnType';
		
			$result = $column_family->get_range($offset_key,'',$nb_rows);		
			
			$vw_vars['results'] = '';	
			$nb_results = 0;
			
			foreach ($result as $key => $value) {
				$vw_row_vars['key'] = $key;
				$vw_row_vars['value'] = $value;
				
				$vw_row_vars['keyspace_name'] = $keyspace_name;
				$vw_row_vars['columnfamily_name'] = $columnfamily_name;
				
				$vw_row_vars['show_actions_link'] = true;
				
				$vw_vars['results'] .= getHTML('columnfamily_browse_data_row.php',$vw_row_vars);
				
				$nb_results++;
			}		
			
			$vw_vars['show_begin_page_link'] = $offset_key != '';
			$vw_vars['show_prev_page_link'] = $offset_key != '' && count($_SESSION['browse_data_offset_key']) > 0;
			
			// We got the number of rows we asked for, display "Next Page" link
			if ($nb_results == $nb_rows) {				
				$offset_key = ++$key;				
				
				$vw_vars['offset_key'] = $offset_key;
				$vw_vars['show_next_page_link'] = true;
			}
			else {
				$vw_vars['offset_key'] = '';
				$vw_vars['show_next_page_link'] = false;
			}			
			
			$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Browse Data';
			
			$vw_vars['is_counter_column'] = $vw_row_vars['is_counter_column'];
			
			$included_header = true;
			echo getHTML('header.php');
			echo getHTML('columnfamily_browse_data.php',$vw_vars);
		}
		catch (cassandra_NotFoundException $e) {
			echo displayErrorMessage('columnfamily_doesnt_exists',array('column_name' => $columnfamily_name));
		}
		catch (Exception $e) {
			echo displayErrorMessage('something_wrong_happened',array('message' => $e->getMessage()));
		}
	}	
	
	/*
		Edit a row
	*/
	if ($action == 'edit_row') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		$key = '';
		if (isset($_GET['key'])) {
			$key = $_GET['key'];
		}
		
		$super_key = '';
		if (isset($_GET['super_key'])) {
			$super_key = $_GET['super_key'];
		}
		
		$vw_vars['cluster_name'] = $sys_manager->describe_cluster_name();
		$vw_vars['keyspace_name'] = $keyspace_name;
		$vw_vars['columnfamily_name'] = $columnfamily_name;
		
		if (!isset($vw_vars['success_message'])) $vw_vars['success_message'] = '';
		if (!isset($vw_vars['info_message'])) $vw_vars['info_message'] = '';
		if (!isset($vw_vars['error_message'])) $vw_vars['error_message'] = '';
		
		$cf_def = ColumnFamilyHelper::getCFInKeyspace($keyspace_name,$columnfamily_name);
		$vw_vars['is_super_cf'] = $cf_def->column_type == 'Super';
		
		$vw_vars['key'] = $key;
		$vw_vars['super_key'] = $super_key;
		
		$vw_vars['mode'] = 'edit';
		
		try {		
			$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
			$column_family = new ColumnFamily($pool, $columnfamily_name);
			
			$vw_vars['results'] = '';	
			$vw_vars['output'] = '';		
					
			$output = $column_family->get($key);
			$vw_vars['output'] = $output;
		}
		catch(Exception $e) {
			echo displayErrorMessage('something_wrong_happened',array('message' => $e->getMessage()));
		}
		
		$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Edit Row';
		
		$included_header = true;
		echo getHTML('header.php');
		echo getHTML('columnfamily_insert_edit_row.php',$vw_vars);
	}
	
	/*
		Delete a row
	*/
	if ($action == 'delete_row') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		$key = '';
		if (isset($_GET['key'])) {
			$key = $_GET['key'];
		}
		
		$super_column_key = null;
		if (isset($_GET['super_column_key'])) {
			$super_column_key = $_GET['super_column_key'];
		}
		
		try {
			$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
			$column_family = new ColumnFamily($pool, $columnfamily_name);	
		
			$column_family->remove($key,null,$super_column_key);
			
			redirect('columnfamily_action.php?action=browse_data&keyspace_name='.$keyspace_name.'&columnfamily_name='.$columnfamily_name);
		}
		catch (cassandra_NotFoundException $e) {
			$included_header = true;
			echo getHTML('header.php');
			echo displayErrorMessage('columnfamily_doesnt_exists',array('column_name' => $columnfamily_name));
		}
		catch (Exception $e) {
			$included_header = true;
			echo getHTML('header.php');
			echo displayErrorMessage('something_wrong_happened',array('message' => $e->getMessage()));
		}
	}
	/*
		Copy row as json
	*/
	if ($action == 'copy_row_as_json') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		$key = '';
		if (isset($_GET['key'])) {
			$key = $_GET['key'];
		}
		
		$super_key = '';
		if (isset($_GET['super_key'])) {
			$super_key = $_GET['super_key'];
		}
		
		$vw_vars['cluster_name'] = $sys_manager->describe_cluster_name();
		$vw_vars['keyspace_name'] = $keyspace_name;
		$vw_vars['columnfamily_name'] = $columnfamily_name;
		
		if (!isset($vw_vars['success_message'])) $vw_vars['success_message'] = '';
		if (!isset($vw_vars['info_message'])) $vw_vars['info_message'] = '';
		if (!isset($vw_vars['error_message'])) $vw_vars['error_message'] = '';
		
		$cf_def = ColumnFamilyHelper::getCFInKeyspace($keyspace_name,$columnfamily_name);
		$vw_vars['is_super_cf'] = $cf_def->column_type == 'Super';
		
		$vw_vars['key'] = $key;
		$vw_vars['super_key'] = $super_key;
		
		$vw_vars['mode'] = 'edit';
		
		try {		
			$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
			$column_family = new ColumnFamily($pool, $columnfamily_name);
			
			$vw_vars['results'] = '';	
			$vw_vars['output'] = '';		
					
			$output = $column_family->get($key);
			$vw_vars['output'] = $output;
		}
		catch(Exception $e) {
			echo displayErrorMessage('something_wrong_happened',array('message' => $e->getMessage()));
		}
		
		$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Edit Row';
		
		$included_header = true;
		echo "<pre>";
		//print_r($vw_vars);
		$json_out = array();
		$json_out[$vw_vars['key']] = $vw_vars['output'];
		//print_r($json_out);
		$json = json_encode($json_out);
		echo $json;
		echo "</pre>";
//		echo getHTML('header.php');
//		echo getHTML('columnfamily_insert_edit_row.php',$vw_vars);
	}
        
        /*
		Dump CF as JSON data 
               * rough version, using browse_data code with 'higher' row limit  
	*/

	if ($action == 'dump_cf_as_json') {
		$is_valid_action = true;
	
		$keyspace_name = '';
		if (isset($_GET['keyspace_name'])) {
			$keyspace_name = $_GET['keyspace_name'];
		}
		
		$columnfamily_name = '';
		if (isset($_GET['columnfamily_name'])) {
			$columnfamily_name = $_GET['columnfamily_name'];
		}
		
		$vw_vars['cluster_name'] = $sys_manager->describe_cluster_name();
		$vw_vars['keyspace_name'] = $keyspace_name;
		$vw_vars['columnfamily_name'] = $columnfamily_name;
				
		try {		
			$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
			$column_family = new ColumnFamily($pool, $columnfamily_name);
		
			// Increment counter
			if (isset($_GET['increment'])) {
				$row_key = $_GET['row_key'];
				$column = $_GET['column'];
				
				$super_column = null;
				if (isset($_GET['super_column'])) $super_column = $_GET['super_column'];
				
				$column_family->add($row_key, $column, 1,$super_column);
			}
			
			// Decrement counter
			if (isset($_GET['decrement'])) {
				$row_key = $_GET['row_key'];
				$column = $_GET['column'];
				
				$super_column = null;
				if (isset($_GET['super_column'])) $super_column = $_GET['super_column'];
				
				$column_family->add($row_key, $column, -1,$super_column);
			}
		
			$offset_key = '';
					
			if (isset($_GET['pos']) && $_GET['pos'] == 'prev' && isset($_SESSION['browse_data_offset_key']) && is_array($_SESSION['browse_data_offset_key'])) {
				if (count($_SESSION['browse_data_offset_key']) > 1) {
					array_pop($_SESSION['browse_data_offset_key']);
				}
				
				$offset_key = array_pop($_SESSION['browse_data_offset_key']);
			}			
			elseif (isset($_GET['offset_key'])) {
				$offset_key = $_GET['offset_key'];
			}
			
			$vw_vars['current_offset_key'] = $offset_key;
		
			if ($offset_key == '') {
				$_SESSION['browse_data_offset_key'] = array();
				$_SESSION['browse_data_offset_key'][] = '';
			}
			else {
				if (!isset($_SESSION['browse_data_offset_key']) || !is_array($_SESSION['browse_data_offset_key'])) {
					$_SESSION['browse_data_offset_key'] = array();
				}
				
				$pos = '';
				if (isset($_GET['pos'])) $pos = $_GET['pos'];
				
				// Make sure it's not only a refresh of the page AND a previous click
				if (end($_SESSION['browse_data_offset_key']) != $offset_key && $pos != 'prev') {
					$_SESSION['browse_data_offset_key'][] = $offset_key;
					
					// Don't keep more then 100 previous key
					//if (count($_SESSION['browse_data_offset_key']) > 100) {
					//	array_shift($_SESSION['browse_data_offset_key']);
					//}
				}
			}
				
			$nb_rows = 10000000 ; //row limit for dump - larger data will be clipped at the moment
			if (isset($_GET['nb_rows']) && is_numeric($_GET['nb_rows']) && $_GET['nb_rows'] > 0) $nb_rows = $_GET['nb_rows'];
			$vw_vars['nb_rows'] = $nb_rows;
		
			$describe_keyspace = $sys_manager->describe_keyspace($keyspace_name);
		
			$cf_def = null;
			foreach ($describe_keyspace->cf_defs as $cfdef) {
				if ($cfdef->name == $columnfamily_name) {
					$cf_def = $cfdef;
					break;
				}
			}

			$vw_row_vars['is_super_cf'] = $cf_def->column_type == 'Super';     
			$vw_row_vars['is_counter_column'] = $column_family->cfdef->default_validation_class == 'org.apache.cassandra.db.marshal.CounterColumnType';
		
			//$result = $column_family->get_range($offset_key,'',$nb_rows);		
			$result = $column_family->get_range($offset_key,'',$nb_rows);		
			
			$vw_vars['results'] = '';	
			$nb_results = 0;
			        $vw_vars['results']=array();
			foreach ($result as $key => $value) {
				$vw_row_vars['key'] = $key;
				$vw_row_vars['value'] = $value;
				
				$vw_row_vars['keyspace_name'] = $keyspace_name;
				$vw_row_vars['columnfamily_name'] = $columnfamily_name;
				
				$vw_row_vars['show_actions_link'] = true;
				
				//$vw_vars['results'] .= getHTML('columnfamily_browse_data_row.php',$vw_row_vars);
				
				$vw_vars['results'][$vw_row_vars['key']]=$vw_row_vars['value'];  
				
				$nb_results++;
			}		
			
			$vw_vars['show_begin_page_link'] = $offset_key != '';
			$vw_vars['show_prev_page_link'] = $offset_key != '' && count($_SESSION['browse_data_offset_key']) > 0;
			
			// We got the number of rows we asked for, display "Next Page" link
			if ($nb_results == $nb_rows) {				
				$offset_key = ++$key;				
				
				$vw_vars['offset_key'] = $offset_key;
				$vw_vars['show_next_page_link'] = true;
			}
			else {
				$vw_vars['offset_key'] = '';
				$vw_vars['show_next_page_link'] = false;
			}			
			
			$current_page_title = 'Cassandra Cluster Admin > '.$keyspace_name.' > '.$columnfamily_name.' > Browse Data';
			
			$vw_vars['is_counter_column'] = $vw_row_vars['is_counter_column'];
			
			$included_header = true;
			echo getHTML('header.php');
			//echo "<pre>";
			//print_r($vw_vars['results']);
                        $json_out = json_encode($vw_vars['results']);
                        echo '<textarea rows="25" cols="150">';
			echo $json_out;
                        echo "</textarea>";
			
			//echo "</pre>";
			//echo getHTML('columnfamily_browse_data.php',$vw_vars);
		}
		catch (cassandra_NotFoundException $e) {
			echo displayErrorMessage('columnfamily_doesnt_exists',array('column_name' => $columnfamily_name));
		}
		catch (Exception $e) {
			echo displayErrorMessage('something_wrong_happened',array('message' => $e->getMessage()));
		}
	}	

	
	/*
		Modify counter
	*/
	if (isset($_POST['btn_modify_counter'])) {
		$is_valid_action = true;
		
		$key = '';
		if (isset($_POST['key'])) $key = $_POST['key'];
		
		$super_column = null;
		if (isset($_POST['super_column'])) $super_column = $_POST['super_column'];
		
		$column = '';
		if (isset($_POST['column'])) $column = $_POST['column'];
		
		$action = '';
		if (isset($_POST['action'])) $action = $_POST['action'];
		
		$value = '';
		if (isset($_POST['value'])) $value = $_POST['value'];

		$keyspace_name = '';
		if (isset($_POST['keyspace_name'])) $keyspace_name = $_POST['keyspace_name'];
		
		$columnfamily_name = '';
		if (isset($_POST['columnfamily_name'])) $columnfamily_name = $_POST['columnfamily_name'];
		
		try {
			$pool = new ConnectionPool($keyspace_name, $cluster_helper->getArrayOfNodesForCurrentCluster(),null,5,5000,5000,10000,$cluster_helper->getCredentialsForCurrentCluster());
			$column_family = new ColumnFamily($pool, $columnfamily_name);	
			
			if ($action == 'dec') {
				$value *= -1;
			}
			
			$column_family->add($key, $column, $value,$super_column);
			
			$new_value = $column_family->get($key);
			
			if ($column_family->cfdef->column_type == 'Super') {
				$new_value = $new_value[$super_column][$column];
			}
			else {				
				$new_value = $new_value[$column];
			}			
			
			redirect('counters.php?keyspace_name='.$keyspace_name.'&columnfamily_name='.$columnfamily_name.'&new_value='.$new_value);
        }
		catch (Exception $e) {
			$_SESSION['message'] = $e->getMessage();
			redirect('counters.php?keyspace_name='.$keyspace_name.'&columnfamily_name='.$columnfamily_name.'&error=1');
		}		
	}

	if (!$included_header) {	
		echo getHTML('header.php');
		
		if (!$is_valid_action) {
			// No action specified
			if (empty($action)) {
				echo displayErrorMessage('no_action_specified');
			}
			// Invalid action specified
			else {
				echo displayErrorMessage('invalid_action_specified',array('action' => $action));
			}
		}
	}
	
	echo getHTML('footer.php');
?>