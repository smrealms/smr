<?php
if (isset($ErrorMsg)) {
	echo $ErrorMsg; ?><br /><?php
}
if (isset($Msg)) {

    echo "<p align='center'>$Msg</p>"; ?><br /><?php
}
?>

<p>
    <p><b>Researched certificates </b></p>
    <br/>
    <table>
        <tr>
            <th>Certificate</th>
            <th>Associated Ships</th>
        </tr>
        <?php foreach($ResearchedCertificates as $rc){ ?>
            <tr>
                <td align="center"><?php echo $rc['label'] ?></td>
                <td><?php if(isset($rc['ship_names'])){
                        foreach($rc['ship_names'] AS $sn){ ?>
                            <p><?php echo $sn; ?></p>
                        <?php }}?></td>
            </tr>
        <?php } ?>
    </table>
</p>

<!--
<p><b>In Research on Planet</b></p>
<?php if(isset($PlanetResearching)){ ?>
<table>
    <tr>
        <th>In Research</th>
        <th>Researched by</th>
        <th>Due</th>
    </tr>
    <tr>
        <td><?php echo $PlanetResearching['cert_label']?></td>
        <td><?php echo $PlanetResearching['player_name']?></td>
        <td><?php echo date("d/m/Y H:i:s",$PlanetResearching['expires']);?></td>
    </tr>
</table>
<?php }else{ ?>
   <p>You should think about triggering a research </p>
<?php } ?>
-->

<p>
    Certificates you can research
    <br/>
<table>
    <tr>
        <th>Certificate</th>
        <th>Associated Ship</th>
        <th>Race</th>
        <th>Iterations</th>
        <th>Duration per iteration</th>
        <th>Credits</th>
        <th>Computer</th>
        <th>Action</th>
    </tr>
    <?php foreach($ResearchableCertificates as $rc){ ?>
        <tr>
            <td align="center"><?php echo $rc['label'] ?></td>
            <td><?php if(isset($rc['ship_names'])){
                    foreach($rc['ship_names'] AS $sn){ ?>
                        <p><?php echo $sn; ?></p>
                <?php }}?></td>
            <td align="center"><?php
                    if($rc['combined_research'] != null){
                        echo "Combined Research";
                    } elseif(isset($rc['race'])){
                        echo $rc['race'];
                    } else {
                        echo "Any";
                    }?>
            </td>
            <td align="center"><?php echo $rc['iteration'] ?></td>
            <td align="center"><?php echo $rc['duration'] ?> hours</td>
            <td align="center"><?php echo $rc['credits'] ?> credits</td>
            <td align="center"><?php echo $rc['computer'] ?> computers</td>
            <td align="center">
                <?php if(isset($rc['ResearchCertificateHRF'])){?>
                    <a href="<?php echo $rc['ResearchCertificateHRF'] ?>">RESEARCH</a>
                <?php }?>
            </td>
        </tr>
    <?php } ?>
</table>
</p>
