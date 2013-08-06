<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 13/08/05
 * Time: 17:23
 */

//namespace SnsTrend\widgets;
//use WP_Widget;

use SnsTrend\Model\Trends;


/**
 * Class SnsTrendRankingWidget
 */
class SnsTrendRankingWidget extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::__construct(false, $name = 'RankingWidget');
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {
		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$before_widget='';$before_title='';$after_title='';$after_widget='';
		extract( $args );
		var_dump($instance);

		//TODO post_idごとの数を並べる
		$trends = new Trends();
		$result = $trends->get();
		var_dump($result);

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$team = empty($instance['team']) ? '' : $instance['team'] ;

		?>
		<?php echo $before_widget; ?>
		<?php if ( $title )
			echo $before_title . $title . $after_title; ?>

		<strong>Hello, World!</strong>

		<?php echo $after_widget; ?>
	<?php
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $new_instance;
	}

	/** @see WP_Widget::form */
	function form($instance) {
		var_dump($instance);
		$title = empty($instance['title']) ? "" : esc_attr($instance['title']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<?php $team = empty($instance['team']) ? "day" : esc_attr($instance['team']); ?>
		<p>
			<label><input type="radio" id="<?php echo $this->get_field_id('team')."_day"; ?>" name="<?php echo $this->get_field_name('team'); ?>" value="day"<?php checked( $team, "day" ); ?>> 今日</label><br>
			<label><input type="radio" id="<?php echo $this->get_field_id('team')."_week"; ?>" name="<?php echo $this->get_field_name('team'); ?>" value="week"<?php checked( $team, "week" ); ?>> 今週</label><br>
			<label><input type="radio" id="<?php echo $this->get_field_id('team')."_month"; ?>" name="<?php echo $this->get_field_name('team'); ?>" value="month"<?php checked( $team, "month" ); ?>> 今月</label><br>
			<label><input type="radio" id="<?php echo $this->get_field_id('team')."_year"; ?>" name="<?php echo $this->get_field_name('team'); ?>" value="year"<?php checked( $team, "year" ); ?>> 今年</label><br>
		</p>

		<?php $sex = empty($instance['sex']) ? "" : esc_attr($instance['sex']); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'sex' ); ?>">Sex:</label>
			<select id="<?php echo $this->get_field_id( 'sex' ); ?>" name="<?php echo $this->get_field_name( 'sex' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( 'male' == $sex ) echo 'selected="selected"'; ?>>male</option>
				<option <?php if ( 'female' == $sex ) echo 'selected="selected"'; ?>>female</option>
			</select>
		</p>

		<?php $show_sex = empty($instance['show_sex']) ? "" : esc_attr($instance['show_sex']); ?>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_sex, 'on' ); ?> id="<?php echo $this->get_field_id( 'show_sex' ); ?>" name="<?php echo $this->get_field_name( 'show_sex' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_sex' ); ?>">Display sex publicly?</label>
		</p>

		<?php
	}

}

