<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.hydroraindrop
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Require composer autoloader if installed on it's own
if (file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
	require_once $composer;
}

use Adrenth\Raindrop\ApiSettings;
use Adrenth\Raindrop\Client;
use Adrenth\Raindrop\ApiAccessToken;
use Adrenth\Raindrop\Environment\ProductionEnvironment;
use Adrenth\Raindrop\Environment\SandboxEnvironment;
use Adrenth\Raindrop\Exception\UnableToAcquireAccessToken;
use Adrenth\Raindrop\TokenStorage\TokenStorage;
use Adrenth\Raindrop\Exception\RegisterUserFailed;
use Adrenth\Raindrop\Exception\UserAlreadyMappedToApplication;
use Adrenth\Raindrop\Exception\VerifySignatureFailed;
use Adrenth\Raindrop\Exception\UnregisterUserFailed;

require_once __DIR__ . '/hydro-raindrop-token.php';

/**
 * Joomla! Two Factor Authentication using Hydro Raindrop Plugin
 *
 * @since  3.2
 */
final class PlgTwofactorauthHydroraindrop extends JPlugin
{
	const COOKIE_NAME = 'HydroRaindropMfa';
	const TIME_OUT = 90;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method name
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $methodName = 'hydroraindrop';

	/**
	 * Client
	 *
	 * @var    object
	 */
	static protected $client;

	/**
	 * validConfig
	 *
	 * @var    bool
	 */
	protected $validConfig;

	/**
	 * Lets create the class
	 */
	public function __construct($subject, $config)
	{
		//exit;
		parent::__construct($subject, $config);
		// Get the config and parse it
		$config = json_decode($config['params'], true);
		// Validate the config
		if (!$this->validateHydroRaindropConfig($config))
			return;
		$this->validConfig = true;
		// Setup the client
		self::$client = new Client(
			new ApiSettings(
				$config['client_id'],
				$config['client_secret'],
				$config['environment'] === 'sandbox'
					? new SandboxEnvironment()
					: new ProductionEnvironment()
			),
			new Hydro_Raindrop_TokenStorage,
			$config['application_id']
		);
		// Load the helper and model used for two factor authentication
		JLoader::register('UsersModelUser', JPATH_ADMINISTRATOR . '/components/com_users/models/user.php');
		JLoader::import('joomla.filesystem.file');
	}

	/**
	 * Validate the hydro raindrop configuration
	 *
	 * @var		string
	 * @var		string
	 */
	private function log($message, $type = 'error')
	{
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_($message), $type);
	}

	/**
	 * Get the mfa view data
	 *
	 * @var string
	 * @return array
	 */
	private function view_data($message)
	{
		return array(
			'error' => null,
			'message' => $message,
			'logo' => JURI::root() . 'plugins/twofactorauth/hydroraindrop/images/logo.svg',
			'image' => JURI::root() . 'plugins/twofactorauth/hydroraindrop/images/security-code.png',
		);
	}

	/**
	 * Validate the hydro raindrop configuration
	 *
	 * @var    array
	 */
	private function validateHydroRaindropConfig($config)
	{
		$options = [
			'client_id',
			'client_secret',
			'environment',
			'application_id',
		];
		foreach ($options as $option) {
			$value = isset($config[$option]) ? $config[$option] : '';
			if (empty($value)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * This method returns the identification object for this two factor
	 * authentication plugin.
	 *
	 * @return  stdClass  An object with public properties method and title
	 *
	 * @since   3.2
	 */
	public function onUserTwofactorIdentify()
	{
		$section = (int)$this->params->get('section', 3);

		$current_section = 0;

		try {
			$app = JFactory::getApplication();

			if ($app->isClient('administrator')) {
				$current_section = 2;
			} elseif ($app->isClient('site')) {
				$current_section = 1;
			}
		} catch (Exception $exc) {
			$current_section = 0;
		}

		if (!($current_section & $section)) {
			return false;
		}

		$user = JFactory::getUser();
		if ($user->guest) {
			return false;
		}

		return (object)array(
			'method' => $this->methodName,
			'title' => JText::_('PLG_TWOFACTORAUTH_HYDRORAINDROP_METHOD_TITLE')
		);
	}

	public function onUserAfterLogin(array $options)
	{
		$model = new UsersModelUser;
		$session = JFactory::getSession();
		$user = $options['user'];
		$otp = $model->getOtpConfig($user->id);
		if ($otp && isset($otp->config['hydro_id'])) {
			$this->unset_cookie();
			$session->set('id', $otp->config['hydro_id'], 'hydro_raindrop');
			$session->set('confirmed', $otp->config['hydro_raindrop_confirmed'], 'hydro_raindrop');
			$session->set('reauthenticate', true, 'hydro_raindrop');
			$this->show_mfa();
		}
		return true;
	}

	public function onAfterDispatch() {
		$this->show_mfa();
	}

	/**
	 * Shows the configuration page for this two factor authentication method.
	 *
	 * @param   object   $otpConfig  The two factor auth configuration object
	 * @param   integer  $user_id    The numeric user ID of the user whose form we'll display
	 *
	 * @return  boolean|string  False if the method is not ours, the HTML of the configuration page otherwise
	 *
	 * @see     UsersModelUser::getOtpConfig
	 * @since   3.2
	 */
	public function onUserTwofactorShowConfiguration($otpConfig, $user_id = null)
	{
		$session = JFactory::getSession();

		$hydro_id = '';
		//var_dump($this->get_salt());
		//var_dump($otpConfig, JFactory::getConfig()->get('secret'));
		if ($otpConfig->method === $this->methodName) {
			// This method is already activated. Reuse the same secret key.
			$hydro_id = $otpConfig->config['hydro_id'];
			$session->set('id', $hydro_id, 'hydro_raindrop');
		}
		
		// Is this a new HYDRO setup? If so, we'll have to show the code validation field.
		$new_totp = $otpConfig->method !== $this->methodName;

		$hydro_mfa_enabled = (bool)$otpConfig->method == $this->methodName && $hydro_id;
		$hydro_raindrop_confirmed = (bool)isset($otpConfig->config['hydro_raindrop_confirmed']) ? $otpConfig->config['hydro_raindrop_confirmed'] : false;

		$message = $this->get_message();
		/*if (!$hydro_raindrop_confirmed || !$this->verifyMessage($hydro_id, $message)) {
			$session->set('reauthenticate', true, 'hydro_raindrop');
			$message = $this->set_message();
		} else {
			$hydro_raindrop_confirmed = true;
		}*/

		// Include the form.php from a template override. If none is found use the default.
		$path = FOFPlatform::getInstance()->getTemplateOverridePath('plg_twofactorauth_hydroraindrop', true);

		// Start output buffering
		@ob_start();

		extract($this->view_data($message));

		if (JFile::exists($path . '/form.php')) {
			include_once $path . '/form.php';
		} else {
			include_once __DIR__ . '/tmpl/form.php';
		}

		// Stop output buffering and get the form contents
		$html = @ob_get_clean();

		// Return the form contents
		return array(
			'method' => $this->methodName,
			'form' => $html
		);
	}

	/**
	 * The save handler of the two factor configuration method's configuration
	 * page.
	 *
	 * @param   string  $method  The two factor auth method for which we'll show the config page
	 *
	 * @return  boolean|stdClass  False if the method doesn't match or we have an error, OTP config object if it succeeds
	 *
	 * @see     UsersModelUser::setOtpConfig
	 * @since   3.2
	 */
	public function onUserTwofactorApplyConfiguration($method)
	{
		if ($method !== $this->methodName) {
			return false;
		}
		
		$app = JFactory::getApplication();

		// Get a reference to the input data object
		$input = $app->input;

		// Load raw data
		$rawData = $input->get('jform', array(), 'array');

		if (!isset($rawData['twofactor']['hydroraindrop'])) {
			return false;
		}

		$data = $rawData['twofactor']['hydroraindrop'];

		// Warn if the hydro_id is empty
		if (array_key_exists('hydro_id', $data) && empty($data['hydro_id'])) {
			try {
				$this->log('PLG_TWOFACTORAUTH_HYDRORAINDROP_ERR_VALIDATIONFAILED');
			} catch (Exception $exc) {
				// This only happens when we are in a CLI application. We cannot
				// enqueue a message, so just do nothing.
			}
			return false;
		}

		$hydro_id = $data['hydro_id'];

		$disable_hydro_mfa = isset($data['disable_hydro_mfa']);
		if ($disable_hydro_mfa) {
			if (!empty($hydro_id)) {
				try {
					self::$client->unregisterUser($hydro_id);
				} catch (UnregisterUserFailed $e) {
					$this->log($e->getMessage());
				}
			}
			$this->unset_cookie();
			return false;
		}

		if (!empty($hydro_id)) {
			$length = strlen($hydro_id);
			if ($length < 5 || $length > 7) {
				$this->log('Please provide a valid HydroID.');
				return false;
			}
			$user = JFactory::getUser();
			$model = new UsersModelUser;
			$otp = $model->getOtpConfig($user->id);
			try {
				self::$client->registerUser($hydro_id);

				return (object)array(
					'method' => 'hydroraindrop',
					'config' => array(
						'hydro_id' => (int)$hydro_id,
						'hydro_raindrop_confirmed' => $hydro_id == $otp->config['hydro_id'] ? $otp->config['hydro_raindrop_confirmed'] : 0
					),
					'otep' => array()
				);
			} catch (UserAlreadyMappedToApplication $e) {
				/*
				 * User is already mapped to this application.
				 *
				 * Edge case: A user tries to re-register with Hydro ID. If the user meta has been deleted, the
				 *            user can re-use his Hydro ID but needs to verify it again.
				 */
				$this->unset_cookie();

				return (object)array(
					'method' => 'hydroraindrop',
					'config' => array(
						'hydro_id' => $hydro_id,
						'hydro_raindrop_confirmed' => $hydro_id == $otp->config['hydro_id'] ? $otp->config['hydro_raindrop_confirmed'] : 0
					),
					'otep' => array()
				);
			} catch (RegisterUserFailed $e) {
				$this->log($e->getMessage());
			}
		}

		return false;
	}

	/**
	 * This method should handle any two factor authentication and report back
	 * to the subject.
	 *
	 * @param   array  $credentials  Array holding the user credentials
	 * @param   array  $options      Array of extra options
	 *
	 * @return  boolean  True if the user is authorised with this two-factor authentication method
	 *
	 * @since   3.2
	 */
	public function onUserTwofactorAuthenticate($credentials, $options)
	{
		// Just return true because HYDRO requires entering a code after the user's hydro id has been fetched
		return true;
	}

	/**
	 * Generates the salt which will be used for hashing and encrypting.
	 *
	 * @return string
	 */
	private function get_salt() : string
	{
		if (defined('AUTH_SALT')) {
			return AUTH_SALT;
		}
		return sha1(base64_encode(JFactory::getConfig()->get('secret')));
		/*$salt = '';
		if (file_exists(__DIR__ . '/salt.key')) {
			$salt = file_get_contents(__DIR__ . '/salt.key');
		} else {
			$salt = random_bytes(32);
			file_put_contents(__DIR__ . '/salt.key', $salt);
		}
		return $salt;*/
	}

	/**
	 * Perform Hash Mac on data and return hash.
	 *
	 * @param string $data Data to hash.
	 *
	 * @return string
	 */
	private function hash_hmac(string $data) : string
	{
		return hash_hmac('sha1', $data, $this->get_salt());
	}

	/**
	 * Get HTTP cookie value.
	 *
	 * @param WP_User $user        Currently logged in user.
	 * @param string  $cookie_name Cookie name.
	 *
	 * @return string
	 */
	private function get_cookie_value($user_id, $hydro_id, string $cookie_name) : string
	{
		$salt = $this->get_salt();
		$user_hash = sha1(md5($user_id));
		$expire = strtotime('+24 hours');
		$value = base64_encode(sprintf('%s|%s|%s|%s', $cookie_name, $user_hash, $hydro_id, $expire));
		$signature = $this->hash_hmac($value);
		return $value . '|' . $signature;
	}

	/**
	 * Set cookie for current user.
	 *
	 * @param WP_User $user Current logged in user.
	 */
	private function set_cookie($user_id, $hydro_id)
	{
		$app = JFactory::getApplication();
		$cookie = $this->get_cookie_value($user_id, $hydro_id, self::COOKIE_NAME);
		// @codingStandardsIgnoreLine
		$result = setcookie(self::COOKIE_NAME, $cookie, 0, $app->get('cookie_path', '/'), $app->get('cookie_domain'), $app->isSSLConnection());
		if (!$result) {
			$this->log('Could not set cookie.');
		}
	}

	/**
	 * Verify Hydro Raindrop MFA cookie.
	 *
	 * @param WP_User $user Verify cookie for current user.
	 *
	 * @return bool
	 * @throws \Hashids\HashidsException When hashing fails.
	 */
	private function verify_cookie($userId, $hydroId) : bool
	{
		// @codingStandardsIgnoreLine
		if (!isset($_COOKIE[self::COOKIE_NAME])) {
			//$this->log('Cookie is not set.');
			return false;
		}
		// @codingStandardsIgnoreLine
		$cookie_list = explode('|', $_COOKIE[self::COOKIE_NAME]);
		if (count($cookie_list) !== 2) {
			$this->log('Cookie contents are not valid (2).');
			return false;
		}
		// @codingStandardsIgnoreLine
		list($b64_value, $cookie_signature) = $cookie_list;
		$signature = $this->hash_hmac($b64_value);
		if ($this->hash_hmac($signature) !== $this->hash_hmac($cookie_signature)) {
			$this->log('Cookie signature invalid.');
			return false;
		}
		// @codingStandardsIgnoreLine
		$cookie_content = explode('|', base64_decode($b64_value));
		if (count($cookie_content) !== 4) {
			$this->log('Cookie contents are not valid (4).');
			return false;
		}
		list($name, $user_id, $hydro_id, $expire) = $cookie_content;
		//$user_hash = ( new \Hashids\Hashids( $this->get_salt(), 64 ) )->decode( $userId );
		$is_valid = self::COOKIE_NAME === $name
			|| $userId === $user_id
			|| $hydro_id === $hydroId;
		if (!$is_valid) {
			$this->log('Cookie data invalid.');
			return false;
		}
		// Cookie expired.
		if ((int)$expire < time()) {
			$this->log('Cookie is expired.');
			return false;
		}
		return true;
	}

	/**
	 * Unset the Hydro Raindrop MFA cookie
	 *
	 * @return void
	 */
	public function unset_cookie() {
		$app = JFactory::getApplication();
		setcookie(self::COOKIE_NAME, '', strtotime('-1 day'), $app->get('cookie_path', '/'), $app->get('cookie_domain'), $app->isSSLConnection());
	}

	private function verifyMessage(string $hydro_id, string $message) : bool
	{
		try {
			$data = self::$client->verifySignature($hydro_id, $message);
			if ($data)
				return true;
		} catch (VerifySignatureFailed $e) {
			$this->log($e->getMessage());
		}
		return false;
	}

	public function onAjaxVerifySignatureLogin()
	{
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$hydro_id = $session->get('id', null, 'hydro_raindrop');
		$message = $this->get_message();

		if ($this->verifyMessage($hydro_id, $message)) {
			$this->set_cookie($user->id, $hydro_id);
			$updates = (object) array(
				'id'     => $user->id,
				'otpKey' => $this->methodName . ':' . json_encode(array(
					'hydro_id' => $hydro_id,
					'hydro_raindrop_confirmed' => 1
				))
			);
			JFactory::getDbo()->updateObject('#__users', $updates, 'id');
			return true;
		}
		return false;
	}

	/**
	 * Get the Raindrop MFA message.
	 *
	 * @return int
	 * @throws Exception When message could not be generated.
	 */
	private function get_message() : int
	{
		$session = JFactory::getSession();
		$message = $session->get('message', null, 'hydro_raindrop');
		if (!$message) {
			return $this->set_message();
		}
		return (int)$message;
	}

	/**
	 * Set the Raindrop MFA message.
	 *
	 * @return int
	 * @throws Exception When message could not be generated.
	 */
	private function set_message() : int
	{
		$session = JFactory::getSession();
		$message = self::$client->generateMessage();
		$session->set('message', $message, 'hydro_raindrop');
		return (int)$message;
	}

	private function show_mfa() {
        $app = JFactory::getApplication();
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$just_logged_in = $session->get('reauthenticate', false, 'hydro_raindrop');
		$hydro_id = $session->get('id', null, 'hydro_raindrop');
		$hydro_raindrop_confirmed = $session->get('confirmed', false, 'hydro_raindrop');
		$show_fma = false;
		if (!$user->guest && $just_logged_in && $hydro_id && $hydro_raindrop_confirmed) {
			$show_fma = true;
			$this->unset_cookie();
		}
		//var_dump($just_logged_in, $hydro_id, $hydro_raindrop_confirmed, $show_fma);
		//if ($this->verify_cookie($user->id, $hydro_id)) {
			//$show_fma = false;
		//}
		if ($show_fma) {
			$document = JFactory::getDocument();
			$input = $app->input;

			$error = null;
			$message = $this->get_message();

			if ($input->get('hydro_raindrop')) {
				if (!$this->verifyMessage($hydro_id, $message)) {
					$message = $this->set_message();
					$this->log('PLG_TWOFACTORAUTH_HYDRORAINDROP_ERR_VALIDATIONFAILED');
				} else {
					$this->log('2FA login complete', 'success');
					$session->clear('reauthenticate', 'hydro_raindrop');
					$this->set_cookie($user->id, $hydro_id);
					return;
				}
			}
			
			// Include the form.php from a template override. If none is found use the default.
			$path = FOFPlatform::getInstance()->getTemplateOverridePath('plg_twofactorauth_hydroraindrop', true);
			// Start output buffering
			@ob_start();

			$layout = new JLayoutFile('mfa', __DIR__ . '/layouts', array('debug' => false, 'client' => 1, 'component' => 'com_login'));
			echo $layout->render($this->view_data($message));

			// Stop output buffering and get the form contents
			$html = @ob_get_clean();

			JHtml::_('jquery.framework');
			//$document->setBuffer(array('menu' => false), 'modules');
			$document->setBuffer($html, 'component');
			$document->addStyleSheet('/plugins/twofactorauth/hydroraindrop/hydro-raindrop-public.css');
			//$document->addScript('/plugins/twofactorauth/hydroraindrop/hydro-raindrop-public.js');

			echo $document->render(false, array(
				'template' => $app->getTemplate(),
				'file' => 'component.php'
			));

			$app->close();
		}
    }
}
