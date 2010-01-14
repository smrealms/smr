<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>

		<script type="text/javascript" src="http://video.smrealms.de/testSite/Jscript/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="http://video.smrealms.de/testSite/Jscript/jquery.corner.js"></script>
		<script type="text/javascript" src="http://video.smrealms.de/testSite/Jscript/time.js"></script>


		<link href="http://video.smrealms.de/testSite/JQueryCSS.css" rel="stylesheet" type="text/css" />

		<title>SMR Rounded Corners</title>
	</head>
	<body onload="startClock();">
		<div id="Container">
		<table class="tableHeight">
			<tr>
				<td class="topleftCell">
					<b><span style="font-size: 72px;">smr</span></b>
					<br />
					<span id="tod"><?php echo $timeDisplay; ?></span>
				</td>
				<td class="topcenterCell">
					<div class="TopInforBorder">
					<div class="TopInfor">
						<div class="name">
							Pilot
							<br />
							<a class="Nav" href="JQueryRound.htm">Freon22(103)</a>
						</div>

						<div class="topcenterOne">
							Race: Human
							<br />
							Newbie Turns: <span style="color: Green;">78</span>
							<br />
							Credits: 1,788,324
							<br />
							Experience: 11,788
						</div>

						<div class="topcenterTwo">
							Level: 12
							<br />
							Next Level: 12,488
							<br />
							Alignment: 212
							<br />
							Alliance: None 
						</div>
						<div class="messages">Scout, player, ... messages icons appear here!</div>
					</div>

					</div>
				</td>
				<td rowspan="2" class="rightCell">
					<div class="RightInforBorder">
					<div class="RightInfor">
						<div class="RightInforShip">
						Federal Ultimatum
						Rating: 39/17
						</div>
						<br />

						Shields: 800/800
						<br />
						Armor: 700/700
						<br />
						<a class="Nav" href="JQueryRound.htm">CIJSD: --**-</a>
						<br /><br />
						<a class="Nav" href="JQueryRound.htm"><span style="font-size: 18px;">Forces</span></a>
						<br />
						<a class="Nav" href="JQueryRound.htm">[x]</a> Mines 0/0
						<br />

						<a class="Nav" href="JQueryRound.htm">[x]</a> Combat 120/120
						<br />
						<a class="Nav" href="JQueryRound.htm">[x]</a> Scout 1/5
					</div>
					</div>
				</td>
			</tr>
			<tr>

				<td class="leftCell">
					<?php $this->includeTemplate('includes/LeftPanel.inc'); ?>
				</td>
				<td class="centerContent">

					<div class="MainContentBorder">
					<div class="ContentWraper">
						<div class="MainContentArea"><?php
							if(isset($PageTopic))
							{
								?><h1><?php echo $PageTopic; ?></h1><br /><?php
							}
							if(isset($MenuBar))
							{
								echo $MenuBar;
							}
							$this->includeTemplate($TemplateBody); ?>
						</div>
					</div>
					</div>
					<div class="copyright">

						SMR v1.6.4©2007-2010 Page and SMR
						<br />
						Kindly Hosted by FeM
						<br />
						Script runtime: 0.0446 seconds
						<br />
						[Imprint]
					</div>  
				</td>
			</tr>
		</table>
		</div>

			<script type="text/javascript">
				$(".TopInfor").corner().parent().corner();
				$(".LeftOne").corner().parent().corner();
				$(".LeftTwo").corner().parent().corner();
				$(".RightInfor").corner().parent().corner();
				$(".MainContentArea").corner().parent().parent().corner();
			</script>
	</body>
</html>