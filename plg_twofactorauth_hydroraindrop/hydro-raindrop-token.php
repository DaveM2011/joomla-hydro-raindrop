<?php
declare (strict_types = 1);
use Adrenth\Raindrop\ApiAccessToken;
use Adrenth\Raindrop\Exception\UnableToAcquireAccessToken;
use Adrenth\Raindrop\TokenStorage\TokenStorage;

/**
 * Class Hydro_Raindrop_TokenStorage
 *
 */
final class Hydro_Raindrop_TokenStorage implements TokenStorage
{

	/**
	 * @return ApiAccessToken
	 * @throws UnableToAcquireAccessToken
	 */
	public function getAccessToken() : ApiAccessToken
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT profile_key, profile_value FROM #__user_profiles'
				. ' WHERE user_id = ' . (int)$user->id . " AND profile_key LIKE 'profile.HydroRaindropToken'"
				. ' ORDER BY ordering'
		);

		try {
			$result = $db->loadObject();
			if ($result) {
				if (is_string($result->profile_value) && substr_count($result->profile_value, '|') === 1) {
					$data = explode('|', $result->profile_value);
					return ApiAccessToken::create($data[0] ?? '', (int)($data[1] ?? 0));
				}
			}
		} catch (RuntimeException $e) {
			$this->_subject->setError($e->getMessage());
			return null;
		}
		throw new UnableToAcquireAccessToken('Access Token is not found in the storage.');
	}

	/**
	 * @param ApiAccessToken $token
	 * @return void
	 */
	public function setAccessToken(ApiAccessToken $token)
	{
		$this->unsetAccessToken();
		$user = JFactory::getUser();
		$profile = new stdClass();
		$profile->user_id = $user->id;
		$profile->profile_key = 'profile.HydroRaindropToken';
		$profile->profile_value = $token->getToken() . '|' . $token->getExpiresAt();
		$profile->ordering = 1;
		// Insert the object into the user profile table.
		JFactory::getDbo()->insertObject('#__user_profiles', $profile);
	}

	/**
	 * @return void
	 */
	public function unsetAccessToken()
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$conditions = array(
			$db->quoteName('user_id') . ' = ' . $user->id,
			$db->quoteName('profile_key') . ' = ' . $db->quote('profile.HydroRaindropToken')
		);
		$query->delete($db->quoteName('#__user_profiles'));
		$query->where($conditions);
		$db->setQuery($query);
		$db->execute();
	}
}
