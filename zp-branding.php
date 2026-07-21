<?php
/**
 * Replacement of the Zenphoto logo on the backend with a custom logo.
 *
 * This plugin is a combination of the <code>admin-branding</code> plugin from kuzzzma and my <code>zp-branding</code> plugin.
 *
 * Create a folder with the name <em>adminlogo</em> in the <em>uploaded</em> folder or use elFinder to do so.
 * Upload the file(s) to the <em>adminlogo</em> folder you wish to use as a custom backend logo.
 * Set options as desired.
 *
 * ## Options:
 *
 * - Select what you want to see. No logo, the Zenphoto default logo or a custom logo.
 * - Set the width of the logo. Height is proportional.
 * - Add custom CSS to change the appearance of the logo and/or backend.
 * - Select the custom logo in the <em>/uploaded/adminlogo/</em> folder you want to use.
 *
 * @author Fred Sondaar (fretzl), with a lot of additions from kuzzma's <em>admin-branding</em> ver. 1.1 plugin (https://github.com/kuz-z-zma/adminBranding)
 * @package plugins
 * @subpackage admin
 */

$plugin_is_filter = 5|ADMIN_PLUGIN;
$plugin_description = i18n::gettext_pl("Replace the default Zenphoto logo on the backend with a custom logo.", 'zp-branding');
$plugin_author = "Fred Sondaar (fretzl), with additions from kuzzzma's <em>admin-branding</em> ver. 1.1 plugin (https://github.com/kuz-z-zma/adminBranding)";
$plugin_siteurl = 'https://github.com/fretzl/zp-branding';
$plugin_version = '2.0.0';
$plugin_disable = (filter::hasFilter('admin_head') && extensionEnabled('admin-branding')) ? i18n::gettext_pl('Only one Zenphoto backend customization plugin may be enabled. Please disable the <code>admin-branding</code> plugin to use this one.','zp-branding') : '';
$plugin_category = i18n::gettext_pl('Admin', 'zp-branding');
$option_interface = 'zpBrandingOptions';

if ($plugin_disable) {
    enableExtension('zp-branding', 0);
} else {
    filter::registerFilter('admin_head', 'zpBranding::printCustomZpLogo');
}

$zp_branding_logo = FULLWEBPATH . '/' . ZENFOLDER . '/images/zen-logo.png';

class zpBrandingOptions {
	
	function __construct() {
		purgeOption('width');// older version option name
		purgeOption('restore');// older version option name
		setOptionDefault('zpbranding_logo-image', 'default');
		setOptionDefault('zpbranding-width', '200');
		setOptionDefault('zpbranding_custom_css', '');
	}

	static function getOptionsSupported() {
		global $zp_branding_logo;
		if ( $zp_branding_logo ) {
			$width = getimagesize($zp_branding_logo)[0];
			$options = array( 
					i18n::gettext_pl('Logo for backend', 'zp-branding') => array('key' => 'zpbranding_logo-image', 'type' => OPTION_TYPE_RADIO,
						'order' => 1,
						'buttons' => array(
							i18n::gettext_pl('No logo', 'zp-branding') => 'disabled',
							i18n::gettext_pl('Custom logo', 'zp-branding') => 'custom',
							i18n::gettext_pl('Default Zenphoto logo', 'zp-branding') => 'default'),
						'desc' => i18n::gettext_pl('Choose what you want to use as logo or no logo at all.', 'zp-branding')),   
					i18n::gettext_pl('Select admin logo image', 'zp-branding') => array('key' => 'zpbranding_logo-custom', 'type' => OPTION_TYPE_CUSTOM, 
						'order' => 4, 
						'desc' => sprintf(i18n::gettext_pl('When <em>Custom logo</em> is selected for <em>Logo for backend</em> you may select a logo from files in the <em>%s</em> folder.<br>If you use the <em>elFinder</em> plugin for Uploads, you can upload files to this folder. Alternatively, you can use FTP to upload your image to the <em>adminlogo</em> folder.', 'zp-branding'),(UPLOAD_FOLDER.'/adminlogo/'))),
					i18n::gettext_pl('Width', 'zp-branding') => array('key' => 'zpbranding-width', 'type' => OPTION_TYPE_TEXTBOX,
						'order'=> 1,
						'desc' => i18n::gettext_pl('The width of the logo (px). Default is 200px.<br>The height is proportional.', 'zp-branding')),
					i18n::gettext_pl('Custom CSS', 'zp-branding') => array('key' => 'zpbranding_custom_css', 'type' => OPTION_TYPE_TEXTAREA, 
						'order' => 3,
						'multilingual' => 0,
						'desc' => i18n::gettext_pl('Enter custom CSS to alter the appearance of the admin area.<br>It is printed between &lt;style&gt; tags in the &lt;head&gt; section.', 'zp-branding'))
				);
				if ( getOption('zpbranding-width') != $width ) {
					$options[i18n::gettext_pl('Reset', 'zp-branding')] = array('key' => 'zpbranding-restore', 'type' => OPTION_TYPE_CHECKBOX,
						'order' => 2,
						'desc' => i18n::gettext_pl('Reset to the original width.', 'zp-branding'));
				}			
		return $options;
		} else { ?>
			<div class="errorbox">
			<?php echo sprintf(i18n::gettext_pl("Image <i>%s</i> does not exist.", 'zp-branding'), substr($zp_branding_logo, strrpos($zp_branding_logo, '/') + 1)); ?>
			</div>
		<?php
		}
	}
	
	function handleOption($option, $currentValue) {
        if ( $option == "zpbranding_logo-custom" ) { ?>
            <select id="zpbranding_logo-custom" name="zpbranding_logo-custom">
                <option value="" style="background-color:LightGray"><?php echo i18n::gettext_pl('*Not specified*', 'zp-branding'); ?></option>';
                <?php filter::applyFilter('theme_head');
                generateListFromFiles($currentValue, SERVERPATH.'/'.UPLOAD_FOLDER.'/adminlogo/','');	?>
            </select>
            <?php }
    }

	function handleOptionSave() {
		global $zp_branding_logo;
		$width = getimagesize($zp_branding_logo)[0];
		if (getOption('zpbranding-restore')) {
			setOption('zpbranding-width', $width);
			setOption('zpbranding-restore', 0);
		}
	}
}

class zpBranding {

	static function printCustomZpLogo() {
		global $zp_branding_logo;
		if ( getOption('zpbranding_logo-image') == 'custom' && getOption('zpbranding_logo-custom') != '' ) {
            $$zp_branding_logo = FULLWEBPATH.'/'.UPLOAD_FOLDER.'/adminlogo/' . getOption('zpbranding_logo-custom');
        }
		if (getimagesize($zp_branding_logo)) {// Check if file is image
			$width = getimagesize($zp_branding_logo)[0];
			$height = getimagesize($zp_branding_logo)[1];
			$ratio = round($height / $width, 2);
			setOptionDefault('zpbranding-width', $width);
			setOptionDefault('zpbranding-restore', 0);
			if (getOption('zpbranding-width')) {
				$new_width = getOption('zpbranding-width');
				$height = ceil($new_width * $ratio);
			} else {
				$new_width = $width;
				setOption('zpbranding-width', $width);
			}
			?>
			
			<?php
			$custom_css = '';
			if ( !empty(getOption('zpbranding_custom_css')) ) {
				$custom_css = "\n/**----------- Custom CSS -----------**/\n" . getOption('zpbranding_custom_css') . "\n" . "/*-----------End of Custom CSS-----------------*/" . "\n";
			} 
			?>
			
			<?php 
			if ( getOption('zpbranding_logo-image') == 'disabled' ) { ?>
				<style>
					#logo {
						display: none; 
					}

					<?php echo $custom_css; ?>
				</style>
			<?php } ?>
				
			<?php
			if ( getOption('zpbranding_logo-image') == 'default' ) { ?>
				<style>
					<?php echo $custom_css; ?>
				</style>
			<?php } ?>
			
			<?php 
			if ( getOption('zpbranding_logo-image') == 'custom' && getOption('zpbranding_logo-custom') != '' ) { ?>
				<style>
					#logo {
						display: none;
					}
					
					#administration {
						width: <?php echo $new_width; ?>px;
						height: <?php echo $height; ?>px;
						background: url("<?php echo pathurlencode(WEBPATH.'/'.UPLOAD_FOLDER.'/adminlogo/' . getOption('zpbranding_logo-custom')); ?>") no-repeat 0 0;
						background-size: <?php echo $new_width; ?>px;
					}
					
					<?php echo $custom_css; ?>
				</style>
			<?php }
		} else { ?>
			<div class="errorbox">
			<?php echo sprintf(i18n::gettext_pl("Image <i>%s</i> does not exist.", 'zp-branding'), substr($zp_branding_logo, strrpos($zp_branding_logo, '/') + 1)); ?>
			</div>
		<?php
		}
	}
}
?>