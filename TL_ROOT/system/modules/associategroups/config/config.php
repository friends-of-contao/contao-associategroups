<?php

$GLOBALS['TL_HOOKS']['createNewUser'][] = array('AssociateGroups', 'createNewUser');

$GLOBALS['BE_MOD']['accounts']['member']['sync_tl_member_to_group']
	= array('AssociateGroups', 'syncMemberToGroup');
$GLOBALS['BE_MOD']['accounts']['user']['sync_tl_user_to_group']
	= array('AssociateGroups', 'syncUserToGroup');

TL_MODE == 'BE' && $GLOBALS['TL_CSS'][] = 'system/modules/associategroups/html/backend.css';
