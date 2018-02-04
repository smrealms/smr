<div align="center">
	<table width="60%" class="standard">
		<tr>
			<th valign="top" width="50%">Relations (Global)</th>
			<th valign="top" width="50%">Relations (Personal)</th>
		</tr>
		<tr>
			<td valign="top" width="50%">
				<p><?php
					foreach ($PoliticalRelations as $raceName => $relation) {
						print($raceName . ' : ' . get_colored_text($relation, $relation));
						?><br /><?php
					} ?>
				</p>
			</td>
			<td valign="top">
				<p><?php
					foreach ($PersonalRelations as $raceName => $relation) {
						print($raceName . ' : ' . get_colored_text($relation, $relation));
						?><br /><?php
					} ?>
				</p>
			</td>
		</tr>
	</table>
</div>
