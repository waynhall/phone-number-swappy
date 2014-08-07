<?php
/**
 * Plugin Name: Phone Number Swappy
 * Plugin URI: http://www.anchorwave.com
 * Description: Used to swap phone numbers
 * Version: 1.0.0
 * Author: Jameel Bokhari
 * Author URI: http://www.codeatlarge.com
 * License: GPL2
 */
/*
Copyright 2014  Jameel Bokhari  ( email : me@jameelbokhari.com )

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define("ECT_RELATED_CONTENT_PATH", dirname(__FILE__));
define("ECT_RELATED_CONTENT_URL", plugin_dir_url( __FILE__ ) );
require_once('lava/class.lava.plugin.core.php');
/**
 * Class LavaPlugin
 * @uses LavaCorePlugin Version 2.2
 * @package ECT Related Content
 */
class PhoneNumberSwappy extends PhoneNumberSwappyCore {
	public $prefix = 'pns_';
	public $ver = '1.0.0';
	public $option_prefix = 'pns_';
	public $name = 'pns';
	public $localize_object = 'PNS';
	protected $plugin_slug;
	protected static $instance;
	protected $templates;
	public function __construct(){
		parent::__construct();
	}
	public function init(){
		$this->useFrontendCss = false;
		$this->useFrontendJs = false;
		$this->useAdminCss = true;
		$this->useAdminJs = false;
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", array($this, 'add_settings_page') );
	}
	
	public static function get_instance() {
		if (null == self::$instance ) {
			self::$instance = new PhoneNumberSwappy();
		}
		return self::$instance;
	}
	/**
	 * Overrides default functionality
	 * @return type
	 */
	function add_settings_page($links) { 
	  $settings_link = '<a href="'.$this->static['options_page']['parent_slug'].'?page='.$this->static['options_page']['menu_slug'].'">Settings</a>'; 
	  array_unshift($links, $settings_link); 
	  return $links; 
	}
	function swappy_header_stuff(){
		if (is_user_logged_in())
			return;
		/*
		 * $sereferral (bool) used to track whether this is a refrral or not 
		 */
		$sereferral;
		/**
		 * $phoneNumbers (array) used to store/display phone numbers
		 */
		// $phoneNumbers;
		//if cookie is not set
		$cookieName = $this->prefix . "_referral";
		$days = $this->lava_options['cookie_length']->get_value();
		$this->_log("Cookie length set for $days days.");
		if ( ! isset( $_COOKIE[$cookieName] ) ){
		    // then lets set it, first...
		    // check if referral var available
		    if( isset( $_SERVER['HTTP_REFERER']) ) {
		        // if so parse url...
		        $ref = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
		        // and check if referral from google, yahoo, bing
		        if(true || strpos( $ref, "google.com" ) !== false || strpos( $ref, "yahoo.com" ) !== false || strpos( $ref, "bing.com" ) !== false ) { 
		            // is a referral
		            $sereferral = true;
		            setcookie( $cookieName, "true", time() + ( 60 * 60 * 24 * $days )  );
		            // one week time
		        } else {
		            // doesn't look like it was a referrral
		            $sereferral = false;
		            setcookie( $cookieName, "false", time() + ( 60 * 60 * 24 * $days )  );
		        }
		    // default to non referral if var is unvailable
		    } else {
		        $sereferral = false;
		        setcookie( $cookieName, "false", time() + ( 60 * 60 * 24 * $days )  );
		    }
		} else {
		    //otherwise consult the almighty cookie
		    if ( $_COOKIE[$cookieName] == "true" ){
		        $sereferral = true;
		    } else {
		        $sereferral = false;
		    }
		}
		$this->referral= $sereferral;
	}
	function is_referral(){
		if ( ! isset( $this->referral ) || empty( $this->referral ) )
			return false;
		else
			return $this->referral;
	}
	function get_numbers(){
		if ( isset($this->numbers) || ! empty($this->numbers) )
			return;
		$phoneNumbers = array();
		if ($this->is_referral()){
		    $phoneNumbers[0] = $this->lava_options["swappyNumber1"]->get_value();
		    $phoneNumbers[1] = $this->lava_options["swappyNumber2"]->get_value();
		    $phoneNumbers[2] = $this->lava_options["swappyNumber3"]->get_value();
		} else {    
		    $phoneNumbers[0] = $this->lava_options["phoneNumber1"]->get_value();
		    $phoneNumbers[1] = $this->lava_options["phoneNumber2"]->get_value();
		    $phoneNumbers[2] = $this->lava_options["phoneNumber3"]->get_value();
		}
		$this->numbers = $phoneNumbers;
	}	
	function swappyNumber1(){
		return $this->numbers[0];
	}
	function swappyNumber2(){
		return $this->numbers[1];
	}
	function swappyNumber3(){
		return $this->numbers[2];
	}
	function appendJS(){
		// echo "TEST!";
		//makes sure numbers are set
		$this->get_numbers();
		$phoneNumbers = $this->numbers;
		// print_r($this);
		$jsTarget1 = $this->lava_options["jsTarget1"]->get_value();
		$jsTarget2 = $this->lava_options["jsTarget2"]->get_value();
		$jsTarget3 = $this->lava_options["jsTarget3"]->get_value();
	 ?>
	<script>
		jQuery(document).ready(function($){
			console.log("Test");
			$("<?php echo $jsTarget1 ?>").html("<?php echo $phoneNumbers[0]; ?>");
			$("<?php echo $jsTarget2 ?>").html("<?php echo $phoneNumbers[1]; ?>");
			$("<?php echo $jsTarget3 ?>").html("<?php echo $phoneNumbers[2]; ?>");
		});
	</script>
	<?php
	}
}
add_action("init", array(PhoneNumberSwappy::get_instance(), "swappy_header_stuff") );
add_action("wp_head", array(PhoneNumberSwappy::get_instance(), "appendJS") );
add_shortcode("swappy1", array(PhoneNumberSwappy::get_instance(), "swappyNumber1") );
add_shortcode("swappy2", array(PhoneNumberSwappy::get_instance(), "swappyNumber2") );
add_shortcode("swappy3", array(PhoneNumberSwappy::get_instance(), "swappyNumber3") );