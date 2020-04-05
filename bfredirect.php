<?php
/**
 * @package   Login redirection by BrainforgeUK
 * @author    https://www.brainforge.co.uk
 * @copyright (C) 2020 Jonathan Brain. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class PlgUserBfredirect extends JPlugin
{
	/**
	 *
	 */
	public function onUserAfterLogin($options = array())
	{
		if (JFactory::getApplication()->isClient('site'))
		{
			if (!empty($options['user']) && $thisUser = $options['user'])
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
					$link = JRoute::_('index.php?Itemid='.$menuitemid);
					if (!empty($link))
					{
						JFactory::getApplication()->redirect(JRoute::_($link));
					}
				}
			}
		}
	}

	/**
	 *
	 */
	protected function getUserMenuItem($thisUser)
	{
		$users = (array)$this->params->get('users');
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
		$groups = (array)$this->params->get('groups');
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
