
<table>
    <tr>
        <th>Rank</th>
        <th>Experience</th>
        <th>Kills</th>
        <th>Operations</th>
        <th>Utilities</th>
        <th>Move</th>
    </tr>
    <?php if(!empty($Rankings)){
        foreach($Rankings as $r){ ?>
            <tr>
                 <td><?php echo $r['label'] ?></td>
                <td><?php echo $r['experience'] ?></td>
                <td><?php echo $r['kills'] ?></td>
                <td><?php echo $r['operation'] ?></td>
                <td><?php echo $r['utility'] ?></td>
                <td><a href="<?php echo $Processor ?>&rankingId=<?php echo $r['rankingId']?>&action=up">Up</a> ,
                    <a href="<?php echo $Processor ?>&rankingId=<?php echo $r['rankingId']?>&action=down">Down</a> ,
                    <a href="<?php echo $Processor ?>&rankingId=<?php echo $r['rankingId']?>&action=delete">Delete</a>
                </td>
            </tr>

        <?php    }
    } ?>
</table>
<br/>
<br/>


<h1>Add new rank</h1>

<form action="<?php echo $Processor ?>" method="POST">
    <input type="hidden" name="rankId" value="0"/>
    <input type="hidden" name="action" value="add"/>
    <table>
        <tr><th>Label</th><td><input type="text" size="30" name="label"></td></tr>
        <tr><th>Experience</th><td><input type="text" size="6" name="experience"></td></tr>
        <tr><th>Kills</th><td><input type="text" size="6" name="kills"></td></tr>
        <tr><th>Operations</th><td><input type="text" size="6" name="operation"></td></tr>
        <tr><th>Utilities</th><td><input type="text" size="6" name="utility"></td></tr>
        <tr><td colspan="2"><input type="submit" value="add"/> </td> </tr>
    </table>

</form>