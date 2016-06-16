<?php
/*
Plugin Name: Genesis Dynamik Image Rotator
Plugin URI: https://github.com/VR51/Genesis-Dynamik-Image-Rotator
Description: Select a bunch of background images to use in random rotation within the header of a Genesis Dynamik theme (the bit behind the logo and header widget area). Images to be used need to uploaded with the Dynamik Image Uploader. The next version of this plugin will rotate images in other theme sections too. Image rotation occurs on each page load.
Author: Lee Hodson
Author URI: https://vr51.com
Version: 1.0.0
License: GPL
*/

/**
*
* Instructions
*
* Download Genesis Dynamik Image Rotator.
* Install as you would any other WordPress plugin by going to Dashboard > Plugins > Upload
* Activate the plugin then click 'Settings' under the plugin name or go to Dashboard > Genesis > Genesis Dynamik Image Rotator.
* Select images to display as the header background in random rotation then click 'Activate Selection' to make it happen.
* The images are set to be contained within the header area. For best results choose images that are appropriately sized for the header area, do not select a header image within Dynamik Settings > Header > Background Image (use this plugn instead).
* Set the height of the header using Dynamik Settings > Header.
*
**/

/**
*
* Security First!
*
**/

if ( !function_exists( 'add_action' ) ) {
	echo "Hi there! I'm just a plugin, not much I can do when called directly.";
	exit;
}

/* Admin bits in admin only */

if ( is_admin() ) {

	/**
	*
	*	Create Action Links for Plugin List Page
	*
	**/

	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'genesis_dynamik_image_rotator_add_action_links' );

	function genesis_dynamik_image_rotator_add_action_links( $links ) {

		// Add link to settings page
		$mylinks = array(
			'<a href="' . admin_url( 'admin.php?page=genesis_dynamik_image_rotator' ) . '">Settings</a>',
			'<a href="https://paypal.me/vr51">Donate</a>'
		);
		return array_merge( $links, $mylinks );
		
	}


	/**
	*
	* Add Settings Page
	*
	**/

	add_action( 'admin_menu', 'genesis_dynamik_image_rotator_add_admin_menu', 99 );
	add_action( 'admin_init', 'genesis_dynamik_image_rotator_settings_init' );


	function genesis_dynamik_image_rotator_add_admin_menu() { 

		add_submenu_page( 'genesis', 'Genesis Dynamik Image Rotator', 'Genesis Dynamik Image Rotator', 'manage_options', 'genesis_dynamik_image_rotator', 'genesis_dynamik_image_rotator_options_page' );

	}

	function genesis_dynamik_image_rotator_settings_init() { 

		register_setting( 'pluginPage', 'genesis_dynamik_image_rotator_settings' );

		add_settings_section(
			'genesis_dynamik_image_rotator_plugin_page_section', 
			__( 'Select the header images to use. Click <strong>Activate Selection</strong> to apply changes.<hr>', 'genesis_dynamik_image_rotator' ),
			'genesis_dynamik_image_rotator_settings_section_callback', 
			'pluginPage'
		);
		
		/**
		*
		*	Confirm Dynamik is installed before we do anything else
		*
		**/
		
		if ( !function_exists('dynamik_get_stylesheet_location') ) {
		
			echo 'Please install and activate <a target="_blank" rel="nofollow" href="http://shareasale.com/r.cfm?b=398190&amp;u=438405&amp;m=29819&amp;urllink=&amp;afftrack=">Dynamik Website Builder</a> to use this plugin.';
		
		} else {
		
	/*	
		genesis_dynamik_image_rotator_register_fields( 'body' );
	*/
		genesis_dynamik_image_rotator_register_fields( 'header' );
	/*
		genesis_dynamik_image_rotator_register_fields( 'logo' );
			
		genesis_dynamik_image_rotator_register_fields( 'content' );
	*/

		}
	}


	/**
	*
	* Register the Database Table Options and Build the Admin Page Fields
	*	$arg = The place in the theme where the image is to be used (currently all header backgrounds)
	*
	**/

	function genesis_dynamik_image_rotator_register_fields( $arg ) {

		$imagePath = dynamik_get_stylesheet_location( 'path' ) . 'images/';
		$imageURL = dynamik_get_stylesheet_location( 'url' ) . 'images/';
		$thumbURL = dynamik_get_stylesheet_location( 'url' ) . 'images/adminthumbnails/';
		

		if ( isset($imagePath) ) {
		
			foreach (scandir("$imagePath") as $file) {
				$extension = pathinfo($file, PATHINFO_EXTENSION);
				
				if (preg_match("/bmp|gif|jpe?g|png|svg/i",$extension)) {
				
					$fieldType = 'checkbox';
					$fieldName = $file;
					
					list( $width, $height ) = getimagesize( $imagePath.$file );
					
					$thumbnail = '<a href="'.$imageURL.$file.'" style="width: 105px; height:105px;" target="_blank"><div style="display:block; width: 105px; height:105px; padding: 5px;background: #ffffff url('.$thumbURL.$file.') center center no-repeat; background-size:contain; border: 2px solid #cfcfcf; outline: 1px #cecece;"></div></a><small style="text-align:center;">Width: '.$width.'px<br>Height: '.$height.'px</small></td><td>';
					
					add_settings_field(
						"$fieldName",
						__( "$thumbnail $file", 'genesis_dynamik_image_rotator' ), 
						'genesis_dynamik_image_rotator_field_render',
						'pluginPage', 
						'genesis_dynamik_image_rotator_plugin_page_section',
						array (
							'fieldType' => $fieldType,
							'label_for' => $fieldName,
							'fieldName' => $fieldName,
							'imageURL' => $imageURL,
							'class' => 'genesis_dynamik_image_rotator_plugin_page'
						)
					);

				}
			}
			
		}
		
	}

	function genesis_dynamik_image_rotator_field_render( $args ) {

		$options = get_option( 'genesis_dynamik_image_rotator_settings' );
		$fieldName = $args["fieldName"];
		$fieldType = $args["fieldType"];
		$imageURL = $args["imageURL"];
		$data = $imageURL.$fieldName;
		
		switch ($fieldType) {
			case 'checkbox':
				if ( !empty($options["$data"]) ) {
					$checked = checked( $options["$data"], 1, 0 );
				}
				
				$field = "<input type='checkbox' name='genesis_dynamik_image_rotator_settings[$data]' $checked value='1'></td><td style='width:50%;'>";
				echo $field;
				break;
		}

	}

	function genesis_dynamik_image_rotator_settings_section_callback() { 

		echo __( '<h1>Images</h1>', 'genesis_dynamik_image_rotator' );

	}


	/**
	*
	*	Do the Admin Settings Page Form
	*
	**/

	function genesis_dynamik_image_rotator_options_page() { 

		?>
		<form action='<?php echo admin_url( 'options.php' ); ?>' method='post'>

			<h2>Genesis Dynamik Image Rotator</h2>

			<?php
			settings_fields( 'pluginPage' );
			do_settings_sections( 'pluginPage' );
			submit_button( __('Activate Selection', 'genesis_dynamik_image_rotator') );
			/* DEBUG TEST
			echo "<br><br>TEST<br><br>";
			$options = get_option( 'genesis_dynamik_image_rotator_settings' );
			print_r($options);
			*/
			
			?>

		</form>
		
		<?php

	}

/* End is_admin() if */
}


/* Frontend bits in frontend only */
if (!is_admin()) {

	/**
	*
	*	Frontend Bits
	*
	**/

	function genesis_dynamik_image_rotator_frontend() {

		$options = get_option( 'genesis_dynamik_image_rotator_settings' );
		$image = array_rand($options, 1);
		?>
		<style>
			.site-header {
				background-image: url(<?php echo $image; ?>);
				background-position: center center;
				background-repeat:no-repeat;
				background-size: contain;
			}
		</style>
		<?php
	}

	add_action( 'wp_head', 'genesis_dynamik_image_rotator_frontend' );
}
