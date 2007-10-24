<?php
/*
Plugin Name: Category Posts Widget
Plugin URI: http://jameslao.com/
Description: Adds a widget that can display a specified number of posts from a single category. Can also set how many widgets to show.
Author: James Lao	
Version: 1.0
Author URI: http://jameslao.com/
*/

// The widget itself.
function nk_cat_posts_widget($args, $number = 1) {
	extract($args);
	$options = get_option('widget_cat_posts');
	$title = empty($options[$number]['title']) ? 'Category' : $options[$number]['title'];
	$cat_id = empty($options[$number]['cat']) ? 1 : $options[$number]['cat'];
	$num = $options[$number]['num'] > 15 ? 15 : $options[$number]['num'];
	
	echo $before_widget;
	echo $before_title . $title . $after_title;
	echo '<ul>';
	nk_cat_posts($cat_id, $num);
	echo '</ul>';
	echo $after_widget;
}

// The control dialog.
function nk_cat_posts_widget_control($number) {
	$options = $newoptions = get_option('widget_cat_posts');
	if ( $_POST["cat-posts-title-" . $number] ) {
		$newoptions[$number]['title'] = strip_tags(stripslashes($_POST["cat-posts-title-" . $number]));
		$newoptions[$number]['cat'] = $_POST["show-cat-id-" . $number];
		$newoptions[$number]['num'] = is_numeric($_POST["cat-posts-num-" . $number]) && $_POST["cat-posts-num-" . $number]!=0 ? $_POST["cat-posts-num-" . $number] : 5;
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_cat_posts', $options);
	}
	
	echo '<p><label for="cat-posts-title-' . $number . '">Title: <input style="width:200px;" id="cat-posts-title-' . $number . '" name="cat-posts-title-' . $number . '" type="text" value="' . $options[$number]['title'] . '" /></label></p>';
	echo '<p><label>Show posts in ';
	wp_dropdown_categories(array('name'=>'show-cat-id-' . $number, 'selected'=>$options[$number]['cat']));
	echo '</label></p>';
	echo '<p><label for="cat-posts-num-' . $number . '">Number of posts to show: <input size="3" id="cat-posts-num-' . $number . '" name="cat-posts-num-' . $number . '" type="text" value="' . $options[$number]['num'] . '" /></label> (max 15)</p>';
}

// Displays the dialog to set how many widgets.
function nk_cat_posts_widget_page() {
	$options = $newoptions = get_option('widget_cat_posts');
?>
	<div class="wrap">
		<form method="post">
			<h2>Category Posts Widgets</h2>
			<p style="line-height: 30px;">How many category posts widgets would you like?
			<select id="cat-posts-number" name="cat-posts-number" value="<?php echo $options['num_of_widgets']; ?>">
<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['num_of_widgets']==$i ? "selected='selected'" : '').">$i</option>"; ?>
			</select>
			<span class="submit"><input type="submit" name="cat-posts-number-submit" id="cat-posts-number-submit" value="<?php echo attribute_escape(__('Save')); ?>" /></span></p>
		</form>
	</div>
<?php
}

// Saves how many widgets to be displayed.
function nk_cat_posts_widget_setup() {
	$options = $newoptions = get_option('widget_cat_posts');
	if ( isset($_POST['cat-posts-number-submit']) ) {
		$number = (int) $_POST['cat-posts-number'];
		if ( $number > 9 ) $number = 9;
		if ( $number < 1 ) $number = 1;
		$newoptions['num_of_widgets'] = $number;
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_cat_posts', $options);
		nk_cat_posts_widget_register($options['num_of_widgets']);
	}
}

// Registers the widget(s).
function nk_cat_posts_widget_register() {
	$options = get_option('widget_cat_posts');
	$num_of_widgets = $options['num_of_widgets'];
	if ( $num_of_widgets < 1 ) $num_of_widgets = 1;
	if ( $num_of_widgets > 9 ) $num_of_widgets = 9;
	$dims = array('width' => 300, 'height' => 150);
	$class = array('classname' => 'widget_cat_posts');
	for ($i = 1; $i <= 9; $i++) {
		$name = sprintf('Category Posts %d', $i);
		$id = "cat-posts-$i";
		wp_register_sidebar_widget($id, $name, $i <= $num_of_widgets ? 'nk_cat_posts_widget' : '', $class, $i);
		wp_register_widget_control($id, $name, $i <= $num_of_widgets ? 'nk_cat_posts_widget_control' : '', $dims, $i);
	}
	add_action('sidebar_admin_setup', 'nk_cat_posts_widget_setup');
	add_action('sidebar_admin_page', 'nk_cat_posts_widget_page');
}

function nk_cat_posts($cat, $num = 5) {
	$catposts = get_posts('numberposts='.$num.'&category='.$cat);
	
	foreach($catposts as $post) {
		echo '<li><a href="'.get_permalink($post).'">'.$post->post_title.'</a></li>';
	}
}

add_action('widgets_init', 'nk_cat_posts_widget_register');

?>