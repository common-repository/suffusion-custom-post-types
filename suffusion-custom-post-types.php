<?php
/**
 * Plugin Name: Suffusion Custom Post Types
 * Plugin URI: http://www.aquoid.com/news/plugins/suffusion-custom-post-types/
 * Description: This plugin is an add-on to the Suffusion WordPress Theme. When Custom Post Types were introduced in WP there were no plugins to help users define the post types via a UI. So this code was included in the theme. With the changing plugin landscape this is no longer a requirement, hence with version 4.0.0 of Suffusion the functionality is being pulled from the theme and released as a plugin.
 * Version: 1.00
 * Author: Sayontan Sinha
 * Author URI: http://mynethome.net/blog
 * License: GNU General Public License (GPL), v2 (or newer)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (c) 2009 - 2010 Sayontan Sinha. All rights reserved.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

class Suffusion_Custom_Post_Types {
	var $options_page_name;
	function __construct() {
		$this->set_globals();
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
		add_action('admin_init', array(&$this, 'admin_init'));

		add_action('wp_ajax_suffusion_cpt_display_all_custom_post_types', array(&$this, 'display_all_custom_post_types'));
		add_action('wp_ajax_suffusion_cpt_display_custom_post_type', array(&$this, 'display_custom_post_type'));
		add_action('wp_ajax_suffusion_cpt_save_custom_post_type', array(&$this, 'save_custom_post_type'));

		add_action('wp_ajax_suffusion_cpt_modify_post_type_layout', array(&$this, 'modify_post_type_layout'));

		add_action('wp_ajax_suffusion_cpt_display_all_custom_taxonomies', array(&$this, 'display_all_custom_taxonomies'));
		add_action('wp_ajax_suffusion_cpt_display_custom_taxonomy', array(&$this, 'display_custom_taxonomy'));
		add_action('wp_ajax_suffusion_cpt_save_custom_taxonomy', array(&$this, 'save_custom_taxonomy'));
	}

	function admin_menu() {
		$this->options_page_name = add_theme_page('Suffusion Custom Post Types', 'Suffusion Custom Post Types', 'edit_theme_options', 'suffusion-cpt', array(&$this, 'render_options'));
	}

	function admin_enqueue_scripts($hook) {
		if (is_admin() && $hook == $this->options_page_name) {
			wp_enqueue_style('suffusion-cpt-jq-ui', plugins_url('include/css/jquery-ui-custom.css', __FILE__), array(), '1.00');
			wp_enqueue_style('suffusion-cpt-admin', plugins_url('include/css/admin.css', __FILE__), array(), '1.00');
			wp_enqueue_script('suffusion-cpt-admin', plugins_url('include/js/admin.js', __FILE__), array('jquery-ui-tabs', 'jquery-ui-draggable'), '1.00');
		}
	}

	function admin_init($hook) {
		if (is_admin() && $hook == $this->options_page_name) {
			register_setting('suffusion_post_type_options', 'suffusion_post_types');
			register_setting('suffusion_taxonomy_options', 'suffusion_taxonomies');
		}
	}

	function set_globals() {
		global $suffusion_post_type_options, $suffusion_post_type_labels, $suffusion_post_type_args, $suffusion_post_type_supports;
		$suffusion_post_type_options = array(
			array('name' => 'post_type', 'type' => 'text', 'desc' => 'Post Type (e.g. book)', 'std' => '', 'reqd' => true),
			array('name' => 'style_inherit', 'type' => 'select', 'desc' => 'Inherit styles from (for the Suffusion theme only):', 'std' => 'post',
				'options' => array('post' => 'Post - will get styles for Posts', 'page' => 'Page - will get styles for Pages', 'custom' => 'Custom - define your own styles')),
		);

		if (!isset($suffusion_post_type_labels)) {
			$suffusion_post_type_labels = array(
				array('name' => 'name', 'type' => 'text', 'desc' => 'Name (e.g. Books)', 'std' => '', 'reqd' => true),
				array('name' => 'singular_name', 'type' => 'text', 'desc' => 'Singular Name (e.g. Book)', 'std' => '', 'reqd' => true),
				array('name' => 'add_new', 'type' => 'text', 'desc' => 'Text for "Add New" (e.g. Add New)', 'std' => ''),
				array('name' => 'add_new_item', 'type' => 'text', 'desc' => 'Text for "Add New Item" (e.g. Add New Book)', 'std' => ''),
				array('name' => 'edit_item', 'type' => 'text', 'desc' => 'Text for "Edit Item" (e.g. Edit Book)', 'std' => ''),
				array('name' => 'new_item', 'type' => 'text', 'desc' => 'Text for "New Item" (e.g. New Book)', 'std' => ''),
				array('name' => 'view_item', 'type' => 'text', 'desc' => 'Text for "View Item" (e.g. View Book)', 'std' => ''),
				array('name' => 'search_items', 'type' => 'text', 'desc' => 'Text for "Search Items" (e.g. Search Books)', 'std' => ''),
				array('name' => 'not_found', 'type' => 'text', 'desc' => 'Text for "Not found" (e.g. No Books Found)', 'std' => ''),
				array('name' => 'not_found_in_trash', 'type' => 'text', 'desc' => 'Text for "Not found in Trash" (e.g. No Books Found in Trash)', 'std' => ''),
				array('name' => 'parent_item_colon', 'type' => 'text', 'desc' => 'Parent Text with a colon (e.g. Book Series:)', 'std' => ''),
			);
		}

		if (!isset($suffusion_post_type_args)) {
			$suffusion_post_type_args = array(
				array('name' => 'public', 'desc' => 'Public', 'type' => 'checkbox', 'default' => true),
				array('name' => 'publicly_queryable', 'desc' => 'Publicly Queriable', 'type' => 'checkbox', 'default' => true),
				array('name' => 'show_ui', 'desc' => 'Show UI', 'type' => 'checkbox', 'default' => true),
				array('name' => 'query_var', 'desc' => 'Query Variable', 'type' => 'checkbox', 'default' => true),
				array('name' => 'rewrite', 'desc' => 'Rewrite', 'type' => 'checkbox', 'default' => true),
				array('name' => 'hierarchical', 'desc' => 'Hierarchical', 'type' => 'checkbox', 'default' => true),
				array('name' => 'exclude_from_search', 'desc' => 'Exclude from Search', 'type' => 'checkbox', 'default' => true),
				array('name' => 'show_in_nav_menus', 'desc' => 'Show in Navigation menus', 'type' => 'checkbox', 'default' => true),
				array('name' => 'menu_position', 'desc' => 'Menu Position', 'type' => 'text', 'default' => null),
			);
		}

		if (!isset($suffusion_post_type_supports)) {
			$suffusion_post_type_supports = array(
				array('name' => 'title', 'desc' => 'Title', 'type' => 'checkbox', 'default' => false),
				array('name' => 'editor', 'desc' => 'Editor', 'type' => 'checkbox', 'default' => false),
				array('name' => 'author', 'desc' => 'Author', 'type' => 'checkbox', 'default' => false),
				array('name' => 'thumbnail', 'desc' => 'Thumbnail', 'type' => 'checkbox', 'default' => false),
				array('name' => 'excerpt', 'desc' => 'Excerpt', 'type' => 'checkbox', 'default' => false),
				array('name' => 'trackbacks', 'desc' => 'Trackbacks', 'type' => 'checkbox', 'default' => false),
				array('name' => 'custom-fields', 'desc' => 'Custom Fields', 'type' => 'checkbox', 'default' => false),
				array('name' => 'comments', 'desc' => 'Comments', 'type' => 'checkbox', 'default' => false),
				array('name' => 'revisions', 'desc' => 'Revisions', 'type' => 'checkbox', 'default' => false),
				array('name' => 'page-attributes', 'desc' => 'Page Attributes', 'type' => 'checkbox', 'default' => false),
			);
		}

		global $suffusion_taxonomy_options, $suffusion_taxonomy_labels, $suffusion_taxonomy_args;
		$suffusion_taxonomy_options = array(
			array('name' => 'taxonomy', 'type' => 'text', 'desc' => 'Taxonomy (e.g. genres)', 'std' => '', 'reqd' => true),
			array('name' => 'object_type', 'type' => 'text', 'desc' => 'Applicable to post types (comma-separated list e.g. book, movie)', 'std' => '', 'reqd' => true),
		);

		if (!isset($suffusion_taxonomy_labels)) {
			$suffusion_taxonomy_labels = array(
				array('name' => 'name', 'type' => 'text', 'desc' => 'Name (e.g. Genres)', 'std' => '', 'reqd' => true),
				array('name' => 'singular_name', 'type' => 'text', 'desc' => 'Singular Name (e.g. Genre)', 'std' => '', 'reqd' => true),
				array('name' => 'search_items', 'type' => 'text', 'desc' => 'Text for "Search Items" (e.g. Search Genres)', 'std' => ''),
				array('name' => 'popular_items', 'type' => 'text', 'desc' => 'Text for "Popular Items" (e.g. Popular Genres)', 'std' => ''),
				array('name' => 'all_items', 'type' => 'text', 'desc' => 'Text for "All Items" (e.g. All Genres)', 'std' => ''),
				array('name' => 'parent_item', 'type' => 'text', 'desc' => 'Parent Item (e.g. Parent Genre)', 'std' => ''),
				array('name' => 'parent_item_colon', 'type' => 'text', 'desc' => 'Parent Item Colon (e.g. Parent Genre:)', 'std' => ''),
				array('name' => 'edit_item', 'type' => 'text', 'desc' => 'Text for "Edit Item" (e.g. Edit Genre)', 'std' => ''),
				array('name' => 'update_item', 'type' => 'text', 'desc' => 'Text for "Update Item" (e.g. Update Genre)', 'std' => ''),
				array('name' => 'add_new_item', 'type' => 'text', 'desc' => 'Text for "Add New Item" (e.g. Add New Genre)', 'std' => ''),
				array('name' => 'new_item_name', 'type' => 'text', 'desc' => 'Text for "New Item Name" (e.g. New Genre Name)', 'std' => ''),
			);
		}

		if (!isset($suffusion_taxonomy_args)) {
			$suffusion_taxonomy_args = array(
				array('name' => 'public', 'desc' => 'Public', 'type' => 'checkbox', 'default' => true),
				array('name' => 'show_ui', 'desc' => 'Show UI', 'type' => 'checkbox', 'default' => true),
				array('name' => 'show_tagcloud', 'desc' => 'Show in Tagcloud widget', 'type' => 'checkbox', 'default' => true),
				array('name' => 'hierarchical', 'desc' => 'Hierarchical', 'type' => 'checkbox', 'default' => true),
				array('name' => 'rewrite', 'desc' => 'Rewrite', 'type' => 'checkbox', 'default' => true),
			);
		}
	}

	function save_custom_post_type() {
		global $suffusion_post_type_options, $suffusion_post_type_labels, $suffusion_post_type_args, $suffusion_post_type_supports;
		$post_type_index = $_POST['post_type_index'];
		$post_type = $_POST['suffusion_post_type'];

		check_ajax_referer('add-edit-post-type-suffusion', 'add-edit-post-type-wpnonce');
		$suffusion_post_types = get_option('suffusion_post_types');
		$valid = $this->validate_custom_type_form($post_type, array('options' => $suffusion_post_type_options, 'labels' => $suffusion_post_type_labels, 'args' => $suffusion_post_type_args, 'supports' => $suffusion_post_type_supports));
		if ($valid) {
			if ($suffusion_post_types == null || !is_array($suffusion_post_types)) {
				$suffusion_post_types = array();
			}
			if (isset($post_type_index) && $post_type_index != '' && $post_type_index != -5) {
				$suffusion_post_types[$post_type_index] = $post_type;
				$index = $post_type_index;
			}
			else {
				$suffusion_post_types[] = $post_type;
				$index = max(array_keys($suffusion_post_types));
			}

			update_option('suffusion_post_types', $suffusion_post_types);
			$this->display_custom_post_type($index, "Post Type saved successfully");
		}
		else {
			$this->display_custom_post_type(-1, "NOT SAVED: Please populate all required fields");
		}
		$this->display_all_custom_post_types();
	}

	function display_all_custom_post_types() {
		$delete = "";
		if (isset($_POST['processing_function'])) {
			$processing_function = $_POST['processing_function'];
		}
		else {
			$processing_function = "";
		}
		if ($processing_function == 'delete') {
			$delete = $this->delete_post_type();
			$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
		}
		else if ($processing_function == 'delete_all') {
			$delete = $this->delete_all_custom_post_types();
			$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
		}
		$suffusion_post_types = get_option('suffusion_post_types');
		?>
	<div class='suf-custom-post-types-section suf-section'>
		<table class="form-table">
			<tr>
				<td>
					<?php
					echo $delete;
					echo wp_nonce_field('custom_post_types_suffusion', 'custom_post_types_wpnonce', true, false);
					?>
					<p>The following post types exist. You can edit / delete any of these. Note that if you edit / delete the name of any of these, it will not delete associated posts. You can recreate these post types again and everything will be back to normal:</p>
					<input type="hidden" name="post_type_index" value="" />
					<input type="hidden" name="formaction" value="default" />

					<table class='custom-type-list'>
						<tr>
							<th>Post Type</th>
							<th>Name</th>
							<th>Singular Name</th>
							<th>Action</th>
						</tr>
						<?php
						if (is_array($suffusion_post_types)) {
							foreach ($suffusion_post_types as $id => $custom_post_type) {
								?>
								<tr>
									<td><?php echo $custom_post_type['post_type']; ?></td>
									<td><?php echo $custom_post_type['labels']['name']; ?></td>
									<td><?php echo $custom_post_type['labels']['singular_name']; ?></td>
									<td><a class='edit-post-type' id='edit-post-type-<?php echo $id; ?>' href='<?php echo site_url(); ?>/wp-admin/admin-ajax.php'>Edit</a> | <a class='delete-post-type' id='delete-post-type-<?php echo $id; ?>' href='<?php echo site_url(); ?>/wp-admin/admin-ajax.php'>Delete</a></td>
								</tr>
								<?php
							}
						}
						?>
					</table>

					<div class="suf-button-toggler fix">
						<a href='#' class='suf-button-toggler-custom-post-types'><span class='suf-button-toggler-custom-post-types'>Save / Reset</span></a>
					</div>
					<div class="suf-button-bar suf-button-bar-custom-post-types">
						<h2 class="fix"><a href='#'><img src='<?php echo plugins_url('include/images/remove.png', __FILE__); ?>' alt='Close' /></a>Custom Type Actions</h2>
						<label><input name="delete-all-custom-post-types" type="button" value="Delete All Custom Post Types" class="button delete-all-custom-post-types" /></label>
					</div><!-- suf-button-bar -->
				</td>
			</tr>
		</table>
	</div><!-- .suf-custom-post-types-section -->
	<?php
		if ($processing_function == 'delete' || $processing_function == 'delete_all') {
			$this->display_custom_post_type(-1);
		}
	}

	function modify_post_type_layout() {
		$layout_positions = array('hide' => 'Do not show', 'tleft' => 'Below title, left', 'tright' => 'Below title, right',
			'bleft' => 'Below content, left', 'bright' => 'Below content, right');
		$delete = "";
		$processing_function = $_POST['processing_function'];
		if (isset($processing_function) && $processing_function == 'delete') {
			$delete = $this->delete_post_type();
			$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
		}
		else if (isset($processing_function) && $processing_function == 'delete_all') {
			$delete = $this->delete_all_custom_post_types();
			$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
		}
		$suffusion_post_types = get_option('suffusion_post_types');
		?>
	<div class='suf-modify-post-type-layout-section suf-section'>
		<table class="form-table">
			<tr>
				<td>
					<?php echo $delete; ?>
					<p>The following post types exist. You can edit / delete any of these. Note that if you edit / delete the name of any of these, it will not delete associated posts. You can recreate these post types again and everything will be back to normal:</p>
					<input type="hidden" name="post_type_index" value="" />
					<input type="hidden" name="formaction" value="default" />

					<table class='custom-type-list'>
						<tr>
							<th>Post Type</th>
							<th>Position of Elements</th>
						</tr>
						<?php
						if (is_array($suffusion_post_types)) {
							foreach ($suffusion_post_types as $custom_post_type) {
								?>
								<tr>
									<td><?php echo $custom_post_type['post_type']; ?></td>
									<td>
										<?php
										$custom_post_type_supports = $custom_post_type['options'];
										if (in_array('author', $custom_post_type_supports)) {
											echo "Author Position: ";
										}
										?>
									</td>
									<td><?php echo $custom_post_type['labels']['singular_name']; ?></td> -->
								</tr>
								<?php
							}
						}
						?>
					</table>

					<div class="suf-button-toggler fix">
						<a href='#' class='suf-button-toggler-modify-post-type'><span class='suf-button-toggler-modify-post-type'>Save / Reset</span></a>
					</div>
					<div class="suf-button-bar suf-button-bar-modify-post-type">
						<h2 class="fix"><a href='#'><img src='<?php echo plugins_url('include/images/remove.png', __FILE__); ?>' alt='Close' /></a>Custom Type Actions</h2>
						<label><input name="save-post-type-layouts" type="button" value="Save Post Type Layouts" class="button delete-all-custom-post-types" /></label>
					</div><!-- suf-button-bar -->
				</td>
			</tr>
		</table>
	</div><!-- .suf-modify-post-type-layout-section -->
	<?php
		if ($processing_function == 'delete' || $processing_function == 'delete_all') {
			$this->display_custom_post_type(-1);
		}
	}

	function delete_post_type() {
		// For some reason a blank nonce is being fetched here even if I do $_POST['custom_post_types_wpnonce']. Weird
		check_ajax_referer('custom_post_types_suffusion', 'custom_post_types_wpnonce');
		$post_type_index = $_POST['post_type_index'];
		$ret = "";
		if (isset($post_type_index)) {
			$suffusion_post_types = get_option('suffusion_post_types');
			if (is_array($suffusion_post_types)) {
				unset($suffusion_post_types[$post_type_index]);
				update_option('suffusion_post_types', $suffusion_post_types);
				$ret = "Post type deleted.";
			}
			else {
				$ret = "Failed to delete post type. Post types are not stored as an array in the database.";
			}
		}
		return $ret;
	}

	function delete_all_custom_post_types() {
		check_ajax_referer('custom_post_types_suffusion', 'custom_post_types_wpnonce');
		$option = get_option('suffusion_post_types');
		if (isset($option) && is_array($option)) {
			$ret = delete_option('suffusion_post_types');
			if ($ret) {
				$ret = "All post types deleted.";
			}
			else {
				$ret = "Failed to delete post types.";
			}
		}
		else {
			$ret = "No post types exist!";
		}
		return $ret;
	}

	function display_custom_post_type($index = null, $msg = null) {
		global $suffusion_post_type_labels, $suffusion_post_type_args, $suffusion_post_type_supports, $suffusion_post_type_options;
		if (isset($_POST['post_type_index'])) {
			$post_type_index = $_POST['post_type_index'];
		}
		else {
			$post_type_index = -5;
		}
		$suffusion_post_types = get_option('suffusion_post_types');
		if (is_array($suffusion_post_types) && $post_type_index != '' && $post_type_index != -5) {
			$post_type = $suffusion_post_types[$post_type_index];
		}
		else if (is_array($suffusion_post_types) && ($post_type_index =='' || $post_type_index == -5) && ($index > -1)) {
			$post_type = $suffusion_post_types[$index];
		}
		else if (isset($_POST['suffusion_post_type']) && ($post_type_index =='' || $post_type_index == -5) && $index == -1) {
			$post_type = $_POST['suffusion_post_type'];
		}
		else {
			$post_type = array('labels' => $suffusion_post_type_labels, 'args' => $suffusion_post_type_args, 'supports' => $suffusion_post_type_supports);
			foreach ($post_type as $parameter_type => $parameters) {
				foreach ($parameters as $parameter => $parameter_value) {
					$post_type[$parameter_type][$parameter] = FALSE;
				}
			}
		}

		$msg = $msg == null ? null : "<div id='message' class='updated fade'><p><strong>$msg</strong></p></div>";
		?>
	<div class='suf-post-type-edit-section suf-section'>
		<table class="form-table">
			<tr>
				<td>
					<?php
					echo $msg;
					echo wp_nonce_field('add-edit-post-type-suffusion', 'add-edit-post-type-wpnonce', true, false);
					?>
					<input type='hidden' name='post_type_index' id='post_type_index' value="<?php echo $post_type_index; ?>"/>
					<table>
						<?php
						foreach ($suffusion_post_type_options as $option) {
							?>
							<tr>
								<?php $this->process_custom_type_option($option, null, $post_type, 'suffusion_post_type'); ?>
							</tr>
							<?php
						}
						?>
					</table>

					<table class="custom-type-table">
						<tr>
							<col class='half-width' />
							<col/>
						</tr>
						<tr valign='top'>
							<th scope='row'>Display information</th>
							<th scope='row'>Arguments</th>
						</tr>
						<tr>
							<td rowspan='2'>
								<table>
									<?php foreach ($suffusion_post_type_labels as $label) { ?>
									<tr>
										<?php $this->process_custom_type_option($label, 'labels', $post_type, 'suffusion_post_type'); ?>
									</tr>
									<?php } ?>
								</table>
							</td>

							<td>
								<table>
									<?php foreach ($suffusion_post_type_args as $arg) { ?>
									<tr>
										<?php $this->process_custom_type_option($arg, 'args', $post_type, 'suffusion_post_type'); ?>
									</tr>
									<?php } ?>
								</table>
							</td>
						</tr>

						<tr>
							<td>
								<table width='100%'>
									<tr>
										<th>Supports</th>
									</tr>

									<tr>
										<td>
											<table>
												<?php foreach ($suffusion_post_type_supports as $support) { ?>
												<tr>
													<?php $this->process_custom_type_option($support, 'supports', $post_type, 'suffusion_post_type'); ?>
												</tr>
												<?php } ?>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>

					<div class="suf-button-toggler fix">
						<a href='#' class='suf-button-toggler-save-edit-post-type'><span class='suf-button-toggler-save-edit-post-type'>Save / Reset</span></a>
					</div>
					<div class="suf-button-bar suf-button-bar-save-edit-post-type">
						<h2 class="fix"><a href='#'><img src='<?php echo plugins_url('include/images/remove.png', __FILE__); ?>' alt='Close' /></a>Custom Type Actions</h2>
						<label><input name="save-post-type-edit" type="button" value="Save changes" class="button save-post-type-edit" /></label>
						<label><input name="reset-post-type-edit" type="button" value="Clear all fields" class="button reset-post-type-edit" /></label>
						<input type="hidden" name="formaction" value="default" />
						<input type="hidden" name="formcategory" value="add-edit-post-type" />
					</div><!-- suf-button-bar -->
				</td>
			</tr>
		</table>
	</div><!-- suf-post-type-edit-section -->
	<?php
	}

	function save_custom_taxonomy() {
		global $suffusion_taxonomy_options, $suffusion_taxonomy_labels, $suffusion_taxonomy_args;
		$taxonomy_index = $_POST['taxonomy_index'];
		$taxonomy = $_POST['suffusion_taxonomy'];
		$valid = $this->validate_custom_type_form($taxonomy, array('options' => $suffusion_taxonomy_options, 'labels' => $suffusion_taxonomy_labels, 'args' => $suffusion_taxonomy_args));
		if ($valid) {
			$suffusion_taxonomies = get_option('suffusion_taxonomies');
			if ($suffusion_taxonomies == null || !is_array($suffusion_taxonomies)) {
				$suffusion_taxonomies = array();
			}
			if (isset($taxonomy_index) && $taxonomy_index != '' && $taxonomy_index != -5) {
				$suffusion_taxonomies[$taxonomy_index] = $taxonomy;
				$index = $taxonomy_index;
			}
			else {
				$suffusion_taxonomies[] = $taxonomy;
				$index = max(array_keys($suffusion_taxonomies));
			}

			update_option('suffusion_taxonomies', $suffusion_taxonomies);
			$this->display_custom_taxonomy($index, "Taxonomy saved successfully");
		}
		else {
			$this->display_custom_taxonomy(-1, "NOT SAVED: Please populate all required fields");
		}
		$this->display_all_custom_taxonomies();
	}

	function display_all_custom_taxonomies() {
		$delete = "";
		if (isset($_POST['processing_function'])) {
			$processing_function = $_POST['processing_function'];
		}
		else {
			$processing_function = "";
		}
		if ($processing_function == 'delete') {
			$delete = $this->delete_taxonomy();
			$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
		}
		else if ($processing_function == 'delete_all') {
			$delete = $this->delete_all_custom_taxonomies();
			$delete = $delete == "" ? null : "<div id='message' class='updated fade'><p><strong>$delete</strong></p></div>";
		}
		$suffusion_taxonomies = get_option('suffusion_taxonomies');
		?>
	<div class='suf-custom-taxonomies-section suf-section'>
		<table class="form-table">
			<tr>
				<td>
					<?php
					echo $delete;
					echo wp_nonce_field('custom-taxonomies-suffusion', 'custom-taxonomies-wpnonce', true, false);
					?>
					<p>The following taxonomies exist. You can edit / delete any of these. Note that if you edit / delete the name of any of these, it will not delete associated posts. You can recreate these taxonomies again and everything will be back to normal:</p>
					<input type="hidden" name="taxonomy_index" value="" />
					<input type="hidden" name="formaction" value="default" />

					<table class='custom-type-list'>
						<tr>
							<th>Taxonomy</th>
							<th>Object Type</th>
							<th>Name</th>
							<th>Singular Name</th>
							<th>Action</th>
						</tr>
						<?php
						if (is_array($suffusion_taxonomies)) {
							foreach ($suffusion_taxonomies as $id => $custom_taxonomy) {
								?>
								<tr>
									<td><?php echo $custom_taxonomy['taxonomy']; ?></td>
									<td>
										<?php
											$object_type = $custom_taxonomy['object_type'];
											$object_type = explode(',', $object_type);
											if (is_array($object_type)) {
												foreach ($object_type as $type) {
													echo trim($type)."<br/>";
												}
											}
										?>
									</td>
									<td><?php echo $custom_taxonomy['labels']['name']; ?></td>
									<td><?php echo $custom_taxonomy['labels']['singular_name']; ?></td>
									<td><a class='edit-taxonomy' id='edit-taxonomy-<?php echo $id; ?>' href='<?php echo site_url(); ?>/wp-admin/admin-ajax.php'>Edit</a> | <a class='delete-taxonomy' id='delete-taxonomy-<?php echo $id; ?>' href='<?php echo site_url(); ?>/wp-admin/admin-ajax.php'>Delete</a></td>
								</tr>
								<?php
							}
						}
						?>
					</table>

					<div class="suf-button-toggler fix">
						<a href='#' class='suf-button-toggler-view-taxonomies'><span class='suf-button-toggler-view-taxonomies'>Save / Reset</span></a>
					</div>
					<div class="suf-button-bar suf-button-bar-view-taxonomies">
						<h2 class='fix'><a href='#'><img src='<?php echo plugins_url('include/images/remove.png', __FILE__); ?>' alt='Close' /></a>Custom Type Actions</h2>
						<label><input name="delete-all-custom-taxonomies" type="button" value="Delete All Custom Taxonomies" class="button delete-all-custom-taxonomies" /></label>
					</div><!-- suf-button-bar -->
				</td>
			</tr>
		</table>
	</div><!-- .suf-custom-taxonomies-section -->
	<?php
		if ($processing_function == 'delete' || $processing_function == 'delete_all') {
			$this->display_custom_taxonomy(-1);
		}
	}

	function display_custom_taxonomy($index = null, $msg = null) {
		global $suffusion_taxonomy_labels, $suffusion_taxonomy_args, $suffusion_taxonomy_options;
		if (isset($_POST['taxonomy_index'])) {
			$taxonomy_index = $_POST['taxonomy_index'];
		}
		else {
			$taxonomy_index = -5;
		}
		$suffusion_taxonomies = get_option('suffusion_taxonomies');
		if (is_array($suffusion_taxonomies) && $taxonomy_index != '' && $taxonomy_index != -5) {
			$taxonomy = $suffusion_taxonomies[$taxonomy_index];
		}
		else if (is_array($suffusion_taxonomies) && ($taxonomy_index =='' || $taxonomy_index == -5) && ($index > -1)) {
			$taxonomy = $suffusion_taxonomies[$index];
		}
		else if (isset($_POST['suffusion_taxonomy']) && ($taxonomy_index =='' || $taxonomy_index == -5) && $index == -1) {
			$taxonomy = $_POST['suffusion_taxonomy'];
		}
		else {
			$taxonomy = array('labels' => $suffusion_taxonomy_labels, 'args' => $suffusion_taxonomy_args);
			foreach ($taxonomy as $parameter_type => $parameters) {
				foreach ($parameters as $parameter => $parameter_value) {
					$taxonomy[$parameter_type][$parameter] = FALSE;
				}
			}
		}

		$msg = $msg == null ? null : "<div id='message' class='updated fade'><p><strong>$msg</strong></p></div>";
		?>
	<div class='suf-taxonomy-edit-section suf-section'>
		<table class="form-table">
			<tr>
				<td>
					<?php
					echo $msg;
					echo wp_nonce_field('add-edit-taxonomy-suffusion', 'add-edit-taxonomy-wpnonce', true, false);
					?>
					<input type='hidden' name='taxonomy_index' id='taxonomy_index' value="<?php echo $taxonomy_index; ?>"/>
					<table>
						<?php
						foreach ($suffusion_taxonomy_options as $option) {
							?>
							<tr>
								<?php $this->process_custom_type_option($option, null, $taxonomy, 'suffusion_taxonomy'); ?>
							</tr>
							<?php
						}
						?>
					</table>

					<table class="custom-type-table">
						<tr>
							<col class='half-width' />
							<col/>
						</tr>
						<tr valign='top'>
							<th scope='row'>Display information</th>
							<th scope='row'>Arguments</th>
						</tr>
						<tr>
							<td>
								<table>
									<?php foreach ($suffusion_taxonomy_labels as $label) { ?>
									<tr>
										<?php $this->process_custom_type_option($label, 'labels', $taxonomy, 'suffusion_taxonomy'); ?>
									</tr>
									<?php } ?>
								</table>
							</td>

							<td>
								<table>
									<?php foreach ($suffusion_taxonomy_args as $arg) { ?>
									<tr>
										<?php $this->process_custom_type_option($arg, 'args', $taxonomy, 'suffusion_taxonomy'); ?>
									</tr>
									<?php } ?>
								</table>
							</td>
						</tr>
					</table>

					<div class="suf-button-toggler fix">
						<a href='#' class='suf-button-toggler-view-edit-taxonomies'><span class='suf-button-toggler-view-edit-taxonomies'>Save / Reset</span></a>
					</div>
					<div class="suf-button-bar suf-button-bar-view-edit-taxonomies">
						<h2>Custom Type Actions</h2>
						<label><input name="save-taxonomy-edit" type="button" value="Save changes" class="button save-taxonomy-edit" /></label>
						<label><input name="reset-taxonomy-edit" type="button" value="Clear all fields" class="button reset-taxonomy-edit" /></label>
						<input type="hidden" name="formaction" value="default" />
						<input type="hidden" name="formcategory" value="add-edit-taxonomy" />
					</div><!-- suf-button-bar -->
				</td>
			</tr>
		</table>
	</div><!-- suf-taxonomy-edit-section -->
	<?php
	}

	function delete_taxonomy() {
		$taxonomy_index = $_POST['taxonomy_index'];
		$ret = "";
		if (isset($taxonomy_index)) {
			$suffusion_taxonomies = get_option('suffusion_taxonomies');
			if (is_array($suffusion_taxonomies)) {
				unset($suffusion_taxonomies[$taxonomy_index]);
				update_option('suffusion_taxonomies', $suffusion_taxonomies);
				$ret = "Taxonomy deleted.";
			}
			else {
				$ret = "Failed to delete taxonomy. Taxonomies are not stored as an array in the database.";
			}
		}
		return $ret;
	}

	function delete_all_custom_taxonomies() {
		$option = get_option('suffusion_taxonomies');
		if (isset($option) && is_array($option)) {
			$ret = delete_option('suffusion_taxonomies');
			if ($ret) {
				$ret = "All taxonomies deleted.";
			}
			else {
				$ret = "Failed to delete taxonomies.";
			}
		}
		else {
			$ret = "No taxonomies exist!";
		}
		return $ret;
	}

	function validate_custom_type_form($custom_type, $validation_options) {
		foreach ($validation_options as $option_type => $option_set) {
			if ($option_type == 'options') {
				$to_validate = $custom_type;
			}
			else {
				if (isset($custom_type[$option_type])) {
					$to_validate = $custom_type[$option_type];
				}
			}
			foreach ($option_set as $option) {
				if (isset($option['reqd'])) {
					if (isset($to_validate[$option['name']]) && trim($to_validate[$option['name']]) == '') {
						return false;
					}
				}
			}
		}
		return true;
	}

	function process_custom_type_option($option, $section, $custom_type, $custom_type_name) {
		if (is_array($option)) {
			$required = "";
			if (isset($option['reqd'])) {
				$required = " <span class='note'>[Required *]</span> ";
			}
			switch ($option['type']) {
				case 'text':
					echo "<td>".$option['desc'].$required."</td>";
					if ($section != null) {
						if (isset($option['name']) && isset($custom_type[$section][$option['name']])) {
							echo "<td><input name='{$custom_type_name}[$section][".$option['name']."]' type='text' value=\"".$custom_type[$section][$option['name']]."\"/></td>";
						}
						else {
							echo "<td><input name='{$custom_type_name}[$section][".$option['name']."]' type='text' value=\"\"/></td>";
						}
					}
					else {
						if (isset($option['name']) && isset($custom_type[$option['name']])) {
							echo "<td><input name='{$custom_type_name}[".$option['name']."]' type='text' value=\"".$custom_type[$option['name']]."\"/></td>";
						}
						else {
							echo "<td><input name='{$custom_type_name}[".$option['name']."]' type='text' value=\"\"/></td>";
						}
					}
					break;

				case 'checkbox':
	?>
			<td colspan='2'>
			<?php
					if ($section != null) {
			?>
				<input name='<?php echo $custom_type_name; ?>[<?php echo $section; ?>][<?php echo $option['name'];?>]' type='checkbox' value='1' <?php if (isset($custom_type[$section][$option['name']])) checked('1', $custom_type[$section][$option['name']]); ?> />
			<?php
					}
					else {
			?>
				<input name='<?php echo $custom_type_name; ?>[<?php echo $option['name'];?>]' type='checkbox' value='1' <?php if (isset($custom_type[$option['name']])) checked('1', $custom_type[$option['name']]); ?> />
			<?php
					}
			?>
				&nbsp;&nbsp;<?php echo $option['desc'].$required;?>
			</td>
	<?php
			        break;

				case 'select':
	?>
			<td><?php echo $option['desc'].$required;?></td>
			<td>
			<?php
					if ($section != null) {
						if (!isset($custom_type[$section][$option['name']]) || $custom_type[$section][$option['name']] == null) {
							$value = $option['std'];
						}
						else {
							$value = $custom_type[$section][$option['name']];
						}
			?>
				<select name='<?php echo $custom_type_name; ?>[<?php echo $section; ?>][<?php echo $option['name'];?>]' >
			<?php
						foreach ($option['options'] as $dd_value => $dd_display) {
			?>
					<option value='<?php echo $dd_value;?>' <?php selected($value, $dd_value); ?> ><?php echo $dd_display; ?></option>
			<?php
						}
			?>

				</select>
			<?php
					}
					else {
						if (!isset($custom_type[$option['name']]) || $custom_type[$option['name']] == null) {
							$value = $option['std'];
						}
						else {
							$value = $custom_type[$option['name']];
						}
			?>
				<select name='<?php echo $custom_type_name; ?>[<?php echo $option['name'];?>]' >
			<?php
						foreach ($option['options'] as $dd_value => $dd_display) {
			?>
					<option value='<?php echo $dd_value;?>' <?php selected($value, $dd_value); ?>><?php echo $dd_display; ?></option>
			<?php
						}
			?>

				</select>
			<?php
					}
			?>
			</td>
	<?php
			        break;
			}
		}
	}

	/**
	 * Creates a page for custom post types. This is treated differently from the rest of the options, as these are special cases.
	 *
	 * @return void
	 */
	function render_options() {
		?>
		<div class='suf-options suf-options-$group suf-custom-type-settings' id='suf-options'>
			<div class='suf-options-page-header fix'>
				<h1>Custom Types for Suffusion</h1>
				<p style='clear: both; '>This plugin is an add-on to the Suffusion WordPress Theme. When Custom Post Types were introduced in WP there were no plugins to help users define the post types via a UI. So this code was included in the theme. With the changing plugin landscape this is no longer a requirement, hence with version 4.0.0 of Suffusion the functionality is being pulled from the theme and released as a plugin.</p>
			</div><!-- suf-options-page-header -->

			<div class="suf-loader"><img src='<?php echo plugins_url('include/images/ajax-loader-large.gif', __FILE__); ?>' alt='Processing'></div>
				<ul class='suf-section-tabs'>
					<li><a href="#custom-post-types">Existing Post Types</a></li>
					<li><a href="#add-edit-post-type">Add / Edit Post Type</a></li>
					<li><a href="#custom-taxonomies">Existing Taxonomies</a></li>
					<li><a href="#add-edit-taxonomy">Add / Edit Taxonomy</a></li>
				</ul>

				<div class='custom-post-types suffusion-options-panel' id='custom-post-types'>
					<h3 class='suf-header-2'>Existing Post Types</h3>
					<form method="post" name="form-custom-post-types" id="form-custom-post-types" action="options.php">
					<?php
						$this->display_all_custom_post_types();
					?>
					</form>
				</div><!-- .custom-post-types -->

				<div class='add-edit-post-type suffusion-options-panel' id='add-edit-post-type'>
					<h3 class='suf-header-2'>Add / Edit Post Type</h3>
					<form method="post" name="form-add-edit-post-type" id="form-add-edit-post-type" action="options.php">
					<?php
						$this->display_custom_post_type(-1);
					?>
					</form>

				</div><!-- .add-edit-post-type -->

				<div class='custom-taxonomies suffusion-options-panel' id='custom-taxonomies'>
					<h3 class='suf-header-2'>Existing Taxonomies</h3>
					<form method="post" name="form-custom-taxonomies" id="form-custom-taxonomies" action="options.php">
					<?php
						$this->display_all_custom_taxonomies();
					?>
					</form>
				</div><!-- .custom-taxonomies -->

				<div class='add-edit-taxonomy suffusion-options-panel' id='add-edit-taxonomy'>
					<h3 class='suf-header-2'>Add / Edit Taxonomy</h3>
					<form method="post" name="form-add-edit-taxonomy" id="form-add-edit-taxonomy" action="options.php">
					<?php
						$this->display_custom_taxonomy(-1);
					?>
					</form>
				</div><!-- .add-edit-taxonomies -->
			</div><!-- .suf-options-post-types -->
	<?php
	}
}

add_action('init', 'init_suffusion_custom_post_types');
function init_suffusion_custom_post_types() {
	global $Suffusion_Custom_Post_Types, $suffusion_post_types, $suffusion_taxonomies;
	global $suffusion_post_type_labels, $suffusion_post_type_args, $suffusion_post_type_supports;
	$Suffusion_Custom_Post_Types = new Suffusion_Custom_Post_Types();

	$suffusion_post_types = get_option('suffusion_post_types');
	$suffusion_taxonomies = get_option('suffusion_taxonomies');

	if (isset($suffusion_post_types) && is_array($suffusion_post_types)) {
		foreach ($suffusion_post_types as $post_type) {
			$args = array();
			$labels = array();
			$supports = array();
			foreach ($suffusion_post_type_labels as $label) {
				if (isset($post_type['labels'][$label['name']]) && $post_type['labels'][$label['name']] != '') {
					$labels[$label['name']] = $post_type['labels'][$label['name']];
				}
			}
			foreach ($suffusion_post_type_supports as $support) {
				if (isset($post_type['supports'][$support['name']])) {
					if ($post_type['supports'][$support['name']] == '1') {
						$supports[] = $support['name'];
					}
				}
			}
			foreach ($suffusion_post_type_args as $arg) {
				if (isset($post_type['args'][$arg['name']])) {
					if ($arg['type'] == 'checkbox' && $post_type['args'][$arg['name']] == '1') {
						$args[$arg['name']] = true;
					}
					else if ($arg['type'] != 'checkbox') {
						$args[$arg['name']] = $post_type['args'][$arg['name']];
					}
				}
			}
			$args['labels'] = $labels;
			$args['supports'] = $supports;
			register_post_type($post_type['post_type'], $args);
		}
	}

	global $suffusion_taxonomy_labels, $suffusion_taxonomy_args;
	if (isset($suffusion_taxonomies) && is_array($suffusion_taxonomies)) {
		foreach ($suffusion_taxonomies as $taxonomy) {
			$labels = array();
			$args = array();
			foreach ($suffusion_taxonomy_labels as $label) {
				if (isset($taxonomy['labels'][$label['name']]) && $taxonomy['labels'][$label['name']] != '') {
					$labels[$label['name']] = $taxonomy['labels'][$label['name']];
				}
			}
			foreach ($suffusion_taxonomy_args as $arg) {
				if (isset($taxonomy['args'][$arg['name']])) {
					if ($arg['type'] == 'checkbox' && $taxonomy['args'][$arg['name']] == '1') {
						$args[$arg['name']] = true;
					}
					else if ($arg['type'] != 'checkbox') {
						$args[$arg['name']] = $taxonomy['args'][$arg['name']];
					}
				}
			}
			$args['labels'] = $labels;
			$object_type_str = $taxonomy['object_type'];
			$object_type_array = explode(',',$object_type_str);
			$object_types = array();
			foreach ($object_type_array as $object_type) {
				if (post_type_exists(trim($object_type))) {
					$object_types[] = trim($object_type);
				}
			}
			register_taxonomy($taxonomy['taxonomy'], $object_types, $args);
		}
	}
}
