<?php

class Widget_Clima_Freekitime extends WP_Widget {

	function __construct()
	{
		$widget_ops = array('classname' => 'widget_clima_freekitime', 'description' => __( 'obtiene las condiciones climaticas de la ciudad elegida') );
		parent::__construct('clima_freekitime', __('clima_freekitime'), $widget_ops);
	}

	
	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title'], $instance, $this->id_base);
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo '<div id="clima_freekitime_wrap">';
		clima_freekitime();
		echo '</div>';
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titulo:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
<?php
	}

}

function widget_clima_freekitime_init()
{
register_widget('Widget_Clima_Freekitime');
}

?>
