<?php
if(isset($ErrorMessage)) {
	echo $ErrorMessage; ?><br /><br /><?php
}
if(isset($Message)) {
	echo $Message; ?><br /><br /><?php
} ?>

	<h1>Research</h1><br />


<?php if(isset($Games)){ ?>
    <p>This is the entry page to the research management. <br/>
    <br/>
    <p>Please select the game to set the research </p>
    <form name="FORM" method="POST" action="<?php echo $SelectGameForResearchHref ?>">
        <select name="gameId" size="1" id="InputFields">
                <?php foreach($Games as $g) {?>
                    <option value="<?php echo $g['ID'] ?>"><?php echo $g['Name'] ?></option>
                <?php } ?>
            </select>
        <input type="submit" value="select"/>
    </form>

<?php } ?>

<?php if(isset($Game)){?>

    <h2>Game <?php echo $Game->getName() ?></h2>

    <p><a href="<?php echo $ShipResearchHref ?>">Ship Research</p>

	<br />
	<br />
<?php } ?>