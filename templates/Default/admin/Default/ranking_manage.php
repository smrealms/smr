

<table>
    <tr>
        <th>Rank</th>
        <th>Experience</th>
        <th>Kills</th>
        <th>Operations</th>
        <th>Utilities</th>
    </tr>
</table>



<h1>Add new rank</h1>

<form action="${ManageRank}" method="POST">
    <input type="hidden" name="rankId" value="0"/>
    <table>
        <tr><th>Experience</th><td><input type="text" size="6" name="experience"></td></tr>
        <tr><th>Kills</th><td><input type="text" size="6" name="kills"></td></tr>
        <tr><th>Operations</th><td><input type="text" size="6" name="operations"></td></tr>
        <tr><th>Utilities</th><td><input type="text" size="6" name="utilities"></td></tr>
        <tr><td colspan="2"><input type="submit" value="add"/> </td> </tr>
    </table>



</form>