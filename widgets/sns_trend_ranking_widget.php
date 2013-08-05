<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 13/08/05
 * Time: 17:23
 */

/**
 * RankingWidget Class
 */
class SnsTrendRankingWidget extends WP_Widget {
	/** constructor */
	function __construct() {
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
		var_dump($new_instance);
		return $new_instance;
	}

	/** @see WP_Widget::form */
	function form($instance) {
		var_dump($instance);
		$title = esc_attr($instance['title']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<p>
			<label><input type="radio" id="<?php echo $this->get_field_id('aaa'); ?>" name="<?php echo $this->get_field_name('aaa'); ?>" value="1"> 今日</label><br>
			<label><input type="radio" id="<?php echo $this->get_field_id('aaa'); ?>" name="<?php echo $this->get_field_name('aaa'); ?>" value="2"> 今日</label><br>
			<label><input type="radio" id="<?php echo $this->get_field_id('aaa'); ?>" name="<?php echo $this->get_field_name('aaa'); ?>" value="3"> 今日</label><br>
			<label><input type="radio" id="<?php echo $this->get_field_id('aaa'); ?>" name="<?php echo $this->get_field_name('aaa'); ?>" value="4"> 今日</label><br>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sex' ); ?>">Sex:</label>
			<select id="<?php echo $this->get_field_id( 'sex' ); ?>" name="<?php echo $this->get_field_name( 'sex' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( 'male' == $instance['sex'] ) echo 'selected="selected"'; ?>>male</option>
				<option <?php if ( 'female' == $instance['sex'] ) echo 'selected="selected"'; ?>>female</option>
			</select>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( @$instance['show_sex'], true ); ?> id="<?php echo $this->get_field_id( 'show_sex' ); ?>" name="<?php echo $this->get_field_name( 'show_sex' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_sex' ); ?>">Display sex publicly?</label>
		</p>
	<?php
		echo '<div>name:<br /><input name="' . $this->get_field_name('name') . '" type="text" value="' . $instance['name'] . '" /></div>';

	}

}

