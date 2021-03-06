<?php
/*  
    Copyright (c) 2009 Matt Weber

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/

//get the plugin settings
$s4w_settings = s4w_get_option('plugin_s4w_settings');
#set defaults if not initialized
if ($s4w_settings['s4w_solr_initialized'] != 1) {
  
  
  $options['s4w_index_all_sites'] = 0;
  $options['s4w_solr_host'] = 'localhost';
  $options['s4w_solr_port'] = 8983;
  $options['s4w_solr_path'] = '/solr';
  $options['s4w_index_pages'] = 1;
  $options['s4w_index_posts'] = 1;
  $options['s4w_delete_page'] = 1;
  $options['s4w_delete_post'] = 1;
  $options['s4w_private_page'] = 1;
  $options['s4w_private_post'] = 1;
  $options['s4w_output_info'] = 1;
  $options['s4w_output_pager'] = 1;
  $options['s4w_output_facets'] = 1;
  //$options['s4w_exclude_pages', array());
  $options['s4w_exclude_pages'] = '';  
  $options['s4w_num_results'] = 5;
  $options['s4w_cat_as_taxo'] = 1;
  $options['s4w_solr_initialized'] = 1;
  $options['s4w_max_display_tags'] = 10;
  $options['s4w_facet_on_categories'] = 1;
  $options['s4w_facet_on_taxonomy'] = 1;
  $options['s4w_facet_on_tags'] = 1;
  $options['s4w_facet_on_author'] = 1;
  $options['s4w_facet_on_type'] = 1;
  $options['s4w_enable_dym'] = 1;
  $options['s4w_index_comments'] = 1;
  $options['s4w_connect_type'] = 'solr';
  //$options['s4w_index_custom_fields', array());
  //$options['s4w_facet_on_custom_fields', array());
  $options['s4w_index_custom_fields'] = '';  
  $options['s4w_facet_on_custom_fields'] = '';  
  
  //update existing settings from multiple option record to a single array
  //if old options exist, update to new system
  $delte_option_function = 'delete_option';
  if (is_multisite()) {
    $indexall = get_site_option('s4w_index_all_sites');
    $delte_option_function = 'delete_site_option';
  }

	foreach( $options as $key => $value ) {
    if( $existing = get_option($key)) {
  	  krumo( $existing);
  		$options[$key] = $existing;
  		delete_option($key);
  		$indexall = FALSE;
      $option_function($key);
    }
	}
  
  $s4w_settings = $options;
  //save our options array
  s4w_update_option($options);
}

wp_reset_vars(array('action'));

# save form settings if we get the update action
# we do saving here instead of using options.php because we need to use
# s4w_update_option instead of update option.
# As it stands we have 27 options instead of making 27 insert calls (which is what update_options does)
# Lets create an array of all our options and save it once.
if ($_POST['action'] == 'update') {   
  //lets loop through our setting fields $_POST['settings']
  foreach ($s4w_settings as $option => $old_value ) {
    $value = $_POST['settings'][$option];
    if ($option == 's4w_index_all_sites' || $option == 's4w_solr_initialized') $value = trim($old_value);  
    if ( !is_array($value) ) $value = trim($value); 
    $value = stripslashes_deep($value);
    $s4w_settings[$option] = $value;
  }    
  //lets save our options array
  s4w_update_option($s4w_settings);


  ?>
  <div id="message" class="updated fade"><p><strong><?php _e('Success!', 'solr4wp') ?></strong></p></div>
  <?php
}

# checks if we need to check the checkbox
function s4w_checkCheckbox( $fieldValue ) {
  if( $fieldValue == '1'){
    echo 'checked="checked"';
  }
}

function s4w_checkConnectOption($optionType, $connectType) {
    if ( $optionType === $connectType ) {
        echo 'checked="checked"';
    }
}



# check for any POST settings
if ($_POST['s4w_ping']) {
    if (s4w_get_solr(true)) {
?>
<div id="message" class="updated fade"><p><strong><?php _e('Ping Success!', 'solr4wp') ?></strong></p></div>
<?php
    } else {
?>
    <div id="message" class="updated fade"><p><strong><?php _e('Ping Failed!', 'solr4wp') ?></strong></p></div>
<?php
    }
} else if ($_POST['s4w_deleteall']) {
    s4w_delete_all();
?>
    <div id="message" class="updated fade"><p><strong><?php _e('All Indexed Pages Deleted!', 'solr4wp') ?></strong></p></div>
<?php
} else if ($_POST['s4w_optimize']) {
    s4w_optimize();
?>
    <div id="message" class="updated fade"><p><strong><?php _e('Index Optimized!', 'solr4wp') ?></strong></p></div>
<?php
}
?>

<div class="wrap">
<h2><?php _e('Solr For WordPress', 'solr4wp') ?></h2>

<form method="post" action="options-general.php?page=solr-for-wordpress/solr-for-wordpress.php">
<h3><?php _e('Configure Solr', 'solr4wp') ?></h3>

<div class="solr_admin clearfix">
	<div class="solr_adminR">
		<div class="solr_adminR2" id="solr_admin_tab2">
			<label><?php _e('Solr Host', 'solr4wp') ?></label>
			<p><input type="text" name="settings[s4w_solr_host]" value="<?php _e($s4w_settings['s4w_solr_host'], 'solr4wp'); ?>" /></p>
			<label><?php _e('Solr Port', 'solr4wp') ?></label>
			<p><input type="text" name="settings[s4w_solr_port]" value="<?php _e($s4w_settings['s4w_solr_port'], 'solr4wp'); ?>" /></p>
			<label><?php _e('Solr Path', 'solr4wp') ?></label>
			<p><input type="text" name="settings[s4w_solr_path]" value="<?php _e($s4w_settings['s4w_solr_path'], 'solr4wp'); ?>" /></p>
		</div>
	</div>
	<ol>
		<li id="solr_admin_tab1_btn" class="solr_admin_tab1">
		</li>
		<li id="solr_admin_tab2_btn" class="solr_admin_tab2">
			<h4><input id="solrconnect" name="settings[s4w_connect_type]" type="radio" value="solr" <?php s4w_checkConnectOption($s4w_settings['s4w_connect_type'], 'solr'); ?> onclick="switch1();" />Solr Server</h4>
			<ol>
				<li>Download, install and configure your own <a href="">Apache Solr 1.4</a> instance</li>
			</ol>
		</li>
	</ol>
</div>
<hr />
<h3><?php _e('Indexing Options', 'solr4wp') ?></h3>
<table class="form-table">
    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Index Pages', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_index_pages]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_index_pages']); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Index Posts', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_index_posts]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_index_posts']); ?> /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Remove Page on Delete', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_delete_page]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_delete_page']); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Remove Post on Delete', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_delete_post]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_delete_post']); ?> /></td>
    </tr>
    
    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Remove Page on Status Change', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_private_page]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_private_page']); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Remove Post on Status Change', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_private_post]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_private_post']); ?> /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Index Comments', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_index_comments]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_index_comments']); ?> /></td>
    </tr>
        
    <?php
    //is this a multisite installation
    if (is_multisite() && is_main_site()) {
    ?>
    
    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Index all Sites', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_index_all_sites]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_index_all_sites']); ?> /></td>
    </tr>
    <?php
    }
    ?>
    <tr valign="top">
        <th scope="row"><?php _e('Index custom fields (comma separated names list)') ?></th>
        <td><input type="text" name="settings[s4w_index_custom_fields]" value="<?php print( s4w_filter_list2str($s4w_settings['s4w_index_custom_fields'], 'solr4wp')); ?>" /></td>
    </tr>
    <tr valign="top">
        <th scope="row"><?php _e('Excludes Posts or Pages (comma separated ids list)') ?></th>
        <td><input type="text" name="settings[s4w_exclude_pages]" value="<?php print( s4w_filter_list2str($s4w_settings['s4w_exclude_pages'], 'solr4wp')); ?>" /></td>
    </tr>
</table>
<hr />
<h3><?php _e('Result Options', 'solr4wp') ?></h3>
<table class="form-table">
    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Output Result Info', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_output_info]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_output_info']); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Output Result Pager', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_output_pager]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_output_pager']); ?> /></td>
    </tr>
 
    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Output Facets', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_output_facets]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_output_facets']); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Category Facet as Taxonomy', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_cat_as_taxo]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_cat_as_taxo']); ?> /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Categories as Facet', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_facet_on_categories]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_facet_on_categories']); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Tags as Facet', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_facet_on_tags]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_facet_on_tags']); ?> /></td>
    </tr>
    
    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Author as Facet', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_facet_on_author]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_facet_on_author']); ?> /></td>
        <th scope="row" style="width:200px;float:left;margin-left:20px;"><?php _e('Type as Facet', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_facet_on_type]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_facet_on_type']); ?> /></td>
    </tr>

     <tr valign="top">
         <th scope="row" style="width:200px;"><?php _e('Taxonomy as Facet', 'solr4wp') ?></th>
         <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_facet_on_taxonomy]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_facet_on_taxonomy']); ?> /></td>
      </tr>
      
    <tr valign="top">
        <th scope="row"><?php _e('Custom fields as Facet (comma separated ordered names list)') ?></th>
        <td><input type="text" name="settings[s4w_facet_on_custom_fields]" value="<?php print( s4w_filter_list2str($s4w_settings['s4w_facet_on_custom_fields'], 'solr4wp')); ?>" /></td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width:200px;"><?php _e('Enable Spellchecking', 'solr4wp') ?></th>
        <td style="width:10px;float:left;"><input type="checkbox" name="settings[s4w_enable_dym]" value="1" <?php echo s4w_checkCheckbox($s4w_settings['s4w_enable_dym']); ?> /></td>
    </tr>
                   
    <tr valign="top">
        <th scope="row"><?php _e('Number of Results Per Page', 'solr4wp') ?></th>
        <td><input type="text" name="settings[s4w_num_results]" value="<?php _e($s4w_settings['s4w_num_results'], 'solr4wp'); ?>" /></td>
    </tr>   
    
    <tr valign="top">
        <th scope="row"><?php _e('Max Number of Tags to Display', 'solr4wp') ?></th>
        <td><input type="text" name="settings[s4w_max_display_tags]" value="<?php _e($s4w_settings['s4w_max_display_tags'], 'solr4wp'); ?>" /></td>
    </tr>
</table>
<hr />
<?php settings_fields('s4w-options-group'); ?>

<p class="submit">
<input type="hidden" name="action" value="update" />
<input id="settingsbutton" type="submit" class="button-primary" value="<?php _e('Save Changes', 'solr4wp') ?>" />
</p>

</form>
<hr />
<form method="post" action="options-general.php?page=solr-for-wordpress/solr-for-wordpress.php">
<h3><?php _e('Actions', 'solr4wp') ?></h3>
<table class="form-table">
    <tr valign="top">
        <th scope="row"><?php _e('Check Server Settings', 'solr4wp') ?></th>
        <td><input type="submit" class="button-primary" name="s4w_ping" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
    </tr>
 
    <tr valign="top">
        <th scope="row"><?php _e('Load All Pages', 'solr4wp') ?></th>
        <td><input type="submit" class="button-primary" name="s4w_pageload" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
    </tr>

    <tr valign="top">
        <th scope="row"><?php _e('Load All Posts', 'solr4wp') ?></th>
        <td><input type="submit" class="button-primary" name="s4w_postload" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
    </tr>
    
    <tr valign="top">
        <th scope="row"><?php _e('Optimize Index', 'solr4wp') ?></th>
        <td><input type="submit" class="button-primary" name="s4w_optimize" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
    </tr>
        
    <tr valign="top">
        <th scope="row"><?php _e('Delete All', 'solr4wp') ?></th>
        <td><input type="submit" class="button-primary" name="s4w_deleteall" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
    </tr>
</table>
</form>

</div>
