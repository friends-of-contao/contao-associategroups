<?php

try {
	$objDB = Database::getInstance();
	$objAssocGroups = new AssociateGroups();
	$objDB->tableExists('tl_member_to_group') || $objAssocGroups->syncAssociationTable('member');
	$objDB->tableExists('tl_user_to_group') || $objAssocGroups->syncAssociationTable('user');

} catch(Exception $e) {
	$this->log($e->getMessage(), TL_ERROR);
}
