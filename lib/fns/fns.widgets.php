<?php
/**
 * ObitsCPT_ObituaryFacts_Widget Class
 */
add_action('widgets_init', create_function('', 'return register_widget("ObitsCPT_ObituaryFacts_Widget");'));
class ObitsCPT_ObituaryFacts_Widget extends WP_Widget {
	/** constructor */
	function __construct() {
		$widget_ops = array('classname' => 'eg-obituaryfacts-widget', 'description' => __( 'Displays obituary facts entered for an individual obituary.') );
		parent::WP_Widget(false, $name = 'Obituary Facts', $widget_ops);
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		global $obit_info;
		extract( $args );
		if( is_array( $obit_info ) ){
			echo $before_widget;
			echo $before_title . 'Facts' . $after_title;
			//echo '<textarea style="height: 80px">'.print_r( $obit_info, true ).'</textarea>';
			?><ul><?php
			$facts = array( 'birthdate', 'birthplace', 'deathdate', 'deathplace', 'maiden', 'occupation', 'hobbies' );
			foreach( $facts as $fact ){
					$title = '';
					$data = '';
					switch( $fact ){
						case 'birthdate':
						case 'deathdate':
							( stristr( $fact, 'birth' ) )? $title = 'Birth' : $title = 'Death';
							$data = date( 'F j, Y', strtotime( $obit_info[$fact] ) );
						break;
						case 'birthplace':
						case 'deathplace':
							$title = ucfirst( $fact );
							$variable_key = substr( $fact, 0, 5 );
							if( empty( $obit_info[$fact] ) ){
								if( !empty( $obit_info[$variable_key.'city'] ) && !empty( $obit_info[$variable_key.'state'] ) ){
									$data = $obit_info[$variable_key.'city']. ', '.$obit_info[$variable_key.'state'];
								} elseif( !empty( $obit_info[$variable_key.'city'] ) ){
									$data = $obit_info[$variable_key.'city'];
								} elseif( !empty( $obit_info[$variable_key.'state'] ) ){
									$data = $obit_info[$variable_key.'state'];
								}
							} else {
								if(
									$obit_info[$fact] != ( $obit_info[$variable_key.'city']. ', '.$obit_info[$variable_key.'state'] )
									&&
									!empty( $obit_info[$variable_key.'city'] )
									&&
									!empty( $obit_info[$variable_key.'state'] )
								){
									$data = $obit_info[$fact].', '.$obit_info[$variable_key.'city']. ', '.$obit_info[$variable_key.'state'];
								} else {
									$data = $obit_info[$fact];
								}
							}
						break;
						case 'hobbies':
							$title = 'Organizations/Hobbies';
							$data = $obit_info[$fact];
						break;
						case 'maiden':
							$title = 'Maiden Name';
							$data = $obit_info[$fact];
						break;
						default:
							$title = ucfirst( $fact );
							$data = $obit_info[$fact];
						break;
					}
					if( !empty( $data ) ) printf( '<li><em>%1s:</em> %2s</li>', $title, $data );
			}
			?></ul><?php
			echo $after_widget;
		}
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		?><p>No options exist for this widget.</p><?php
	}

} // class ObitsCPT_ObituaryFacts_Widget

/**
 * ObitsCPT_ObituaryServices_Widget Class
 */
add_action('widgets_init', create_function('', 'return register_widget("ObitsCPT_ObituaryServices_Widget");'));
class ObitsCPT_ObituaryServices_Widget extends WP_Widget {
	/** constructor */
	function __construct() {
		$widget_ops = array('classname' => 'eg-obituaryservices-widget', 'description' => __( 'Displays obituary services entered for an individual obituary.') );
		parent::WP_Widget(false, $name = 'Obituary Services', $widget_ops);
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		global $obit_services;
		extract( $args );
		if( is_array( $obit_services ) ){
			echo $before_widget;
			echo $before_title . 'Services' . $after_title;
			//echo '<textarea style="height: 80px">'.print_r( $obit_info, true ).'</textarea>';
			echo '<div class="services">';
			foreach( $obit_services as $service ){
				echo '<h3>'.$service['service_type'].'</h3>';
				echo '<p>';
				if( !empty( $service['service_date'] ) ) echo date( 'l, F j, Y', strtotime( $service['service_date'] ) ).', ';
				if( !empty( $service['start_time'] ) ) echo $service['start_time'];
				if( !empty( $service['end_time'] ) ) echo ' - '.$service['end_time'];
				if( !empty( $service['service_location'] ) ) echo ' at '.$service['service_location'];
				if( !empty( $service['service_address'] ) ) echo ', '.$service['service_address'];
				if( !empty( $service['city'] ) ) echo ', '.$service['city'];
				if( !empty( $service['state'] ) ) echo ', '.$service['state'];
				if( !empty( $service['zip'] ) ) echo ' '.$service['zip'];
				if( !empty( $service['map_url'] ) ) echo ' (<a href="'.$service['map_url'].'" target="_blank">View Map</a>)';
				echo '</p>';
			}
			echo '</div>';
		}
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		?><p>No options exist for this widget.</p><?php
	}

} // class ObitsCPT_ObituaryServices_Widget

/**
 * EgFeaturedExcerptWidget Class
 */
add_action('widgets_init', create_function('', 'return register_widget("EgFeaturedExcerptWidget");'));
class EgFeaturedExcerptWidget extends WP_Widget {
    /** constructor */
    function EgFeaturedExcerptWidget() {
        $widget_ops = array('classname' => 'widget_eg-featured-excerpt-widget', 'description' => __( 'Displays a 180px wide image along with a page\'s excerpt and a link to that page.') );
		parent::WP_Widget(false, $name = 'Featured Excerpt Widget', $widget_ops);
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		$id = $instance['page_id'];
		( !empty( $instance['more_label'] ) )? $more_label = $instance['more_label'] : $more_label = 'Read more&hellip;';
		$link = get_permalink($instance['page_id']);
		( !empty( $instance['excerpt_len'] ) && is_numeric( $instance['excerpt_len'] ) )? $excerpt_len = $instance['excerpt_len'] : $excerpt_len = 90;
		( empty( $instance['show_featured'] ) )? $show_featured = 0 : $show_featured = $instance['show_featured'];

		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;
		echo '<div class="entry-text">';
		if( $show_featured == true ){
			echo '<a href="'.$link.'">';
			if(has_post_thumbnail($id))
				echo get_the_post_thumbnail($id,'featured-excerpt-image');
			else
				echo '<img src="http://placehold.it/180x240/666/fff" class="featured-excerpt-image alignleft" alt="" />';
			echo '</a>';
		}
		$post = get_post( $id );
		if( !empty( $post->post_excerpt ) ){
			$excerpt = $post->post_excerpt;
		} else {
			$content = strip_tags( $post->post_content, '<p><a><em><strong><h1><h2><h3><h4><h5><h6>' );
			$words = explode(' ',$content);
			for($x = 0; $x <= $excerpt_len; $x++){
				$excerpt.= ' '.$words[$x];
			}
			if( !in_array( substr( $excerpt, -1 ), array( '.', '?', '!' ) ) ) $excerpt.= '[&hellip;]'; // add an ellipsis if the excerpt doesn't end at the end of a sentence
		}
		echo apply_filters( 'the_excerpt', $excerpt.' <a class="more-link" href="'.$link.'">'.$more_label.'</a>' );
        echo '</div><!-- .entry-text -->';
        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['page_id'] = $new_instance['page_id'];
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['more_label'] = strip_tags( $new_instance['more_label'] );
		$instance['excerpt_len'] = strip_tags( intval( $new_instance['excerpt_len'] ) );
		if( $new_instance['show_featured'] != 1) $new_instance['show_featured'] = 0;
		$instance['show_featured'] = $new_instance['show_featured'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
		$page_id = esc_attr( $instance['page_id'] );
		$title = esc_attr( $instance['title'] );
		$more_label = esc_attr( $instance['more_label'] );
		$show_featured = $instance['show_featured'];
		( is_numeric( $instance['excerpt_len'] ) )? $excerpt_len = intval( $instance['excerpt_len'] ) : $excerpt_len = '';

		?>
            <p><label style="margin-bottom: .5em; display: block; font-size: 11px" for="<?php echo $this->get_field_id('page_id'); ?>">Select the page whose thumbnail you wish to display:</label>
			<?php
			$args = array('selected' => $page_id, 'name' => $this->get_field_name('page_id'), 'show_option_none' => 'Select a page...');
			wp_dropdown_pages($args); ?></p>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
			</label></p>
			<p><label for="<?php echo $this->get_field_id('more_label'); ?>"><?php _e('More Label:'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('more_label'); ?>" name="<?php echo $this->get_field_name('more_label'); ?>" type="text" value="<?php echo $more_label; ?>" />
			</label></p>
			<p><label for="<?php echo $this->get_field_id('excerpt_len'); ?>"><?php _e('Excerpt Length:'); ?>
			<input class="" id="<?php echo $this->get_field_id('excerpt_len'); ?>" name="<?php echo $this->get_field_name('excerpt_len'); ?>" type="text" value="<?php echo $excerpt_len; ?>" /><br /><span style="font-size: 11px">Defaults to 90 words.</span>
			</label></p>
			<p><label for="<?php echo $this->get_field_id('show_featured'); ?>"><input id="<?php echo $this->get_field_id('show_featured'); ?>" name="<?php echo $this->get_field_name('show_featured'); ?>" type="checkbox" value="1"<?php if( $show_featured == true ) echo ' checked="checked"' ?> /> <?php _e('Show featured image.'); ?></label></p>
        <?php
    }

} // class EgFeaturedExcerptWidget

add_action('widgets_init', create_function('', 'return register_widget("ObitsCPT_Widget_Search");'));
class ObitsCPT_Widget_Search extends WP_Widget {

	/**
	 * Prefix for the widget.
	 * @since 0.7.0
	 */
	var $prefix;

	/**
	 * Textdomain for the widget.
	 * @since 0.7.0
	 */
	var $textdomain;

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 * @since 0.6.0
	 */
	function ObitsCPT_Widget_Search() {

		/* Set up the widget options. */
		$widget_options = array(
			'classname' => 'search',
			'description' => esc_html__( 'Specify which post_type this widget will search.', 'obitscpt' )
		);

		/* Set up the widget control options. */
		$control_options = array(
			'width' => 525,
			'height' => 350,
			'id_base' => "obitscpt-search-post-types",
			'post_type' => 'all'
		);

		/* Create the widget. */
		$this->WP_Widget( "obitscpt-search-post-types", esc_attr__( 'Search Post Type', 'obitscpt' ), $widget_options, $control_options );
	}

	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 * @since 0.6
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Output the theme's $before_widget wrapper. */
		echo $before_widget;

		/* If a title was input by the user, display it. */
		if ( !empty( $instance['title'] ) )
			echo $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;

		/* If the user chose to use the theme's search form, load it. */
		if ( !empty( $instance['theme_search'] ) ) {
			get_search_form();
		}

		/* Else, create the form based on the user-selected arguments. */
		else {

			/* Set up some variables for the search form. */
			global $search_form_num;
			$search_num = ( ( $search_form_num ) ? '-' . esc_attr( $search_form_num ) : '' );
			$search_text = ( ( is_search() ) ? esc_attr( get_search_query() ) : esc_attr( $instance['search_text'] ) );

			/* Open the form. */
			$search = '<form method="get" class="search-form" id="search-form' . $search_num . '" action="' . home_url() . '/"><div>';
			if( !empty( $instance['post_type'] ) ) $search.= '<input type="hidden" name="post_type" value="'.$instance['post_type'].'" />';
			/* If a search label was set, add it. */
			if ( !empty( $instance['search_label'] ) )
				$search .= '<label for="search-text' . $search_num . '">' . $instance['search_label'] . '</label>';

			/* Search form text input. */
			$search .= '<input class="search-text" type="text" name="s" id="search-text' . $search_num . '" value="' . $search_text . '" onfocus="if(this.value==this.defaultValue)this.value=\'\';" onblur="if(this.value==\'\')this.value=this.defaultValue;" />';

			/* Search form submit button. */
			if ( $instance['search_submit'] )
				$search .= '<input class="search-submit button" name="submit" type="submit" id="search-submit' . $search_num . '" value="' . esc_attr( $instance['search_submit'] ) . '" />';

			/* Close the form. */
			$search .= '</div></form><!-- .search-form -->';

			/* Display the form. */
			echo $search;

			$search_form_num++;
		}

		/* Close the theme's widget wrapper. */
		echo $after_widget;
	}

	/**
	 * Updates the widget control options for the particular instance of the widget.
	 * @since 0.6
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['post_type'] = strip_tags( $new_instance['post_type'] );
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['search_label'] = strip_tags( $new_instance['search_label'] );
		$instance['search_text'] = strip_tags( $new_instance['search_text'] );
		$instance['search_submit'] = strip_tags( $new_instance['search_submit'] );
		$instance['theme_search'] = ( isset( $new_instance['theme_search'] ) ? 1 : 0 );

		return $instance;
	}

	/**
	 * Displays the widget control options in the Widgets admin screen.
	 * @since 0.6
	 */
	function form( $instance ) {

		/* Set up the default form values. */
		$defaults = array(
			'post_type' => '',
			'title' => esc_attr__( 'Search', $this->textdomain ),
			'theme_search' => false,
			'search_label' => '',
			'search_text' => '',
			'search_submit' => ''
		);

		/* Merge the user-selected arguments with the defaults. */
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<div class="hybrid-widget-controls columns-2">
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', $this->textdomain ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'search_label' ); ?>"><?php _e( 'Search Label:', $this->textdomain ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'search_label' ); ?>" name="<?php echo $this->get_field_name( 'search_label' ); ?>" value="<?php echo esc_attr( $instance['search_label'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'search_text' ); ?>"><?php _e( 'Search Text:', $this->textdomain ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'search_text' ); ?>" name="<?php echo $this->get_field_name( 'search_text' ); ?>" value="<?php echo esc_attr( $instance['search_text'] ); ?>" />
		</p>
		</div>

		<div class="hybrid-widget-controls columns-2 column-last">
		<p>
			<label for="<?php echo $this->get_field_id( 'search_submit' ); ?>"><?php _e( 'Search Submit:', $this->textdomain ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'search_submit' ); ?>" name="<?php echo $this->get_field_name( 'search_submit' ); ?>" value="<?php echo esc_attr( $instance['search_submit'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'post_type' ) ?>"><?php _e( 'Post Type:', $this->textdomain ) ?></label>
			<select class="select" id="<?php echo $this->get_field_id( 'post_type' ) ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>"><?php
			$post_types = get_post_types();
			echo '<option value="">All</option>';
			foreach( $post_types as $post_type ){
				echo '<option value="'.$post_type.'"';
				if( $instance['post_type'] == $post_type ) echo ' selected="selected"';
				echo '>'.$post_type.'</option>';
			}
			?></select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'theme_search' ); ?>">
			<input class="checkbox" type="checkbox" <?php checked( $instance['theme_search'], true ); ?> id="<?php echo $this->get_field_id( 'theme_search' ); ?>" name="<?php echo $this->get_field_name( 'theme_search' ); ?>" /> <?php _e( 'Use theme\'s <code>searchform.php</code>?', $this->textdomain ); ?></label>
		</p>
		</div>
		<div style="clear:both;">&nbsp;</div>
	<?php
	}
}

//add_filter('pre_get_posts','ObitsCPT_SearchFilter');
function ObitsCPT_SearchFilter($query) {
	if ($query->is_search) {
		// Insert the specific post type you want to search
		$query->set('post_type', 'feeds');
  	}
	return $query;
}
?>