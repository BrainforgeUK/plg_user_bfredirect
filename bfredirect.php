<?php
/**
 * @package   Login redirection by BrainforgeUK
 * @author    https://www.brainforge.co.uk
 * @copyright (C) 2020-2026 Jonathan Brain. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Event\User;

defined('_JEXEC') or die;

class PlgUserBfredirect extends JPlugin
{
	protected $userRequiresReset = false;

	/**
	 *
	 */
	public function onUserAfterLogin($options = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient('site'))
		{
			if (!empty($options['user']) && $thisUser = $options['user'])
			{
				if ($thisUser->requireReset) return;

				$this->redirect($thisUser);
			}
		}
	}

	/*
	 */
	public function onUserBeforeSave(User\BeforeSaveEvent $event): void
	{
		if (!Factory::getApplication()->isClient('site')) return;

		$eventUser = $event->getUser();

		$this->userRequiresReset = !empty($eventUser['requireReset']);
	}

	/*
	 */
	public function onUserAfterSave(User\AfterSaveEvent $event): void
	{
		if (!$this->userRequiresReset) return;

		$eventUser = $event->getUser();

		if (!empty($eventUser['requireReset'])) return;

		$user = Factory::getApplication()->getIdentity();
		if ($user->id != $eventUser['id']) return;

		$this->redirect($user);
	}

	/*
	 */
	protected function redirect($thisUser)
	{
		if (empty($menuitemid))
		{
			$menuitemid = $this->getUserMenuItem($thisUser);
		}

		if (empty($menuitemid) && !empty($thisUser->groups))
		{
			$menuitemid = $this->getGroupMenuItem($thisUser);
		}

		if (!empty($menuitemid))
		{
			$link = Route::_('index.php?Itemid=' . $menuitemid);
			if (!empty($link))
			{
				Factory::getApplication()->redirect(Route::_($link));
			}
		}
	}

	/**
	 *
	 */
	protected function getUserMenuItem($thisUser)
	{
		$users = (array) $this->params->get('users');
		if (!empty($users))
		{
			foreach ($users as $user)
			{
				if ($user->user == $thisUser->id)
				{
					return $user->menuitemid;
				}
			}
		}

		return null;
	}

	/**
	 *
	 */
	protected function getGroupMenuItem($thisUser)
	{
		$groups = (array) $this->params->get('groups');
		foreach ($groups as $group)
		{
			if (array_intersect($thisUser->groups, $group->group))
			{
				return $group->menuitemid;
			}
		}

		return null;
	}
}