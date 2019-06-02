<?php
if ($FullForceCombatResults['Forced']) { ?>
	<h1>Force Results</h1><br />
	<?php $this->includeTemplate('includes/ForcesCombatResults.inc', array('ForcesCombatResults'=>$FullForceCombatResults['Forces']));
} else { ?>
	<h1>Attacker Results</h1><br />
	<?php $this->includeTemplate('includes/ForceTraderTeamCombatResults.inc', array('TraderTeamCombatResults'=>$FullForceCombatResults['Attackers']));
} ?>
<br />
<br />
<img src="images/creonti_cruiser.jpg" alt="Creonti Cruiser" title="Creonti Cruiser"><br />
<br />
<?php if (!$FullForceCombatResults['Forced']) { ?>
	<h1>Force Results</h1><br />
	<?php $this->includeTemplate('includes/ForcesCombatResults.inc', array('ForcesCombatResults'=>$FullForceCombatResults['Forces']));
} else { ?>
	<h1>Defender Results</h1><br />
	<?php $this->includeTemplate('includes/ForceTraderTeamCombatResults.inc', array('TraderTeamCombatResults'=>$FullForceCombatResults['Attackers']));
} ?>