<div class="wpcl_rule_fieldset">
	<div class="wpcl_query_field fill">
		<select name="<?php echo "{$field['name']}[search_parameters][{$index}][type]" ?>" class="widefat type">
		<?php
			foreach( $type_choices as $type_value => $type_name ) {
				printf( '<option value="%s"%s>%s</option>', $type_value, selected( $param['type'], $type_value, false ), $type_name );
			}
		?>
		</select>
	</div>
	<div class="wpcl_query_field">
		<?php $disabled = $param['type'] === 'post_type' ? 'disabled' : '' ?>
		<select name="<?php echo "{$field['name']}[search_parameters][{$index}][operator]" ?>" class="widefat operator" <?php echo $disabled; ?>>
			<option value="IN"<?php selected( $param['operator'], 'IN', true ); ?>>IN</option>
			<option value="NOT IN"<?php selected( $param['operator'], 'NOT IN', true ); ?>>NOT IN</option>
		</select>
	</div>
	<div class="wpcl_query_field">
		<select name="<?php echo "{$field['name']}[search_parameters][{$index}][selection]" ?>" class="widefat selection">
		<?php
			foreach( $query_options[$param['type']]['choices'] as $option_value => $option_name ) {
				printf( '<option value="%s"%s>%s</option>', $option_value, selected( $param['selection'], $option_value, false ), $option_name );
			}
		?>
		</select>
	</div>
	<div class="wpcl_query_field">
		<button class="button button-default wpcl-button pull-right" data-action="remove">-</button>
	</div>
</div>