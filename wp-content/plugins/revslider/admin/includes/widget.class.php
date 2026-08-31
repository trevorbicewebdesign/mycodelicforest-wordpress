<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * Classic WordPress widget that outputs one slider. Still reachable on block-widget sites through the
 * Legacy Widget block.
 */
class RevSliderWidget extends WP_Widget {
	
    public function __construct(){
        //actual widget process
        parent::__construct('rev-slider-widget', __('Slider Revolution', 'revslider'), ['classname' => 'widget_revslider', 'description' => __('Displays a Slider Revolution Module on the page', 'revslider')]);
    }
	
	/**
	 * Register the classic widget.
	 *
	 * Deliberately NOT gated on wp_use_widgets_block_editor(): classic widgets stay reachable through the
	 * Legacy Widget block, so skipping registration on block-widget sites would remove a working feature.
	 * The filter lets a site drop it without having to unhook 'widgets_init'.
	 *
	 * @return void
	 */
	public static function register_widget(){
		if(apply_filters('revslider_register_widget', true) !== true) return;

		register_widget('RevSliderWidget');
	}
 
    /**
     * the form
     */
    public function form($instance){
		$sliders = [];
		$_slider = new RevSliderSlider();
		
		try {
            $sliders = $_slider->get_sliders_short();
        }catch(Exception $e){}            
          
		if(empty($sliders)){
			echo __('No Sliders found, Please create a Slider first', 'revslider');
			return;
		}
	
		$sliderID		 = $_slider->get_val($instance, 'rev_slider');
		$fieldID_check	 = $this->get_field_id('rev_slider_homepage');
		$fieldPages_ID	 = $this->get_field_id('rev_slider_pages');
		$fieldTitle_ID	 = $this->get_field_id('rev_slider_title');
		?>
		<p>
			<span style="display: inline-block; width: 130px"><label for="<?php echo esc_attr($fieldTitle_ID); ?>"><?php _e('Title', 'revslider')?>:</label></span>
			<input type="text" style="display: inline-block; width: auto;" name="<?php echo esc_attr($this->get_field_name('rev_slider_title')); ?>" id="<?php echo esc_attr($fieldTitle_ID); ?>" value="<?php echo esc_attr($_slider->get_val($instance, 'rev_slider_title')); ?>" class="widefat">
		</p>
		<p>
			<span style="display: inline-block; width: 130px"><?php _e('Choose Slider', 'revslider'); ?>:</span>
			<select name="<?php echo esc_attr($this->get_field_name('rev_slider')); ?>" id="<?php echo esc_attr($this->get_field_id('rev_slider')); ?>">
				<?php
				foreach($sliders ?? [] as $sid => $item){
					$selected = (trim($sid) == trim($sliderID)) ? ' selected' : '';
					echo '<option'.$selected.' value="'.esc_attr($sid).'">'.esc_html($item).'</option>';
				}
				?>
			</select>
		</p>
		<p>
			<span style="display: inline-block; width: 130px"><label for="<?php echo esc_attr($fieldID_check); ?>"><?php _e('Home Page Only', 'revslider'); ?>:</label></span>
			<input type="checkbox" name="<?php echo esc_attr($this->get_field_name('rev_slider_homepage')); ?>" id="<?php echo esc_attr($fieldID_check); ?>" <?php echo ($_slider->get_val($instance, 'rev_slider_homepage') == 'on') ? "checked='checked'" : ''; ?>>
		</p>
		<p>
			<span style="display: inline-block; width: 130px"><label for="<?php echo esc_attr($fieldPages_ID); ?>"><?php _e('Pages (example: 2,10):', 'revslider'); ?></label></span>
			<input type="text" name="<?php echo esc_attr($this->get_field_name('rev_slider_pages')); ?>" id="<?php echo esc_attr($fieldPages_ID); ?>" value="<?php echo esc_attr($_slider->get_val($instance, 'rev_slider_pages')); ?>">
		</p>
		<?php
    }
 
 
    /**
     * update
     */
    public function update($new_instance, $old_instance){
        return $new_instance;
    }

    
    /**
     * widget output
     */
    public function widget($args, $instance){
		try {
			$_slider = new RevSliderSlider();
			$sid	 = $_slider->get_val($instance, 'rev_slider');
			
			if(empty($sid)) return(false);
			
			$homepage	= ($_slider->get_val($instance, 'rev_slider_homepage') == 'on') ? 'homepage' : '';
			$pages		= $_slider->get_val($instance, 'rev_slider_pages');
			
			if(!empty($pages)){
				$homepage .= (!empty($homepage)) ? ',' : '';
				$homepage .= $pages;
			}
			
			$_slider->init_by_id($sid);
			if($_slider->_truefalse($_slider->get_param(['general', 'disableOnMobile'], false)) === true && wp_is_mobile()) return false;	//loose == treated the STRING "false" as true
			
			$output		= new RevSlider7Output();
			//run the standard WP widget_title filter (was missing) so themes/plugins can hook it like on any other widget
			$title		= apply_filters('widget_title', $_slider->get_val($instance, 'rev_slider_title'), $instance, $this->id_base);

			//widget output
			echo $_slider->get_val($args, 'before_widget');

			//esc_html() is stricter than WP core (which echoes the title raw) - a slider widget title is plain text
			echo (!empty($title)) ? $_slider->get_val($args, 'before_title'). esc_html($title) .$_slider->get_val($args, 'after_title') : '';
			
			$output->set_add_to($homepage);
			$slider = $output->add_slider_to_stage($sid);
			
			add_action('wp_head', [$this, 'write_css']);
			
			echo $_slider->get_val($args, 'after_widget');
		}catch(Exception $e){
			$message = $e->getMessage();
			
			$output->print_error_message($message);
		}
    }

    public function write_css(){
		
	}

}