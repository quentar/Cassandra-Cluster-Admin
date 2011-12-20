<h3><a href="index.php"><?php echo $cluster_name; ?></a> &gt; <a href="describe_keyspace.php?keyspace_name=<?php echo $keyspace_name; ?>"><?php echo $keyspace_name; ?></a> &gt; <?php echo $columnfamily_name; ?></h3>

<div id="menu">
	<div class="menu_item" onclick="location.href='columnfamily_action.php?action=browse_data&amp;keyspace_name=<?php echo $keyspace_name; ?>&amp;columnfamily_name=<?php echo $columnfamily_name; ?>'">
		<div class="icon browse_data"></div> Browse Data
	</div>
	
	<?php if (!$is_read_only_keyspace): ?>
		<div class="menu_item" onclick="location.href='columnfamily_action.php?action=create_secondary_index&amp;keyspace_name=<?php echo $keyspace_name; ?>&amp;columnfamily_name=<?php echo $columnfamily_name; ?>'">
			<div class="icon create_secondary_index"></div> Create Secondary Index
		</div>
	<?php endif; ?>
	
	<div class="menu_item" onclick="location.href='columnfamily_action.php?action=get_key&amp;keyspace_name=<?php echo $keyspace_name; ?>&amp;columnfamily_name=<?php echo $columnfamily_name; ?>'">
		<div class="icon get_key"></div> Get Key
	</div>
	
	<?php if (!$is_read_only_keyspace && $is_counter_column): ?>
	<div class="menu_item" onclick="location.href='counters.php?keyspace_name=<?php echo $keyspace_name; ?>&amp;columnfamily_name=<?php echo $columnfamily_name; ?>'">
		<div class="icon counters"></div> Counters
	</div>
	<?php endif; ?>
	
	<?php if (!$is_read_only_keyspace && !$is_counter_column): ?>
	<div class="menu_item" onclick="location.href='columnfamily_action.php?action=insert_row&amp;keyspace_name=<?php echo $keyspace_name; ?>&amp;columnfamily_name=<?php echo $columnfamily_name; ?>'">
		<div class="icon insert_row"></div> Insert Row
	</div>
	<?php endif; ?>
	
	<?php if (!$is_read_only_keyspace): ?>
		<div class="menu_item" onclick="location.href='columnfamily_action.php?action=edit&amp;keyspace_name=<?php echo $keyspace_name; ?>&amp;columnfamily_name=<?php echo $columnfamily_name; ?>'">
			<div class="icon edit_column_family"></div> Edit Column Family
		</div>	
	
		<div class="menu_item" onclick="return truncateColumnFamily('<?php echo $keyspace_name; ?>','<?php echo $columnfamily_name; ?>');">
			<div class="icon truncate_column_family"></div> Truncate Column Family
		</div>
		
		<div class="menu_item" onclick="return dropColumnFamily('<?php echo $keyspace_name; ?>','<?php echo $columnfamily_name; ?>');">
			<div class="icon drop_column_family"></div> Drop Column Family
		</div>
		
		<div class="menu_item" onclick="return dumpColumnFamilyAsJSON('<?php echo $keyspace_name; ?>','<?php echo $columnfamily_name; ?>');">
			<div class="icon dump_column_family"></div> Dump as JSON
		</div>

		<div class="menu_item" onclick="return mergeColumnFamilyWithJSON('<?php echo $keyspace_name; ?>','<?php echo $columnfamily_name; ?>');">
			<div class="icon mergedata_column_family"></div> Merge with JSON data
		</div>

	<?php endif; ?>
	
	<div class="clear_left"></div>
</div>

<h3>Column Family Details</h3>
<?php echo $columnfamily_def; ?>

<?php if (count($secondary_indexes) > 0): ?>
	<h3>Secondary Indexes</h3>
		
	<?php 
		foreach ($secondary_indexes as $one_si): 
			echo getHTML('describe_secondary_index.php',array('one_si' => $one_si,
														  'keyspace_name' => $keyspace_name,
														  'columnfamily_name' => $columnfamily_name));
		endforeach;
	?>
<?php endif; ?>