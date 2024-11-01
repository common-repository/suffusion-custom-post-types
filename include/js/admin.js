/**
 * This file contains all the JS required by the Suffusion options panel to run.
 */

// Suffusion Additions Begin -->
$j = jQuery.noConflict();

$j(document).ready(function() {
	var $tabs = $j("#suf-options").tabs({
		fx: {
			opacity: "toggle",
			duration: "fast"
		}
	});

	$j(".suf-tabbed-options .fade").fadeOut(20000);

	$j('div.suf-checklist input[type="checkbox"]').change(function() {
		var thisClass = (this.className);
		thisClass = thisClass.substring(thisClass.indexOf(" ") + 1);
		thisClass = thisClass.substring(21);

		var all_checked = [];
		$j('#' + thisClass + '-chk :checked').each(function() {
			var thisChild = this.name;
			thisChild = thisChild.substring(thisClass.length + 1);
			all_checked.push(thisChild);
		});
		var joined = all_checked.join(',');
		$j('#' + thisClass).val(joined);
	});

	$j('input.suf-multi-select-button').live('click', function() {
		var thisAction = this.className.substring(0, this.className.indexOf(" "));
		var thisName = this.name.substring(0, this.name.indexOf("-"));
		if (thisAction == 'button-all') {
			$j('input[type="checkbox"].suf-options-checkbox-' + thisName).attr('checked', true);
		}
		else if (thisAction == 'button-none') {
			$j('input[type="checkbox"].suf-options-checkbox-' + thisName).attr('checked', false);
		}

		var all_checked = [];
		$j('#' + thisName + '-chk :checked').each(function() {
			var thisChild = this.name;
			thisChild = thisChild.substring(thisName.length + 1);
			all_checked.push(thisChild);
		});
		var joined = all_checked.join(',');
		$j('#' + thisName).val(joined);
	});

	$j('.suf-button-bar').draggable();

	$j('.suf-button-toggler a').live('click', function() {
		var thisClass = this.className;
		thisClass = thisClass.substr(19);
		var dialogClass = '.suf-button-bar-' + thisClass;
		$j(dialogClass).slideToggle();
		return false;
	});

	$j('.suf-button-bar a').click(function() {
		var thisParent = $j(this).parent().parent();
		thisParent.slideToggle();
		return false;
	});

	$j(".suffusion-options-form :input[type='submit']").click(function() {
		//This is needed, otherwise the event handler cannot figure out which button was clicked.
		suffusion_submit_button = $j(this);
	});

	$j("#suf-options h3").each(function() {
		var text = $j(this).text();
		if (text == '') {
			$j(this).remove();
		}
	});

	$j('.suffusion-options-form').submit(function(event) {
		var field = suffusion_submit_button;
		var value = field.val();

		if (value == 'Migrate from 3.0.2 or lower') {
			if (!confirm("If you are NOT migrating from 3.0.2 or lower, this can wipe out all your settings! Are you sure you want to do this? This process is not reversible.")) {
				return false;
			}
		}
		else if (value == 'Migrate from 3.4.3 or lower') {
			if (!confirm("If you are NOT migrating from 3.4.3 or lower, this can wipe out all your settings! Are you sure you want to do this? This process is not reversible.")) {
				return false;
			}
		}
		else if (value.substring(0, 5) == 'Reset') {
			if (!confirm("This will reset your configurations to the original values!!! Are you sure you want to continue? This is not reversible!")) {
				return false;
			}
		}
		else if (value.substring(0, 6) == 'Delete') {
			if (!confirm("This will delete all your Suffusion configuration options!!! Are you sure you want to continue? This is not reversible!")) {
				return false;
			}
		}
	});

	//$j('#suffusion-options-form').ajaxForm();

	$j('.color').removeClass('text');
	$j('.slidertext').removeClass('text');

	$j('div.suf-loader').hide();
	$j('a.edit-post-type').live("click", function() {
		var thisId = this.id;
		var add_edit_form = $j('form#form-add-edit-post-type');
		$j('div.suf-loader').show();
		$j.post($j(this).attr("href"), {
					action: "suffusion_cpt_display_custom_post_type",
					post_type_index: parseInt(thisId.substr(15))
				}, function(data) {
					add_edit_form.html($j(data));
					$j(add_edit_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j('div.suf-loader').hide();
				}
		);
		$tabs.tabs('select', 1);
		return false;
	});

	$j('a.delete-post-type').live("click", function() {
		var thisId = this.id;
		var list_types_form = $j('form#form-custom-post-types');
		var nonce = $j('#custom_post_types_wpnonce').val();
		var add_edit_type_form = $j('form#form-add-edit-post-type');
		$j('div.suf-loader').show();
		$j.post($j(this).attr("href"), {
					action: "suffusion_cpt_display_all_custom_post_types",
					post_type_index: parseInt(thisId.substr(17)),
					processing_function: "delete",
					custom_post_types_wpnonce: nonce
				}, function(data) {
					list_types_form.html($j(data).filter('.suf-custom-post-types-section'));
					add_edit_type_form.html($j(data).filter('.suf-post-type-edit-section'));
					$j(list_types_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j(add_edit_type_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j('div.suf-loader').hide();
				}
		);
		$tabs.tabs('select', 0);
		return false;
	});

	$j('a.edit-taxonomy').live("click", function() {
		var thisId = this.id;
		var add_edit_form = $j('form#form-add-edit-taxonomy');
		$j('div.suf-loader').show();
		$j.post($j(this).attr("href"), {
					action: "suffusion_cpt_display_custom_taxonomy",
					taxonomy_index: parseInt(thisId.substr(14))
				}, function(data) {
					add_edit_form.html($j(data));
					$j(add_edit_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j('div.suf-loader').hide();
				}
		);
		$tabs.tabs('select', 3);
		return false;
	});

	$j('a.delete-taxonomy').live("click", function() {
		var thisId = this.id;
		var list_types_form = $j('form#form-custom-taxonomies');
		var add_edit_type_form = $j('form#form-add-edit-taxonomy');
		$j('div.suf-loader').show();
		$j.post($j(this).attr("href"), {
					action: "suffusion_cpt_display_all_custom_taxonomies",
					taxonomy_index: parseInt(thisId.substr(16)),
					processing_function: "delete"
				}, function(data) {
					list_types_form.html($j(data).filter('.suf-custom-taxonomies-section'));
					add_edit_type_form.html($j(data).filter('.suf-taxonomy-edit-section'));
					$j(list_types_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j(add_edit_type_form).find('.suf-button-bar').draggable({handle: 'h2'});
					$j('div.suf-loader').hide();
				}
		);
		$tabs.tabs('select', 2);
		return false;
	});

	$j('.suf-custom-type-settings input.button').live("click", function() {
		var thisName = this.name;
		var add_edit_post_type_form = $j('form#form-add-edit-post-type');
		var list_post_types_form = $j('form#form-custom-post-types');
		var add_edit_taxonomy_form = $j('form#form-add-edit-taxonomy');
		var list_taxonomies_form = $j('form#form-custom-taxonomies');
		var form_values;
		if (thisName == 'save-post-type-edit') {
			form_values = add_edit_post_type_form.serialize().replace(/%5B/g, '[').replace(/%5D/g, ']');

			$j('div.suf-loader').show();
			$j.post(ajaxurl, 'action=suffusion_cpt_save_custom_post_type&' + form_values, function(data) {
				add_edit_post_type_form.html($j(data).filter('.suf-post-type-edit-section'));
				list_post_types_form.html($j(data).filter('.suf-custom-post-types-section'));
				$j(list_post_types_form).find('.suf-button-bar').draggable({handle: 'h2'});
				$j(add_edit_post_type_form).find('.suf-button-bar').draggable({handle: 'h2'});
				$j('div.suf-loader').hide();
			});
			$tabs.tabs('select', 1);
		}
		else if (thisName == 'delete-all-custom-post-types') {
			var nonce = $j('#custom_post_types_wpnonce').val();
			$j('div.suf-loader').show();
			$j.post(ajaxurl, {
				action: "suffusion_cpt_display_all_custom_post_types",
				processing_function: "delete_all",
				custom_post_types_wpnonce: nonce
			}, function(data) {
				add_edit_post_type_form.html($j(data).filter('.suf-post-type-edit-section'));
				list_post_types_form.html($j(data).filter('.suf-custom-post-types-section'));
				$j('div.suf-loader').hide();
			});
		}
		else if (thisName == 'reset-post-type-edit') {
			$j(':input', 'form#form-add-edit-post-type')
					.not(':button, :submit, :reset, :hidden')
					.val('')
					.removeAttr('checked')
					.removeAttr('selected');

			//add_edit_form[0].reset();
			$j("#post_type_index").val("");
		}
		else if (thisName == 'save-taxonomy-edit') {
			form_values = add_edit_taxonomy_form.serialize().replace(/%5B/g, '[').replace(/%5D/g, ']');

			$j('div.suf-loader').show();
			$j.post(ajaxurl, 'action=suffusion_cpt_save_custom_taxonomy&' + form_values, function(data) {
				add_edit_taxonomy_form.html($j(data).filter('.suf-taxonomy-edit-section'));
				list_taxonomies_form.html($j(data).filter('.suf-custom-taxonomies-section'));
				$j(add_edit_taxonomy_form).find('.suf-button-bar').draggable({handle: 'h2'});
				$j(list_taxonomies_form).find('.suf-button-bar').draggable({handle: 'h2'});
				$j('div.suf-loader').hide();
			});

			$tabs.tabs('select', 3);
		}
		else if (thisName == 'delete-all-custom-taxonomies') {
			$j('div.suf-loader').show();
			$j.post(ajaxurl, {
				action: "suffusion_cpt_display_all_custom_taxonomies",
				processing_function: "delete_all"
			}, function(data) {
				add_edit_taxonomy_form.html($j(data).filter('.suf-taxonomy-edit-section'));
				list_taxonomies_form.html($j(data).filter('.suf-custom-taxonomies-section'));
				$j(add_edit_taxonomy_form).find('.suf-button-bar').draggable({handle: 'h2'});
				$j(list_taxonomies_form).find('.suf-button-bar').draggable({handle: 'h2'});
				$j('div.suf-loader').hide();
			});
		}
		else if (thisName == 'reset-taxonomy-edit') {
			$j(':input', 'form#form-add-edit-taxonomy')
					.not(':button, :submit, :reset, :hidden')
					.val('')
					.removeAttr('checked')
					.removeAttr('selected');

			//add_edit_form[0].reset();
			$j("#taxonomy_index").val("");
		}

		return false;
	});

	$j('#suf-options').fadeIn();
});

