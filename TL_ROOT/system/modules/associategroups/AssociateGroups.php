<?php

class AssociateGroups extends Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->import('Database');
	}

	/**
	 * Save member groups to the association table
	 * @param	object	$dc
	 * @return	mixed
	 * @link	http://www.contao.org/callbacks.html onsubmit_callback
	 */
	public function submitGroups($dc)
	{
		$strField = substr($dc->table, 3);
		$arrGroups = array_filter(array_unique(array_map('intval', deserialize($dc->activeRecord->groups, true))));

		if (!$arrGroups)
		{
			$this->Database->query("DELETE FROM {$dc->table}_to_group WHERE {$strField}_id={$dc->id}");
		}
		else
		{
			$arrAssociations = $this->Database->execute("SELECT group_id FROM {$dc->table}_to_group WHERE {$strField}_id={$dc->id}")->fetchEach('group_id');

			$arrDelete = array_diff($arrAssociations, $arrGroups);
			$arrInsert = array_diff($arrGroups, $arrAssociations);

			if (count($arrDelete) > 0)
			{
				$this->Database->query("DELETE FROM {$dc->table}_to_group WHERE {$strField}_id={$dc->id} AND group_id IN (" . implode(',', $arrDelete) . ")");
			}

			if (count($arrInsert) > 0)
			{
				$time = time();
				$this->Database->query("INSERT INTO {$dc->table}_to_group (tstamp,{$strField}_id,group_id) VALUES ($time,{$dc->id}," . implode("), ($time,{$dc->id},", $arrInsert) . ")");
			}
		}

		return $varValue;
	}

	/**
	 * Delete groups when member/user is deleted
	 * @param	object	$dc	DataContainer
	 * @return	void
	 * @link	http://www.contao.org/callbacks.html ondelete_callback
	 */
	public function deleteGroups($dc)
	{
		$strField = substr($dc->table, 3);
		$this->Database->execute("DELETE FROM {$dc->table}_to_group WHERE {$strField}_id=" . (int)$dc->activeRecord->id);
	}


	/**
	 * Add groups for a new member
	 * @param	int		$intId
	 * @param	array	$arrData
	 * @return	void
	 * @link	http://www.contao.org/hooks.html#createNewUser
	 */
	public function createNewUser($intId, $arrData)
	{
		$arrGroups = deserialize($arrData['groups']);

		if (is_array($arrGroups) && count($arrGroups))
		{
			$time = time();
			$this->Database->execute("INSERT INTO tl_member_to_group (tstamp,member_id,group_id) VALUES ($time, $intId, " . implode("), ($time, $intId, ", array_map('intval', $arrGroups)) . ")");
		}
	}

	/**
	 * Delete tl_member_to_group and create new
	 * @return	void
	 */
	public function syncMemberToGroup()
	{
		if ($this->Database->tableExists('tl_member_to_group'))
		{
			$this->Database->execute("TRUNCATE tl_member_to_group");
		}
		else
		{
      $this->Database->query("CREATE TABLE `tl_member_to_group` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `tstamp` int(10) unsigned NOT NULL default '0',
                                `member_id` int(10) unsigned NOT NULL default '0',
                                `group_id` int(10) unsigned NOT NULL default '0',
                                PRIMARY KEY  (`id`)
                              ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		}

    $time = time();
		$objMembers = $this->Database->execute("SELECT id,groups FROM tl_member WHERE groups!='' ORDER BY id ASC");

		while( $objMembers->next() )
		{
			$arrGroups = deserialize($objMembers->groups);

			if (is_array($arrGroups) && count($arrGroups))
			{
				$this->Database->query("INSERT INTO tl_member_to_group (tstamp,member_id,group_id) VALUES ($time,{$objMembers->id}," . implode("), ($time,{$objMembers->id},", $arrGroups) . ")");
			}
		}

		$this->redirect($this->Environment->script . '?do=member');
	}

	/**
	 * Delete tl_user_to_group and create new
	 * @return	void
	 */
	public function syncUserToGroup()
	{
		if ($this->Database->tableExists('tl_user_to_group'))
		{
			$this->Database->execute("TRUNCATE tl_user_to_group");
		}
    else
    {
      $this->Database->query("CREATE TABLE `tl_user_to_group` (
                                `id` int(10) unsigned NOT NULL auto_increment,
                                `tstamp` int(10) unsigned NOT NULL default '0',
                                `user_id` int(10) unsigned NOT NULL default '0',
                                `group_id` int(10) unsigned NOT NULL default '0',
                                PRIMARY KEY  (`id`)
                              ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    $time = time();
		$objUsers = $this->Database->execute("SELECT id,groups FROM tl_user WHERE groups!='' ORDER BY id ASC");

		while( $objUsers->next() )
		{
			$arrGroups = deserialize($objUsers->groups);

			if (is_array($arrGroups) && count($arrGroups))
			{
				$this->Database->query("INSERT INTO tl_user_to_group (tstamp,user_id,group_id) VALUES ($time,{$objUsers->id}," . implode("), ($time,{$objUsers->id},", $arrGroups) . ")");
			}
		}

		$this->redirect($this->Environment->script . '?do=user');
	}
}

