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
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @licence MIT
 * @version 1.8.x Koray Karakuş <koraykarakus@yahoo.com>
 * @link https://github.com/jkroepke/2Moons
 */

require_once 'includes/classes/cronjob/CronjobTask.interface.php';

class CleanerCronjob implements CronjobTask
{
	function run()
	{
		global $LNG;

		$config = Config::get(ROOT_UNI);

		$unis = Universe::availableUniverses();

		// Delete old messages
		$del_before   = TIMESTAMP - ($config->del_oldstuff * 86400);
		$del_deleted  = TIMESTAMP - ($config->del_user_manually * 86400);
		$del_messages = TIMESTAMP - ($config->message_delete_days * 86400);

		/*
		 * Automatische Benutzerlöschung.
		 * del_user_automatic = 0 bedeutet: deaktiviert.
		 */
		if((int) $config->del_user_automatic > 0)
		{
			$del_inactive = TIMESTAMP - ((int) $config->del_user_automatic * 86400);
		}
		else
		{
			$del_inactive = 0;
		}

		$sql = 'DELETE FROM %%MESSAGES%% WHERE `message_time` < :time;';
		Database::get()->delete($sql, array(
			':time' => $del_before
		));

		$sql = 'DELETE FROM %%ALLIANCE%% WHERE `ally_members` = 0;';
		Database::get()->delete($sql);

		$sql = 'DELETE FROM %%PLANETS%%
				WHERE `destruyed` < :time
				AND `destruyed` != 0;';

		Database::get()->delete($sql, array(
			':time' => TIMESTAMP
		));

		$sql = 'DELETE FROM %%SESSION%% WHERE `lastonline` < :time;';
		Database::get()->delete($sql, array(
			':time' => TIMESTAMP - SESSION_LIFETIME
		));

		$sql = 'DELETE FROM %%FLEETS_EVENT%%
				WHERE fleetID NOT IN (
					SELECT fleet_id FROM %%FLEETS%%
				);';

		Database::get()->delete($sql);

		$sql = 'DELETE FROM %%LOG_FLEETS%% WHERE `start_time` < :time;';
		Database::get()->delete($sql, array(
			':time' => $del_before
		));

		$sql = 'UPDATE %%USERS%%
				SET `email_2` = `email`
				WHERE `setmail` < :time;';

		Database::get()->update($sql, array(
			':time' => TIMESTAMP
		));

		/*
		 * Benutzer auswählen, die gelöscht werden sollen.
		 *
		 * Möglichkeit 1:
		 * Spieler hat seine Löschung selbst beantragt.
		 *
		 * Möglichkeit 2:
		 * Spieler ist länger als del_user_automatic inaktiv.
		 */
		$sql = 'SELECT
					`id`,
					`username`,
					`lang`,
					`email`,
					`onlinetime`,
					`timezone`,
					`universe`,
					`inactive_mail`,
					`db_deaktjava`
				FROM %%USERS%%
				WHERE `authlevel` = :authlevel
				AND (
					(
						`db_deaktjava` != 0
						AND `db_deaktjava` < :timeDeleted
					)
					OR
					(
						:automaticDeletion > 0
						AND `onlinetime` < :timeInactive
					)
				);';

		$deleteUsers = Database::get()->select($sql, array(
			':authlevel'         => AUTH_USR,
			':timeDeleted'       => $del_deleted,
			':automaticDeletion' => (int) $config->del_user_automatic,
			':timeInactive'      => $del_inactive
		));

		if(!empty($deleteUsers))
		{
			$langObjects = array();

			if($config->mail_active == 1)
			{
				require_once 'includes/classes/Mail.class.php';
			}

			foreach($deleteUsers as $dataRow)
			{
				/*
				 * Wurde der Account vom Spieler selbst zur Löschung
				 * vorgemerkt und ist diese Frist abgelaufen?
				 */
				$isManualDeletion =
					(int) $dataRow['db_deaktjava'] != 0
					&& (int) $dataRow['db_deaktjava'] < $del_deleted;

				/*
				 * Automatische Löschung wegen Inaktivität.
				 */
				$isInactiveDeletion =
					(int) $config->del_user_automatic > 0
					&& (int) $dataRow['onlinetime'] < $del_inactive;

				/*
				 * Stufe 4:
				 * Nur bei automatischer Löschung wegen Inaktivität.
				 *
				 * Eine selbst beantragte Account-Löschung bekommt
				 * diese Mail ausdrücklich nicht.
				 */
				if(
					$isInactiveDeletion
					&& !$isManualDeletion
					&& $config->mail_active == 1
					&& (int) $dataRow['inactive_mail'] < 4
					&& !empty($dataRow['email'])
				)
				{
					try
					{
						if(!isset($langObjects[$dataRow['lang']]))
						{
							$langObjects[$dataRow['lang']] = new Language($dataRow['lang']);

							$langObjects[$dataRow['lang']]->includeData(array(
								'L18N',
								'INGAME',
								'PUBLIC',
								'CUSTOM'
							));
						}

						$LNG = $langObjects[$dataRow['lang']];

						$userConfig = Config::get($dataRow['universe']);

						$MailSubject = sprintf(
							$LNG['spec_mail_inactive_title_4'],
							$userConfig->game_name.' - '.$userConfig->uni_name
						);

						$MailRAW = $LNG->getTemplate('email_inactive_4');

						$MailContent = str_replace(array(
							'{USERNAME}',
							'{GAMENAME}',
							'{LASTDATE}',
							'{HTTPPATH}',
						), array(
							$dataRow['username'],
							$userConfig->game_name.' - '.$userConfig->uni_name,
							_date(
								$LNG['php_tdformat'],
								$dataRow['onlinetime'],
								$dataRow['timezone']
							),
							HTTP_PATH,
						), $MailRAW);

						Mail::send(
							$dataRow['email'],
							$dataRow['username'],
							$MailSubject,
							$MailContent
						);

						/*
						 * Stufe 4 markieren.
						 * Falls deletePlayer wider Erwarten fehlschlägt,
						 * wird die Abschiedsmail nicht erneut verschickt.
						 */
						$sql = 'UPDATE %%USERS%%
								SET `inactive_mail` = 4
								WHERE `id` = :userId;';

						Database::get()->update($sql, array(
							':userId' => $dataRow['id']
						));
					}
					catch(Throwable $e)
					{
						/*
						 * Ein Fehler beim E-Mail-Versand darf die
						 * vorgesehene Account-Löschung nicht verhindern.
						 */
					}
				}

				PlayerUtil::deletePlayer($dataRow['id']);
			}
		}

		// do not delete hall of fame
		// foreach($unis as $uni)
		// {
			// $sql = 'SELECT units FROM %%TOPKB%%
			// WHERE `universe` = :universe
			// ORDER BY units DESC LIMIT 99,1;';

			// $battleHallLowest = Database::get()->selectSingle($sql, array(
				// ':universe' => $uni
			// ), 'units');

			// if(!is_null($battleHallLowest))
			// {
				// $sql = 'DELETE %%TOPKB%%, %%TOPKB_USERS%%
				// FROM %%TOPKB%%
				// INNER JOIN %%TOPKB_USERS%% USING (rid)
				// WHERE `universe` = :universe
				// AND `units` < :battleHallLowest;';

				// Database::get()->delete($sql, array(
					// ':universe' => $uni,
					// ':battleHallLowest' => $battleHallLowest
				// ));
			// }
		// }

		// do not delete combat reports
		// $sql = 'DELETE FROM %%RW%%
		// WHERE `time` < :time
		// AND `rid` NOT IN (SELECT `rid` FROM %%TOPKB%%);';

		// Database::get()->delete($sql, array(
			// ':time' => $del_before
		// ));

		$sql = 'DELETE FROM %%MESSAGES%%
				WHERE `message_deleted` < :time;';

		Database::get()->delete($sql, array(
			':time' => $del_messages
		));
	}
}