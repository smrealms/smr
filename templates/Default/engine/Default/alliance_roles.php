<h2>Current Roles</h2><br /><?php
foreach($AllianceRoles as $RoleID => $Role) {
	$this->includeTemplate('includes/AllianceRole.inc',array('Role' => $Role));
} ?><br />
<h2>Create Role</h2><br /><?php
$this->includeTemplate('includes/AllianceRole.inc',array('Role' => $CreateRole)); ?>
<b>Usage:</b><br />
To add a new entry input the name of the role in the name field and press 'Create'.<br />
To delete an entry clear the box and click 'Edit'.
