<h3><a href="index.php"><?php echo $cluster_name; ?></a> &gt; 
<a href="describe_keyspace.php?keyspace_name=<?php echo $keyspace_name; ?>"><?php echo $keyspace_name; ?></a> &gt; <a href="describe_columnfamily.php?keyspace_name=<?php echo $keyspace_name; ?>&amp;columnfamily_name=<?php echo $columnfamily_name; ?>"><?php echo $columnfamily_name; ?></a> &gt; 
Merge column family with JSON data
</h3>

<script type="text/javascript">
	var num_columns = 0;
	var num_super_columns = 0;


	$(document).ready(function() {		
		<?php if ($mode == 'insert'): ?>
			<?php if ($is_super_cf): ?>
			<?php else: ?>
				num_super_columns++;
			<?php endif; ?>
			
		<?php endif; ?>
		<?php 
			if ($mode == 'edit'):
				if ($is_super_cf):
					foreach ($output as $super_key => $data):
						$super_key = str_replace('\'','\\\'',$super_key);
						echo 'addSuperColumn(\''.$super_key.'\');';
						
						foreach ($data as $name => $value):
							$name = str_replace('\'','\\\'',$name);
							$value = str_replace('\'','\\\'',$value);
							echo 'addColumn(\''.$name.'\',\''.$value.'\',num_super_columns);';
						endforeach;
					endforeach;
				else:
					echo 'num_super_columns++;';
					
					foreach ($output as $name => $value):
						$name = str_replace('\'','\\\'',$name);
						$value = str_replace('\'','\\\'',$value);
						echo 'addColumn(\''.$name.'\',\''.$value.'\',num_super_columns);';
					endforeach;
				endif;
			endif;
		?>
	});
</script>

<?php echo $success_message; ?>
<?php echo $info_message; ?>
<?php echo $error_message; ?>

<form method="post" action="">
	<?php if ($is_super_cf): ?>
	<div style="width: 590px;">
		<div class="clear_right"></div>
	</div>
	<?php endif; ?>
	
	<div>
		<label for="key">JSON input data : </label>
		<textarea id="jsondata" name="jsondata" rows="11" cols="60" ></textarea>
	</div>
	
	<div id="data"></div>
	
	<div>
<input type="submit" name="btn_merge_cf_with_json_data" value="Merge with JSON data" />
	</div>
	
	<?php if ($mode == 'edit'): ?><input type="hidden" name="key" value="<?php echo $key; ?>" /><?php endif;?>
	<input type="hidden" name="keyspace_name" value="<?php echo $keyspace_name; ?>" />
	<input type="hidden" name="columnfamily_name" value="<?php echo $columnfamily_name; ?>" />
	<input type="hidden" name="is_super_cf" value="<?php echo $is_super_cf; ?>" />
	<input type="hidden" name="mode" value="<?php echo $mode; ?>" />
</form>