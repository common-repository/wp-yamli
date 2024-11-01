<?php
/*
Plugin Name: WP Yamli
Version: 1.2
Plugin URI: http://dev.holooli.com/2009/01/22/wp-yamli
Description: WP Yamli allows blog editors and commenters to write Arabic spelling text in English or French and Yamli will convert them.
Author: Khaled Al Hourani
Author URI: http://holooli.com
*/

if( !class_exists('Yamli') ) {
	class Yamli {
		function Yamli() { //constructor
			global $wp_version;
			//ACTIONS
				#Add Settings Panel
					add_action('admin_menu', array($this, 'addPanel') );
				#Update Settings on Save
					if ( $_POST['action'] == 'yamli_update' ) {
						add_action( 'init', array($this, 'saveSettings') );
					}

				#Default settings for Yamli plugin
					add_action( 'init', array($this, 'defaultSettings') );

				#Execute Yamli script
				add_action('admin_footer', array($this, 'execute'));
				add_action('wp_footer', array($this, 'execute'));

			//VERSION CONTROL
				if ($wp_version < 2.1) {
					add_action('admin_notices', array($this, 'versionWarning'));
				}
		}

		function versionWarning() { //Show warning if plugin is installed on a WordPress lower than 2.1
			global $wp_version;

			echo "
				<div id='yamli-warning' class='updated fade-ff0000'>
					<p><strong>" . 
					__('WP Yamli is only compatible with WordPress v2.1 and up. You are currently using WordPress v', 'yamli') . 
					$wp_version . 
					"</strong></p>
				</div>";
		}


		// Add action to build an yamli page
		function addPanel() {
			//Add the Settings and User Panels
			add_options_page('Yamli', 'Yamli', 10, 'yamli', array($this, 'yamliSettings'));
		}

		// Default settings for this plugin
		function defaultSettings () {

			$default = array(
								'title'				=> '0',
								'content'			=> '0',
								'new-tag-post_tag'  => '0',
								'author'			=> '0',
								'email'				=> '0',
								'url'				=> '0',
								'comment'			=> '0',
								's'					=> '0',
								'language'			=> 'en',
								'placement'			=> 'inside'
							);

			// Set defaults if no values exist
			if ( !get_option('yamli') ) {
				add_option( 'yamli', $default );
			} else { // Set Defaults if new value does not exist
				$yamli = get_option( 'yamli' );

				// Check to see if all defaults exists in option table's record, and assign values to empty keys
				foreach ($default as $key => $val) {
					if (!$yamli[$key]) {
						$yamli[$key] = $val;
						$new = true;
					}
				}

				if ( $new ) {
					update_option('yamli', $yamli);
				}
			}

		}


		// yamli page settings
		function yamliSettings(){

			// Get options from option table
			$yamli = get_option( 'yamli' );

			// Display message if any
			if ( $_POST['notice'] ) {
				echo '<div id="message" class="updated fade"><p><strong>' . $_POST['notice'] . '.</strong></p></div>';
			}

			?>

            <div class="wrap" dir="ltr">
				<br/>
            	<h2><?php _e('Yamli Settings', 'yamli') ?></h2>

                <form method="post" action="">

                    <p><?php _e('Check the fields you would like to Yamlify.', 'yamli');?></p>
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                       			<th scope="row"><label for="name"><?php _e('New Post:', 'yamli');?></label></th>
                        		<td>
                                    <label><input type="checkbox" name="yamli_title" value="1" <?php if ($yamli['title']) echo 'checked="checked"';?> /> <?php _e('Post title', 'yamli');?></label>
                                    <label><input type="checkbox" name="yamli_content" value="1" <?php if ($yamli['content']) echo 'checked="checked"';?> /> <?php _e('Post content', 'yamli');?></label>
									<label><input type="checkbox" name="yamli_tag" value="1" <?php if ($yamli['new-tag-post_tag']) echo 'checked="checked"';?> /> <?php _e('Post tag', 'yamli');?></label>
                                </td>
                        	</tr>
                            <tr valign="top">
                       			<th scope="row"><label for="name"><?php _e('Comments:', 'yamli');?></label></th>
                        		<td>
                                    <label><input type="checkbox" name="yamli_author" value="1" <?php if( $yamli['author'] ) echo 'checked="checked"';?> /> <?php _e('Author field', 'yamli');?></label>
                                    <label><input type="checkbox" name="yamli_email" value="1" <?php if( $yamli['email'] ) echo 'checked="checked"';?> /> <?php _e('Email field', 'yamli');?></label>
                                    <label><input type="checkbox" name="yamli_url" value="1" <?php if( $yamli['url'] ) echo 'checked="checked"';?> /> <?php _e('URL field', 'yamli');?></label>
                                    <label><input type="checkbox" name="yamli_comment" value="1" <?php if( $yamli['comment'] ) echo 'checked="checked"';?> /> <?php _e('Comment field', 'yamli');?></label>
                                </td>
                        	</tr>
                            <tr valign="top">
                       			<th scope="row"><label for="name"><?php _e('Search:', 'yamli');?></label></th>
                        		<td>
                                    <label><input type="checkbox" name="yamli_s" value="1" <?php if( $yamli['s'] ) echo 'checked="checked"';?> /> <?php _e('Search field', 'yamli');?></label>
                                </td>
                        	</tr>
                        </tbody>
                    </table>

					<br/><br/>
					<p><?php _e('Interface', 'yamli');?></p>
                    <table class="form-table">
                        <tbody>
                        	<tr valign="top">
                       			<th scope="row"><label for="text"><?php _e('Interface language:', 'yamli');?></label></th>
                        		<td>
									<label><input type="radio" name="yamli_language" value="en" <?php if ($yamli['language'] == 'en') echo 'checked="checked"';?> /> <?php _e('English', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_language" value="ar" <?php if ($yamli['language'] == 'ar') echo 'checked="checked"';?> /> <?php _e('Arabic', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_language" value="fr" <?php if ($yamli['language'] == 'fr') echo 'checked="checked"';?> /> <?php _e('French', 'yamli');?></label>
                                </td>
                        	</tr>
							<tr valign="top">
                       			<th scope="row"><label for="text"><?php _e('Placement:', 'yamli');?></label></th>
                        		<td>
									<label><input type="radio" name="yamli_placement" value="inside" <?php if ($yamli['placement'] == 'inside') echo 'checked="checked"';?> /> <?php _e('inside', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_placement" value="bottomRight" <?php if ($yamli['placement'] == 'bottomRight') echo 'checked="checked"';?> /> <?php _e('bottom right', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_placement" value="bottomLeft" <?php if ($yamli['placement'] == 'bottomLeft') echo 'checked="checked"';?> /> <?php _e('bottom left', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_placement" value="topRight" <?php if ($yamli['placement'] == 'topRight') echo 'checked="checked"';?> /> <?php _e('top right', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_placement" value="topLeft" <?php if ($yamli['placement'] == 'topLeft') echo 'checked="checked"';?> /> <?php _e('top left', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_placement" value="rightTop" <?php if ($yamli['placement'] == 'rightTop') echo 'checked="checked"';?> /> <?php _e('right top', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_placement" value="leftTop" <?php if ($yamli['placement'] == 'leftTop') echo 'checked="checked"';?> /> <?php _e('left top', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_placement" value="rightBottom" <?php if ($yamli['placement'] == 'rightBottom') echo 'checked="checked"';?> /> <?php _e('right bottom', 'yamli');?></label> &nbsp;
									<label><input type="radio" name="yamli_placement" value="leftBottom" <?php if ($yamli['placement'] == 'leftBottom') echo 'checked="checked"';?> /> <?php _e('left bottom', 'yamli');?></label> &nbsp;
                                </td>
                        	</tr>
                        </tbody>
                    </table>

                    <p class="submit"><input name="Submit" value="<?php _e('Save Changes', 'yamli');?>" type="submit" />
                    <input name="action" value="yamli_update" type="hidden" />
                </form>
            </div>

           <?php

		}


		// Save the new settings of Yamli options
		function saveSettings() {

			// Get the new values from the submitted POST
			$update['title']			= $_POST['yamli_title'];
			$update['content']  		= $_POST['yamli_content'];
			$update['new-tag-post_tag'] = $_POST['yamli_tag'];
			$update['author']  			= $_POST['yamli_author'];
			$update['email']  			= $_POST['yamli_email'];
			$update['url']  			= $_POST['yamli_url'];
			$update['comment']  		= $_POST['yamli_comment'];
			$update['s']	  			= $_POST['yamli_s'];

			$update['language']	  		= $_POST['yamli_language'];
			$update['placement']		= $_POST['yamli_placement'];

			// Save the new settings to option table's record
			update_option('yamli', $update);

			// Display success message
            $_POST['notice'] = __('Settings Saved', 'yamli');

		}


		// Execute the selected options
		function execute() {
			// Get options from option table
			$yamli = get_option('yamli');

			// Read language and placement
			$placement = array_pop($yamli);
			$language  = array_pop($yamli);

			$selected_fields = array();
			// Get all the selected fields from settings page
			foreach ($yamli as $key => $value) {
				if ($value) {
					array_push($selected_fields, $key);
				}
			}

			$out = '';
			// Put the selected fields in Yamli script
			foreach ( $selected_fields as $selected_field ) {
				$out .= "Yamli.yamlify( \"$selected_field\", { settingsPlacement: \"$placement\", uiLanguage: \"$language\" } );\n";
			}

			// if the admin didn't choose any field to yamlify don't execute the script
			if (sizeof($selected_fields) > 0) {
				echo "
					<!-- YAMLI CODE START -->
					<script type=\"text/javascript\" src=\"http://api.yamli.com/js/yamli_api.js\"></script>
					<script type=\"text/javascript\">
					  if (typeof(Yamli) == \"object\" && Yamli.init( { uiLanguage: \"en\" , startMode: \"offOrUserDefault\" } ))
					  {".
						$out
					  ."}
					</script>
					<!-- YAMLI CODE END -->
				";
			}
		}

	} // End Yamli class
} // End the BIG if


# Run The Plugin! DUH :\
if ( class_exists('Yamli') ) {
	$yamli = new Yamli();
}

?>