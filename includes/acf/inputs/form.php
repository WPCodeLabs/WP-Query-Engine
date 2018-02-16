<!-- Do Posts Per Page Field -->
<div class="wpcl_query_field">
	<label for="<?php echo "{$field['name']}[posts_per_page]" ?>">Posts Per Page</label>
	<input type="number" name="<?php echo "{$field['name']}[posts_per_page]" ?>" value="<?php echo $values['posts_per_page']; ?>">
	<p class="description">Use <strong>-1</strong> to return <em>all</em> posts matching the search criteria</p>
</div>
<!-- Do Pagination Field -->
<div class="wpcl_query_field">
	<label for="<?php echo "{$field['name']}[pagination]" ?>"><input type="checkbox" name="<?php echo "{$field['name']}[pagination]" ?>" value="true" <?php checked( $values['pagination'], 'true', true ); ?>> Use Pagination?</label>
	<p class="description"><strong>Note:</strong> Pagination may cause unpredictable behavior in some cases</p>
</div>
<!-- Sticky Posts -->
<div class="wpcl_query_field">
	<label for="<?php echo "{$field['name']}[ignore_sticky_posts]" ?>"><input type="checkbox" name="<?php echo "{$field['name']}[ignore_sticky_posts]" ?>" value="true" <?php checked( $values['ignore_sticky_posts'], 'true', true ); ?>> Ignore Sticky Posts?</label>
	<p class="description">If true, query will not use default sticky post behavior</p>
</div>
<!-- Do orderby field -->
<div class="wpcl_query_field">
	<label for="<?php echo "{$field['name']}[orderby]" ?>">Order By</label>
	<select name="<?php echo "{$field['name']}[orderby]" ?>">
	<?php
		$options = array( 'date' => 'Date', 'type' => 'Type', 'name' => 'Name', 'title' => 'Title', 'author' => 'Author', 'ID' => 'ID', 'modified' => 'Modified', 'parent' => 'Parent', 'comment_count' => 'Comment Count', 'menu_order' => 'Menu Order', 'rand' => 'Random', 'none' => 'None' );
		foreach( $options as $option_value => $option_name ) {
			printf( '<option value="%s"%s>%s</option>', $option_value, selected( $values['orderby'], $option_value, false ), $option_name );
		}
	?>
	</select>
	<p class="description">The Attribute to order the post by</p>
</div>
<!-- Do Order Field -->
<div class="wpcl_query_field">
	<label for="<?php echo "{$field['name']}[order]" ?>">Order</label>
	<select name="<?php echo "{$field['name']}[order]" ?>">
	<?php
		foreach( array( 'ASC' => 'ASC', 'DESC' => 'DESC' ) as $option_value => $option_name ) {
			printf( '<option value="%s"%s>%s</option>', $option_value, selected( $values['order'], $option_value, false ), $option_name );
		}
	?>
	</select>
	<p class="description">Either Ascending or Descending order</p>
</div>
<!-- Do template field -->
<div class="wpcl_query_field">
	<label for="<?php echo "{$field['name']}[template]" ?>">Template</label>
	<select name="<?php echo "{$field['name']}[template]" ?>">
	<?php
		$templates = \WPCL\QueryEngine\Output::get_template_names();
		foreach( $templates as $template ) {
			printf( '<option value="%1$s"%2$s>%1$s</option>', $template, selected( $values['template'], $template, false ) );
		}
	?>
	</select>
	<p class="description">Which output template to use</p>
</div>