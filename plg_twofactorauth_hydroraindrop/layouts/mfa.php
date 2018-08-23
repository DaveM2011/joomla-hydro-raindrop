<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.hydroraindrop.layouts
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Extract the view data
extract($displayData);

?>
<style>
html, body {
	height: 100%;
}
.parent {
	height: 100%;
	position: relative;
}
.child {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
}
</style>
<div class="parent">
<div id="login" class="hydro-raindrop-mfa child">
	<h1>
		<img src="<?php echo $logo; ?>" height="46" alt="<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_MFA'); ?>">
	</h1>
	<?php if ($error) { ?>
		<div id="login_error"><?php echo $error; ?></div>
	<?php } ?>
	<form action="" method="post">
		<p class="hydro-illustration">
			<img src="<?php echo $image ?>" width="180" alt="">
		</p>
		<p>
			<label for="hydro_digits">
				<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_ENTER_CODE_APP'); ?>
			</label>
		</p>
		<div id="hydro_digits" class="message-digits">
			<span class="digit"><?php echo substr( (string) $message, 0, 1 ); ?></span>
			<span class="digit"><?php echo substr( (string) $message, 1, 1 ); ?></span>
			<span class="digit"><?php echo substr( (string) $message, 2, 1 ); ?></span>
			<span class="digit"><?php echo substr( (string) $message, 3, 1 ); ?></span>
			<span class="digit"><?php echo substr( (string) $message, 4, 1 ); ?></span>
			<span class="digit"><?php echo substr( (string) $message, 5, 1 ); ?></span>
		</div>
		<input type="submit"
			name="cancel_hydro_raindrop"
			class="btn"
			value="<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_CANCEL'); ?>" />
		<input type="submit"
			id="hydro_raindrop_authenticate"
			name="hydro_raindrop"
			class="btn btn-success"
			value="<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_AUTHENTICATE'); ?>" />
	</form>
</div>
</div>