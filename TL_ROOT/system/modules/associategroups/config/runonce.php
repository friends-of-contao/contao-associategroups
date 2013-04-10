<?php

class AssociateGroupsRunonce extends Frontend
{

	/**
	 * Initialize the object
	 */
	public function __construct()
	{
		parent::__construct();

		$this->import('Database');
	}


	/**
	 * Run the controller
	 */
	public function run()
	{
		$time = time();

		if (!$this->Database->tableExists('tl_member_to_group'))
		{
			$this->Database->query("CREATE TABLE `tl_member_to_group` (
									  `id` int(10) unsigned NOT NULL auto_increment,
									  `tstamp` int(10) unsigned NOT NULL default '0',
									  `member_id` int(10) unsigned NOT NULL default '0',
									  `group_id` int(10) unsigned NOT NULL default '0',
									  PRIMARY KEY  (`id`)
									) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

			$objMembers = $this->Database->execute("SELECT id,groups FROM tl_member WHERE groups!=''");

			while( $objMembers->next() )
			{
				$arrGroups = deserialize($objMembers->groups);

				if (is_array($arrGroups) && count($arrGroups))
				{
					$this->Database->query("INSERT INTO tl_member_to_group (tstamp,member_id,group_id) VALUES ($time,{$objMembers->id}," . implode("), ($time,{$objMembers->id},", $arrGroups) . ")");
				}
			}
		}


		if (!$this->Database->tableExists('tl_user_to_group'))
		{
			$this->Database->query("CREATE TABLE `tl_user_to_group` (
									  `id` int(10) unsigned NOT NULL auto_increment,
									  `tstamp` int(10) unsigned NOT NULL default '0',
									  `user_id` int(10) unsigned NOT NULL default '0',
									  `group_id` int(10) unsigned NOT NULL default '0',
									  PRIMARY KEY  (`id`)
									) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

			$objUsers = $this->Database->execute("SELECT id,groups FROM tl_user WHERE groups!=''");

			while( $objUsers->next() )
			{
				$arrGroups = deserialize($objUsers->groups);

				if (is_array($arrGroups) && count($arrGroups))
				{
					$this->Database->query("INSERT INTO tl_user_to_group (tstamp,user_id,group_id) VALUES ($time,{$objUsers->id}," . implode("), ($time,{$objUsers->id},", $arrGroups) . ")");
				}
			}
		}
	}
}

$objAssociateGroupsRunonce = new AssociateGroupsRunonce();
$objAssociateGroupsRunonce->run();
