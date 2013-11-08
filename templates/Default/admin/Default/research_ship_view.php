<?php
if(isset($ErrorMessage)) {
	echo $ErrorMessage; ?><br /><br /><?php
}
if(isset($Message)) {
	echo $Message; ?><br /><br /><?php
} ?>

	<h1>Ship Research for GameResearchId: <?php echo $GameResearch['id'] ?></h1><br />

    <h2>Certificates</h2>
    <?php if(isset($GameResearchCertificates)){ ?>
        <p><b>Created certificates</b></p>
        <table>
            <tr>
                <th>Certificate Name</th>
                <th>Race</th>
                <th>Duration in hours</th>
                <th>Iterations</th>
                <th>Credits</th>
                <th>Computer</th>
                <th>Multirace</th>
                <th>Parent</th>
                <th>DELETE</th>
            </tr>

            <?php foreach( $GameResearchCertificates AS $cert){ ?>
                <tr>
                    <td><?php echo $cert['label'] ?></td>
                    <td align="center"><?php if(isset($cert['race_name'])) echo $cert['race_name'] ?></td>
                    <td align="center"><?php echo $cert['duration'] ?></td>
                    <td align="center"><?php echo $cert['iteration'] ?></td>
                    <td align="center"><?php echo $cert['credits'] ?></td>
                    <td align="center"><?php echo $cert['computer'] ?></td>
                    <td align="center"><?php if(isset($cert['combined_research'])) echo "YES" ?></td>
                    <td align="center"><?php if(isset($cert['parent_label'])) echo $cert['parent_label'] ?></td>
                    <td align="center"><a href="<?php echo $cert['deleteHref'] ?>">DELETE</a></td>
                </tr>
            <?php } ?>
        </table>

    <?php } ?>
    <p><b>Add a new certificate</b></p>
<form name="AddCertificate" method="POST" action="<?php echo $AddCertificateHref; ?>">
<input type="hidden" name="gameId" value="<?php echo $GameResearch['game_id']; ?>"/>
        <table>
            <tr>
                <td><b>Label for the certificate</b></td>
                <td><input type="text" name="label" size="50"/></td>
            </tr>
            <tr>
                <td><b>Select race that is allowed to research this certificate (None for any)</b></td>
                <td><select name="raceId" size="1" id="InputFields">
                        <option value="">None</option>
                        <?php foreach($Races as $r) {?>
                            <option value="<?php echo $r['race_id'] ?>"><?php echo $r['race_name'] ?></option>
                        <?php } ?>
                    </select></td>
            </tr>
            <tr>
                <td><b>Research duration in hours</b></td>
                <td><input type="text" name="duration" size="2"/> </td>
            </tr>
            <tr>
                <td><b>Research iteration in order to conclude the research</b></td>
                <td><!-- <input type="text" name="iteration" size="1"/> --> NEXT RELEASE</td>
            </tr>
            <tr>
                <td><b>Credits to start research</b></td>
                <td><input type="text" name="credits" size="8"/> </td>
            </tr>
            <tr>
                <td><b>Computers on planet to start research</b></td>
                <td><input type="text" name="computer" size="5"/> </td>
            </tr>
            <tr>
                <td><b>Do all races need to research on that certificate ?</b></td>
                <td> <!-- <input type="checkbox" name="combinedResearch" value="1" /> --> !!! NEXT RELEASE !!!</td>
            </tr>
            <tr>
                <td></b>Predecessor certificate</b></td>
                <td><select name="parentId" size="1" id="InputFields">
                        <option value="">None</option>

                        <?php if(isset($GameResearchCertificates)) {
                                foreach($GameResearchCertificates as $v) {?>
                                <option value="<?php echo $v['id'] ?>"><?php echo $v['label'] ?></option>
                            <?php }
                        } ?>
                    </select></td>
            </tr>

            <tr>
                <td colspan="2"><input type="submit" name="addCertificate" value="Add certificate"/></td>
            </tr>
        </table>


    </form>
    <br/>
    <hr/>
    <br/>

    <h2>Ships assigned to certificates</h2>
<?php if(isset($GameResearchShipCertificates)){ ?>
<p><b>Created certificates</b></p>
<table>
    <tr>
        <th>Certificate Name</th>
        <th>Ship Name</th>
        <th>Required Certificate</th>
        <th>DELETE</th>
    </tr>
    <?php foreach( $GameResearchShipCertificates AS $cert){ ?>
    <tr>
        <td><?php echo $cert['label'] ?></td>
        <td align="center"><?php echo $cert['ship_name'] ?></td>
        <td align="center"><?php if(isset($cert['parent'])) echo $cert['parent'] ?></td>
        <td align="center"><a href="<?php echo $cert['deleteHref'] ?>">DELETE</a></td>
    </tr>
    <?php } ?>
</table>

        <?php } ?>

    <p><b>Assign ship to certificate</b></p>
<form name="AssignShipToCertificate" method="POST" action="<?php echo $AddCertificateHref; ?>">
    <input type="hidden" name="gameId" value="<?php echo $GameResearch['game_id']; ?>"/>
    <table>

        <tr>
            <th>Select the ship-type that should be assigned</th>
        </tr>
        <tr>
            <td><select name="shipTypeId" size="1" id="InputFields">

                    <?php foreach($ShipTypes as $st) {?>
                        <option value="<?php echo $st['ShipTypeID'] ?>"><?php echo $st['Name'] ?></option>
                    <?php } ?>
                </select></td>
        </tr>

        <tr>
            <th>Certificate assigned to the ship</th>
        </tr>
        <tr>
            <td><select name="researchCertificateId" size="1" id="InputFields">
                    <?php if(isset($GameResearchCertificates)) {
                        foreach($GameResearchCertificates as $v) {?>
                            <option value="<?php echo $v['id'] ?>"><?php echo $v['label'] ?></option>
                        <?php }
                    } ?>
                </select></td>
        </tr>

        <tr>
            <td colspan="2"><input type="submit" name="assignCertificate" value="Assign certificate"/></td>
        </tr>
    </table>


</form>


<br />
	<br />
