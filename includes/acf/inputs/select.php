
<select name="<?php echo $field_name; ?>" class="wpcl-select">
	<?php foreach( $choices as $choice_value => $choice_name ) : ?>
		<?php printf( '<option value="%s"%s>%s</option>', $choice_name, selected( $field_value, $choice_name, false ), $choice_name ); ?>
	<?php endforeach; ?>
</select>