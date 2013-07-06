
<h1 align="center">Hall of Fame</h1>



<table border="1">
    <tr>
        <th>Position</a></th>
        <th>Ranking</th>
        <th><a href="<?php echo $OrderLinks['LoginHref'] ?>">Account</a></th>
        <th><a href="<?php echo $OrderLinks['ExperienceHref'] ?>">Experience</a></th>
        <th><a href="<?php echo $OrderLinks['OperationHref'] ?>">Operation</a></th>
        <th><a href="<?php echo $OrderLinks['KillsHref'] ?>">Kills</th>
        <th>Utilities</th>
    </tr>

    <?php foreach ($UpperHof as $pos => $hof) { ?>
    <tr>
        <td><?php if(isset($hof['ranking'])) echo $pos+1 ?></td>
        <td><?php if(isset($hof['ranking'])) echo $hof['ranking'] ?></td>
        <td><?php echo $hof['login'] ?></td>
        <td><?php echo $hof['experience'] ?></td>
        <td><?php echo $hof['operation'] ?></td>
        <td><?php echo $hof['kills'] ?></td>
        <td>soon ...</td>
    </tr>
<?php } ?>

    <tr>
        <td colspan="7" align="center">
            <form action="<?php echo $Form ?>" method="post">
                <input type="hidden" name="o" value="<?php echo $O ?>"/>
                <label for="offset">offset</label> <input type="text" size="4" name="offset" value="10">
                <input type="submit" value="show">
            </form>
        </td>
    </tr>

    <?php foreach ($LowerHof as $pos => $hof) { ?>
        <tr>
            <td><?php if(isset($hof['ranking'])) echo $pos+1 ?></td>
            <td><?php if(isset($hof['ranking'])) echo $hof['ranking'] ?></td>
            <td><?php echo $hof['login'] ?></td>
            <td><?php echo $hof['experience'] ?></td>
            <td><?php echo $hof['operation'] ?></td>
            <td><?php echo $hof['kills'] ?></td>
            <td>soon ...</td>
        </tr>

    <?php } ?>

</table>



