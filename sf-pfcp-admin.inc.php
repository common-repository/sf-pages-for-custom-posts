<?php

/* Language support */
function sf_pfcp_lang_init() {
	load_plugin_textdomain( SF_PFCP_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}


/* Settings for the administration page */
function sf_pfcp_register_settings() {
	register_setting( 'sf-pfcp-settings', 'sf_pfcp_options', 'sf_pfcp_satanize' );
}


/* When unistall the plugin */
function sf_pfcp_uninstaller() {
	delete_option( 'sf_pfcp_options' );
}


/* Menu item */
function sf_pfcp_menu() {
	add_submenu_page( 'options-general.php', SF_PFCP_PLUGIN_NAME, __("Pages for C. Posts", SF_PFCP_DOMAIN), 'manage_options', 'sf_pfcp_config', 'sf_pfcp_settings_page' );
}


/* Settings page */
function sf_pfcp_settings_page() {
	global $wpdb;
	$post_types = get_post_types( array('_builtin' => false) );
	$options = get_option( 'sf_pfcp_options' );
	// @var $options = array( 'page_id' => array( 'post_type', 'posts_per_page' ) )
	$pfcp_ops = array();
	if(!empty($options) && count($options)) {
		foreach($options as $page_id => $ops) {
			$post_type = $ops['post_type'];
			unset($ops['page_id']);
			$pfcp_ops[$post_type]['page_id'] = $page_id;
			foreach($ops as $k => $op) {
				$pfcp_ops[$post_type][$k] = $op;
			}
		}
	}
	// @var $pfcp_ops = array( 'post_type' => array( 'page_id', 'posts_per_page' ) )
?>
<div class="wrap" id="sf-pfcp">

	<form name="sf_pfcp" method="post" action="options.php">
		<?php screen_icon(); ?>
		<h2><?php echo esc_html( SF_PFCP_PLUGIN_NAME ); ?></h2>
		<?php settings_fields( 'sf-pfcp-settings' ); ?>

		<?php if (count($post_types) && !empty($post_types)) { ?>
		<table class="form-table">
			<?php foreach($post_types as $i => $type) {
				$post_type = get_post_type_object( $type );
				if (!post_type_exists($post_type->name) || !$post_type->public) { continue; }
				?>
				<tr valign="top">
					<th scope="row">
						<span style="display:inline-block;width:28px;height:28px;position:relative;top:-6px;background:<?php echo((isset($post_type->menu_icon) && $post_type->menu_icon) ? 'url('.$post_type->menu_icon.') 6px 6px' : 'url('.admin_url('images/menu.png').') -271px -33px'); ?> no-repeat;">&nbsp;</span> 
						<?php echo $post_type->label; ?>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo $post_type->label; ?></span></legend>
							<p><label for="sf_pfcp_options[<?php echo $post_type->name; ?>][page_id]">
								<?php printf( __('%1$s page: %2$s', SF_PFCP_DOMAIN), $post_type->label, wp_dropdown_pages( array( 'name' => 'sf_pfcp_options['.$post_type->name.'][page_id]', 'echo' => 0, 'show_option_none' => __("&mdash; Select &mdash;"), 'option_none_value' => '0', 'selected' => (isset($pfcp_ops[$post_type->name]['page_id']) ? $pfcp_ops[$post_type->name]['page_id'] : 0) ) ) ); ?>
							</label></p>
							<p><label for="sf_pfcp_options[<?php echo $post_type->name; ?>][posts_per_page]">
								<?php printf( __("%s per page: ", SF_PFCP_DOMAIN), $post_type->label ); ?>
								<input name="sf_pfcp_options[<?php echo $post_type->name; ?>][posts_per_page]" type="text" id="sf_pfcp_options[<?php echo $post_type->name; ?>][posts_per_page]" value="<?php echo (isset($pfcp_ops[$post_type->name]['posts_per_page']) ? $pfcp_ops[$post_type->name]['posts_per_page'] : 0); ?>" class="small-text" />
							</label></p>
							<?php $templs = array('is_single' => __("Single Post", SF_PFCP_DOMAIN), 'is_page' => __("Page", SF_PFCP_DOMAIN), 'is_archive' => __("Archive Page", SF_PFCP_DOMAIN), 'is_category' => __("Category Page", SF_PFCP_DOMAIN), 'is_tag' => __("Tag Page", SF_PFCP_DOMAIN), 'is_tax' => __("Taxonomy Page", SF_PFCP_DOMAIN), 'is_search' => __("Search Page", SF_PFCP_DOMAIN), 'is_home' => __("Home Page (Posts Page)", SF_PFCP_DOMAIN), 'is_attachment' => __("Attachment Page", SF_PFCP_DOMAIN), 'is_post_type_archive' => __("Post Type Archive", SF_PFCP_DOMAIN)); ?>
							<p><label for="sf_pfcp_options[<?php echo $post_type->name; ?>][template]">
								<?php _e("Template: ", SF_PFCP_DOMAIN); ?>
								<select id="sf_pfcp_options[<?php echo $post_type->name; ?>][template]" name="sf_pfcp_options[<?php echo $post_type->name; ?>][template]">
									<option value="0"><?php _e("&mdash; Select &mdash;"); ?></option>
									<?php foreach ($templs as $templ => $name) {
										echo '<option class="level-0" value="'.$templ.'"'.( (isset($pfcp_ops[$post_type->name]['template']) && $pfcp_ops[$post_type->name]['template'] == $templ) ? ' selected="selected"' : '' ).'>'.$name.'</option>';
									} ?>
								</select>
							</label></p>

							<?php
							$this_type_sts = (isset($pfcp_ops[$post_type->name]) ? $pfcp_ops[$post_type->name] : array());
							// @var $post_type Object : post type object
							// @var $this_type_ops Array : saved settings for this post type
							do_action('add_fields_to_pfcp', $post_type, $this_type_sts); ?>
						</fieldset>
					</td>
				</tr>
				<?php
			} ?>
		</table>
		<?php } ?>

		<p class="help"><?php _e("Need some help? Check out the &laquo;Help&raquo; toggle at the top right of your screen.", SF_PFCP_DOMAIN); ?></p>

		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="<?php _e("Save Changes"); ?>" />
		</p>
	</form>
</div>
<?php
}


/* Satanize function and organize the array for easier manipulation in front-end */
function sf_pfcp_satanize($options) {
	// @var $options = array( 'post_type' => array( 'page_id', 'posts_per_page' ) )
	$pfcp_sops = array();
	foreach($options as $post_type => $ops) {
		if ($ops['page_id']) {
			$page_id = esc_attr($ops['page_id']);
			unset($ops['page_id']);
			$pfcp_sops[$page_id]['post_type'] = esc_attr($post_type);
			foreach($ops as $k => $op) {
				$pfcp_sops[$page_id][$k] = esc_attr($op);
			}
		}
	}
	// @var $pfcp_sops = array( 'page_id' => array( 'post_type', 'posts_per_page' ) )
	return $pfcp_sops;
}


/* Contextual help for the Settings page */
function sf_pfcp_contextual_help($help, $screen_id, $screen) {
	if ($screen_id == 'settings_page_sf_pfcp_config'){
		$nhelp  = '<h5>'.__("Post type page:", SF_PFCP_DOMAIN).'</h5>';
		$nhelp .= '<p>'. __("Choose witch page will display the custom post type. Don't choose the same page for two post types, it won't work.", SF_PFCP_DOMAIN).'</p>';
		$nhelp .= '<h5>'.__("Items per page:", SF_PFCP_DOMAIN).'</h5>';
		$nhelp .= '<p>'. sprintf(__('Empty or &laquo;0&raquo; means &laquo;same as default&raquo; (default value is set in Settings > %1$sReading%2$s), &laquo;-1&raquo; means &laquo;display all in the same page&raquo;.', SF_PFCP_DOMAIN), '<a href="'.admin_url('options-reading.php').'">', '</a>').'</p>';
		$nhelp .= '<h5>'.__("Template: ", SF_PFCP_DOMAIN).'</h5>';
		$nhelp .= '<p>'. __("Choose how to display your listing page. In most cases, keep it empty and the general template (index.php) will be used. If you have a specific template for your front page or home page (front-page.php, home.php), you can choose &laquo;Home Page&raquo;, especially if you actually display this page as your website's front page.", SF_PFCP_DOMAIN).'</p>';
		$nhelp .= '<i class=\'alignright\'>'.sprintf(__('Not enough help here? Grab some on %1$smy blog%2$s [french].', SF_PFCP_DOMAIN), '<a title=\'Screenfeed\' target=\'_blank\' href=\'http://scri.in/pfcp\'>', '</a>').'</i>';
		$nhelp .= $help;
	}
	return $nhelp;
}


/* Hook example, adds an order text field */
if (!function_exists('sf_pfcp_new_fields')) {
	function sf_pfcp_new_fields($post_type, $settings) {
		// @var $post_type Object : post type object
		// @var $settings Array : saved settings for this post type ?>
		<p><label for="sf_pfcp_options[<?php echo $post_type->name; ?>][order]">
			<?php _e("Order: "); ?>
			<input name="sf_pfcp_options[<?php echo $post_type->name; ?>][order]" type="text" id="sf_pfcp_options[<?php echo $post_type->name; ?>][order]" value="<?php echo (isset($settings['order']) ? $settings['order'] : 'desc'); ?>" class="small-text" />
			<?php _e("desc or asc (default is desc)"); ?>
		</label></p>
	<?php }
	// Uncomment the following line to see what it does
	//add_action('add_fields_to_pfcp', 'sf_pfcp_new_fields', 10, 2);
}


?>