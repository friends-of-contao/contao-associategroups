<?php

$GLOBALS['TL_DCA']['tl_user']['config']['ondelete_callback'][] = array('AssociateGroups', 'deleteGroups');
$GLOBALS['TL_DCA']['tl_user']['config']['onsubmit_callback'][] = array('AssociateGroups', 'submitGroups');

$GLOBALS['TL_DCA']['tl_user']['list']['global_operations']['sync_associate_groups'] = array(
	'label'				=> &$GLOBALS['TL_LANG']['tl_user']['sync_associate_groups'],
	'href'				=> 'key=sync_tl_user_to_group',
	'class'				=> 'sync_associate_groups header_sync_all',
	'attributes'		=> 'onclick="Backend.getScrollOffset();"',
);
