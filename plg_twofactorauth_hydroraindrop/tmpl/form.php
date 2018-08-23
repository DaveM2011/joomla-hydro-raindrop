<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.hydroraindrop.tmpl
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('jquery.framework');
$document = JFactory::getDocument();
$document->addStyleSheet('/plugins/twofactorauth/hydroraindrop/hydro-raindrop-public.css');
$document->addScript('/plugins/twofactorauth/hydroraindrop/hydro-raindrop-public.js');

?>
<h2>Hydro Raindrop MFA</h2>

<div class="hydro-raindrop-mfa">
	<h1>
		<img src="<?php echo $logo; ?>" height="46" alt="Hydro Raindrop MFA">
	</h1>
</div>

<div class="well">
	<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_INTRO') ?>
</div>

<?php if ( ! $this->validConfig ) : ?>
	<div class="alert alert-error">
		<?php if ( current_user_can( 'manage_options' ) ) : ?>
			The Hydro Raindrop MFA plugin is not properly configured, please review the
			<a href="<?php echo _( 'options-general.php?page=' . $this->plugin_name ); ?>-options">Hydro Raindrop MFA Settings</a>
			and try again.
		<?php else : ?>
			<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_CONFIG_INVALID') ?>
		<?php endif; ?>
	</div>
<?php else : ?>
	<?php if ( ! $hydro_mfa_enabled ) : ?>
		<div class="alert alert-error">
			Your account does not have Hydro Raindrop MFA enabled.
		</div>
	<?php endif ?>
	<?php if ( $hydro_mfa_enabled && ! $hydro_raindrop_confirmed ) : ?>
		<div class="alert alert-error">
			Your account does have Hydro Raindrop MFA enabled, but it is unconfirmed.<a href="<?php echo _( '?hydro-raindrop-verify=1 ' ); ?>">Click here</a> to confirm.
		</div>
	<?php endif ?>
	<?php if ( $hydro_mfa_enabled && $hydro_raindrop_confirmed ) : ?>
		<div class="alert alert-success">
			Your account has Hydro Raindrop MFA enabled and confirmed.
		</div>
	<?php endif ?>

	<?php if ( $hydro_mfa_enabled ) : ?>
		<button class="btn" onclick="document.getElementById('jform_twofactor_method').value='none'">Unregister Raindrop MFA</button>
	<?php endif ?>

	<fieldset class="hydro-raindrop-mfa">
		<legend>
			<?php echo JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_HYDROID') ?>
		</legend>
		<?php if ( $hydro_mfa_enabled && $hydro_raindrop_confirmed) : ?>
			<input type="text"
				class="input-small"
				name="jform[twofactor][hydroraindrop][hydro_id]"
				id="hydro_id"
				autocomplete="0"
				maxlength="7"
				value="<?php echo $hydro_id; ?>"
				disabled />
		<?php else : ?>
			<input type="text"
				class="input-small"
				name="jform[twofactor][hydroraindrop][hydro_id]"
				id="hydro_id"
				autocomplete="0"
				size="7"
				minlength="3"
				maxlength="32"
				value="<?php echo $hydro_id ?? ''; ?>" />
			<p>Enter your HydroID, visible in the Hydro mobile app.</p>
		<?php endif ?>
	</fieldset>

	<?php if ( $hydro_mfa_enabled && ! $hydro_raindrop_confirmed ) : ?>
		<fieldset class="hydro-raindrop-mfa">
			<p class="hydro-illustration">
				<img src="<?php echo $image; ?>" width="180" alt="">
			</p>
			<p>
				<label for="hydro_digits">
					Enter security code into the Hydro app.
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
				class="button button-primary button-large"
				value="Authenticate" />
		</fieldset>
	<?php endif ?>
<?php endif ?>
