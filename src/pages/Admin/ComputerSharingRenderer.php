<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class ComputerSharingRenderer {

/**
 * @param list<list<array{name: string, account_id: int, associated_ids: string, style: string, color: string, common_ip: string, last_login: string, suspicion: string, exception: string, email: string}>> $Tables
 */
public static function render(array $Tables, string $CloseHREF): void {
?>
<form method="POST" action="<?php echo $CloseHREF; ?>">
	<?php
	foreach ($Tables as $Rows) { ?>
		<table class="standard">
			<tr>
				<th class="center">Accounts</th>
				<th>Email</th><th>Most Common IP</th>
				<th>Last Login</th>
				<th>Exception</th>
				<th>Closed</th>
				<th>Option</th>
			</tr><?php
			foreach ($Rows as $Row) { ?>
				<tr class="<?php echo $Row['color']; ?>">
					<td><?php echo $Row['name']; ?></td>
					<td style="<?php echo $Row['style']; ?>"><?php echo $Row['email']; ?></td>
					<td><?php echo $Row['common_ip']; ?></td>
					<td><?php echo $Row['last_login']; ?></td>
					<td><?php echo $Row['exception']; ?></td>
					<td><?php echo $Row['suspicion']; ?></td>
					<td><input type="checkbox" name="close[<?php echo $Row['account_id']; ?>]" value="<?php echo $Row['associated_ids']; ?>"></td>
				</tr><?php
			} ?>
		</table>
		<br /><?php
	}
	echo create_submit_display('Close Accounts'); ?>
</form>

<?php
}

}
