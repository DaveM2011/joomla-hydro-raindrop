<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.hydroraindrop.tmpl
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<legend>
	<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_MFA'); ?>
</legend>

<div class="hydro-raindrop-mfa">
	<h1>
		<img src="<?php echo $logo; ?>" height="46" alt="<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_MFA'); ?>">
	</h1>
</div>

<div class="well">
	<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_INTRO') ?>
</div>

<?php if ( ! $this->validConfig ) : ?>
	<div class="alert alert-error">
		<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_CONFIG_INVALID') ?>
	</div>
<?php else : ?>
	<?php if ( ! $hydro_mfa_enabled ) : ?>
		<div class="alert alert-error">
			<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_NO_MFA') ?>
		</div>
	<?php endif ?>
	<?php if ( $hydro_mfa_enabled && ! $hydro_raindrop_confirmed ) : ?>
		<div class="alert alert-error">
			<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_NO_CONFIRM') ?>
		</div>
	<?php endif ?>
	<?php if ( $hydro_mfa_enabled && $hydro_raindrop_confirmed ) : ?>
		<div class="alert alert-success">
			<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_CONFIRMED') ?>
		</div>
	<?php endif ?>

	<?php if ( $hydro_mfa_enabled ) : ?>
		<?php if ( $is_admin ) : ?>
			<button class="btn" onclick="document.getElementById('jform_twofactor_method').value='none';Joomla.submitbutton('user.apply')">Unregister Raindrop MFA</button>
		<?php else: ?>
			<button class="btn" onclick="document.getElementById('jform_twofactor_method').value='none'">Unregister Raindrop MFA</button>
		<?php endif ?>
	<?php endif ?>
	<div class="clear"></div>
	<br />
	<fieldset class="hydro-raindrop-mfa">
		<legend>
			<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_HYDROID') ?>
		</legend>
		<?php if ( $hydro_mfa_enabled && $hydro_raindrop_confirmed ) : ?>
			<input type="text"
				class="input-small"
				name="jform[twofactor][hydroraindrop][hydro_id]"
				id="hydro_id"
				autocomplete="0"
				maxlength="7"
				value="<?php echo $hydro_id; ?>"
				disabled />
		<?php else : ?>
			<p>
				<label>
					<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_ENTER_HYDROID'); ?>
				</label>
			</p>
			<input type="text"
				class="input-small"
				name="jform[twofactor][hydroraindrop][hydro_id]"
				id="hydro_id"
				autocomplete="0"
				size="7"
				minlength="3"
				maxlength="32"
				value="<?php echo $hydro_id ?? ''; ?>" />
		<?php endif ?>
	</fieldset>

	<?php if ( $hydro_mfa_enabled && ! $hydro_raindrop_confirmed ) : ?>
		<fieldset class="hydro-raindrop-mfa">
			<p class="hydro-illustration">
				<img src="<?php echo $image; ?>" width="180" alt="">
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
				id="hydro_raindrop_authenticate"
				name="hydro_raindrop"
				class="btn btn-success"
				value="<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_AUTHENTICATE'); ?>" />
		</fieldset>
	<?php endif ?>
<?php endif ?>

<div class="clear"></div>
<br />
