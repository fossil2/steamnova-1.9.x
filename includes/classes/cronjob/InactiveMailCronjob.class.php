<?php

/**
 *  2Moons
 *   by Jan-Otto Kröpke 2009-2016
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package 2Moons
 * @author Jan-Otto Kröpke <slaver7@gmail.com>
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke
 * @licence MIT
 * @version 1.8.x
 */

require_once 'includes/classes/cronjob/CronjobTask.interface.php';

class InactiveMailCronjob implements CronjobTask
{
	function run()
	{
		global $LNG;

		$config = Config::get(ROOT_UNI);

		if($config->mail_active != 1)
		{
			return;
		}

		/** @var Language[] $langObjects */
		$langObjects = array();

		require_once 'includes/classes/Mail.class.php';

		$sql = 'SELECT
					`id`,
					`username`,
					`lang`,
					`email`,
					`onlinetime`,
					`timezone`,
					`universe`,
					`inactive_mail`
				FROM %%USERS%%
				WHERE `inactive_mail` < 3
				AND `authlevel` = :authlevel;';

		$inactiveUsers = Database::get()->select($sql, array(
			':authlevel' => AUTH_USR
		));

		foreach($inactiveUsers as $user)
		{
			$userConfig = Config::get($user['universe']);

			$inactiveDays = (int) floor(
				(TIMESTAMP - (int) $user['onlinetime']) / 86400
			);

			$currentStage = (int) $user['inactive_mail'];
			$stage = 0;

			/*
			 * Stufe 1:
			 * normale Erinnerungsfrist aus der Serverkonfiguration.
			 * Bei dir aktuell 8 Tage.
			 */
			if($inactiveDays >= (int) $userConfig->del_user_sendmail)
			{
				$stage = 1;
			}

			/*
			 * Stufe 2:
			 * nach 21 Tagen Inaktivität.
			 */
			if($inactiveDays >= 21)
			{
				$stage = 2;
			}

			/*
			 * Stufe 3:
			 * 7 Tage vor der automatischen Löschung.
			 *
			 * Bei deaktivierter automatischer Löschung
			 * (del_user_automatic = 0) gibt es keine Stufe 3.
			 */
			if(
				(int) $userConfig->del_user_automatic > 7
				&& $inactiveDays >= ((int) $userConfig->del_user_automatic - 7)
			)
			{
				$stage = 3;
			}

			/*
			 * Keine neue Stufe erreicht.
			 */
			if($stage === 0 || $stage <= $currentStage)
			{
				continue;
			}

			/*
			 * Sprache des Benutzers laden.
			 */
			if(!isset($langObjects[$user['lang']]))
			{
				$langObjects[$user['lang']] = new Language($user['lang']);
				$langObjects[$user['lang']]->includeData(array(
					'L18N',
					'INGAME',
					'PUBLIC',
					'CUSTOM'
				));
			}

			$LNG = $langObjects[$user['lang']];

			$subjectKey  = 'spec_mail_inactive_title_'.$stage;
			$templateName = 'email_inactive_'.$stage;

			/*
			 * Betreff der jeweiligen Stufe.
			 */
			$MailSubject = sprintf(
				$LNG[$subjectKey],
				$userConfig->game_name.' - '.$userConfig->uni_name
			);

			/*
			 * Mail-Template der jeweiligen Stufe.
			 */
			$MailRAW = $LNG->getTemplate($templateName);

			$MailContent = str_replace(array(
				'{USERNAME}',
				'{GAMENAME}',
				'{LASTDATE}',
				'{HTTPPATH}',
			), array(
				$user['username'],
				$userConfig->game_name.' - '.$userConfig->uni_name,
				_date(
					$LNG['php_tdformat'],
					$user['onlinetime'],
					$user['timezone']
				),
				HTTP_PATH,
			), $MailRAW);

			/*
			 * Mail senden.
			 */
			Mail::send(
				$user['email'],
				$user['username'],
				$MailSubject,
				$MailContent
			);

			/*
			 * Speichern, welche Stufe bereits verschickt wurde.
			 */
			$sql = 'UPDATE %%USERS%%
					SET `inactive_mail` = :stage
					WHERE `id` = :userId;';

			Database::get()->update($sql, array(
				':stage'	=> $stage,
				':userId'	=> $user['id']
			));
		}
	}
}