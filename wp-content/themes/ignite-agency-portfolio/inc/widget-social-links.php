<?php
/**
 * Widget Social Links
 *
 * @package ignite_agency_portfolio
 */

// register Ignite_Agency_Portfolio_Social_Links widget 
function ignite_agency_portfolio_register_social_links_widget() {
    register_widget( 'Ignite_Agency_Portfolio_Social_Links' );
}
add_action( 'widgets_init', 'ignite_agency_portfolio_register_social_links_widget' );

if( ! class_exists( 'Ignite_Agency_Portfolio_Social_Links' ) ): 
 /**
 * Adds Ignite_Agency_Portfolio_Social_Links widget.
 */
class Ignite_Agency_Portfolio_Social_Links extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'ignite_agency_portfolio_social_links', // Base ID
			esc_html__( 'TI: Social Links', 'ignite-agency-portfolio' ), // Name
			array( 'description' => esc_html__( 'A Social Links Widget', 'ignite-agency-portfolio' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	   
        $ignite_agency_portfolio_title      = ! empty( $instance['title'] ) ? $instance['title'] : '';		
        $ignite_agency_portfolio_facebook   = ! empty( $instance['facebook'] ) ? $instance['facebook'] : '' ;
        $ignite_agency_portfolio_instagram  = ! empty( $instance['instagram'] ) ? $instance['instagram'] : '' ;
        $ignite_agency_portfolio_twitter    = ! empty( $instance['twitter'] ) ? $instance['twitter'] : '' ;
        $ignite_agency_portfolio_pinterest  = ! empty( $instance['pinterest'] ) ? $instance['pinterest'] : '' ;
        $ignite_agency_portfolio_linkedin   = ! empty( $instance['linkedin'] ) ? $instance['linkedin'] : '' ;
        $ignite_agency_portfolio_youtube    = ! empty( $instance['youtube'] ) ? $instance['youtube'] : '' ;
        $ignite_agency_portfolio_tiktok    = ! empty( $instance['tiktok'] ) ? $instance['tiktok'] : '' ;
        
        if( $ignite_agency_portfolio_facebook || $ignite_agency_portfolio_instagram || $ignite_agency_portfolio_twitter || $ignite_agency_portfolio_pinterest || $ignite_agency_portfolio_linkedin || $ignite_agency_portfolio_youtube || $ignite_agency_portfolio_tiktok ){ 
        echo $args['before_widget'];
        if($ignite_agency_portfolio_title)
        echo $args['before_title'] . apply_filters( 'widget_title', $ignite_agency_portfolio_title, $instance, $this->id_base ) . $args['after_title'];
        ?>
            <ul class="social-networks">
				<?php if( $ignite_agency_portfolio_facebook ){ ?>
                <li><a href="<?php echo esc_url( $ignite_agency_portfolio_facebook ); ?>" title="<?php esc_attr_e( 'Facebook', 'ignite-agency-portfolio' ); ?>" ><i class="fa fa-facebook"></i></a></li>
				<?php } if( $ignite_agency_portfolio_instagram ){ ?>
                <li><a href="<?php echo esc_url( $ignite_agency_portfolio_instagram ); ?>" title="<?php esc_attr_e( 'Instagram', 'ignite-agency-portfolio' ); ?>"><i class="fa fa-instagram"></i></a></li>
                <?php } if( $ignite_agency_portfolio_twitter ){ ?>
                <li><a href="<?php echo esc_url( $ignite_agency_portfolio_twitter ); ?>" title="<?php esc_attr_e( 'Twitter', 'ignite-agency-portfolio' ); ?>"><i class="fa fa-twitter"></i></a></li>
				<?php } if( $ignite_agency_portfolio_pinterest ){ ?>
                <li><a href="<?php echo esc_url( $ignite_agency_portfolio_pinterest ); ?>"  title="<?php esc_attr_e( 'Pinterest', 'ignite-agency-portfolio' ); ?>"><i class="fa fa-pinterest-p"></i></a></li>
				<?php } if( $ignite_agency_portfolio_linkedin ){ ?>
                <li><a href="<?php echo esc_url( $ignite_agency_portfolio_linkedin ); ?>" title="<?php esc_attr_e( 'Linkedin', 'ignite-agency-portfolio' ); ?>"><i class="fa fa-linkedin"></i></a></li>
				<?php } if( $ignite_agency_portfolio_youtube ){ ?>
                <li><a href="<?php echo esc_url( $ignite_agency_portfolio_youtube ); ?>" title="<?php esc_attr_e( 'YouTube', 'ignite-agency-portfolio' ); ?>"><i class="fa fa-youtube"></i></a></li>
                <?php } if( $ignite_agency_portfolio_tiktok ){ ?>
                <li><a href="<?php echo esc_url( $ignite_agency_portfolio_tiktok ); ?>" title="<?php esc_attr_e( 'Tiktok', 'ignite-agency-portfolio' ); ?>"><i class="fab fa-tiktok"></i></a></li>
                <?php } ?>
			</ul>
        <?php
        echo $args['after_widget'];
        }
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
        
        $ignite_agency_portfolio_title      = ! empty( $instance['title'] ) ? $instance['title'] : '';		
        $ignite_agency_portfolio_facebook   = ! empty( $instance['facebook'] ) ? $instance['facebook'] : '' ;
        $ignite_agency_portfolio_instagram  = ! empty( $instance['instagram'] ) ? $instance['instagram'] : '' ;
        $ignite_agency_portfolio_twitter    = ! empty( $instance['twitter'] ) ? $instance['twitter'] : '' ;
        $ignite_agency_portfolio_pinterest  = ! empty( $instance['pinterest'] ) ? $instance['pinterest'] : '' ;
        $ignite_agency_portfolio_linkedin   = ! empty( $instance['linkedin'] ) ? $instance['linkedin'] : '' ;
        $ignite_agency_portfolio_youtube    = ! empty( $instance['youtube'] ) ? $instance['youtube'] : '' ;
        $ignite_agency_portfolio_tiktok    = ! empty( $instance['tiktok'] ) ? $instance['tiktok'] : '' ;
        
        ?>
		
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'ignite-agency-portfolio' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $ignite_agency_portfolio_title ); ?>" />
		</p>
        
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'facebook' ) ); ?>"><?php esc_html_e( 'Facebook', 'ignite-agency-portfolio' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'facebook' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'facebook' ) ); ?>" type="text" value="<?php echo esc_attr( $ignite_agency_portfolio_facebook ); ?>" />
		</p>
        
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'instagram' ) ); ?>"><?php esc_html_e( 'Instagram', 'ignite-agency-portfolio' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'instagram' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'instagram' ) ); ?>" type="text" value="<?php echo esc_attr( $ignite_agency_portfolio_instagram ); ?>" />
		</p>
                
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'twitter' ) ); ?>"><?php esc_html_e( 'Twitter', 'ignite-agency-portfolio' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'twitter' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'twitter' ) ); ?>" type="text" value="<?php echo esc_attr( $ignite_agency_portfolio_twitter ); ?>" />
		</p>
        
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'pinterest' ) ); ?>"><?php esc_html_e( 'Pinterest', 'ignite-agency-portfolio' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'pinterest' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'pinterest' ) ); ?>" type="text" value="<?php echo esc_attr( $ignite_agency_portfolio_pinterest ); ?>" />
		</p>
        
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'linkedin' ) ); ?>"><?php esc_html_e( 'LinkedIn', 'ignite-agency-portfolio' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'linkedin' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'linkedin' ) ); ?>" type="text" value="<?php echo esc_attr( $ignite_agency_portfolio_linkedin ); ?>" />
		</p>
        
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'youtube' ) ); ?>"><?php esc_html_e( 'YouTube', 'ignite-agency-portfolio' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'youtube' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'youtube' ) ); ?>" type="text" value="<?php echo esc_attr( $ignite_agency_portfolio_youtube ); ?>" />
		</p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'tiktok' ) ); ?>"><?php esc_html_e( 'Tiktok', 'ignite-agency-portfolio' ); ?></label> 
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'tiktok' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tiktok' ) ); ?>" type="text" value="<?php echo esc_attr( $ignite_agency_portfolio_tiktok ); ?>" />
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
        $instance = array();
		
        $instance['title']     = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) :'';
        $instance['facebook']  = ! empty( $new_instance['facebook'] ) ? esc_url_raw( $new_instance['facebook'] ) : '';
        $instance['instagram'] = ! empty( $new_instance['instagram'] ) ? esc_url_raw( $new_instance['instagram'] ) : '';
        $instance['twitter']   = ! empty( $new_instance['twitter'] ) ? esc_url_raw( $new_instance['twitter'] ) : '';
        $instance['pinterest'] = ! empty( $new_instance['pinterest'] ) ? esc_url_raw( $new_instance['pinterest'] ) : '';
        $instance['linkedin']  = ! empty( $new_instance['linkedin'] ) ? esc_url_raw( $new_instance['linkedin'] ) : '';
        $instance['youtube']   = ! empty( $new_instance['youtube'] ) ? esc_url_raw( $new_instance['youtube'] ) : '';
        $instance['tiktok']    = ! empty( $new_instance['tiktok'] ) ? esc_url_raw( $new_instance['tiktok'] ) : '';
		
        return $instance;
                
	}

} // class Ignite_Agency_Portfolio_Social_Links 
endif;