<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 13/08/05
 * Time: 17:23
 */

//namespace SnsTrend\widgets;
//use WP_Widget;

/**
 * RankingWidget Class
 */
class RankingWidget extends WP_Widget {
	/** constructor */
//	function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {
	function __construct(  ) {
		parent::__construct(false, $name = 'RankingWidget');
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {
		$before_widget=null;$before_title=null;$after_title=null;$after_widget=null;
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		?>
		<?php echo $before_widget; ?>
		<?php if ( $title )
			echo $before_title . $title . $after_title; ?>
		Hello, World!
		<?php echo $after_widget; ?>
	<?php
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	/** @see WP_Widget::form */
	function form($instance) {
		$title = esc_attr($instance['title']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
	<?php
	}

} // class FooWidget


class HogeWidget extends WP_Widget {

	function __construct(  ) {
		parent::__construct(false, $name = 'ほげうぃじぇっと');
	}
	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);

		echo $before_widget;
		if ($title) echo $before_title . $title . $after_title;
		echo 'Hello ' . htmlspecialchars($instance['name']) . '!';
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['name'] = strip_tags($new_instance['name']);
		return $instance;
	}

	function form($instance) {
		echo '<div>title:<br /><input name="' . $this->get_field_name('title') . '" type="text" value="' . $instance['title'] . '" /></div>';
		echo '<div>name:<br /><input name="' . $this->get_field_name('name') . '" type="text" value="' . $instance['name'] . '" /></div>';
	}
}