<?php
require_once('config.inc');
require_once(LIB.'External/recaptcha/recaptchalib.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
	<style>.recaptchatable #recaptcha_response_field {background:white; color: black;}</style>
	<title>Space Merchant Realms</title>
</head>

<body>

<table cellspacing='0' cellpadding='0' border='0' width='100%' height='100%'>
<tr>
	<td></td>
	<td colspan='3' height='1' bgcolor='#0B8D35'></td>
	<td></td>
</tr>
<tr>
	<td width='135'>&nbsp;</td>
	<td width='1' bgcolor='#0B8D35'></td>
	<td align='left' valign='top' width='600' bgcolor='#06240E'>
		<table width='100%' height='100%' border='0' cellspacing='5' cellpadding='5'>
		<tr>
			<td valign='top'>

				<h1>CREATE LOGIN</h1>
				<p align='justify'>
					Creating multiple logins will not be tolerated. If we discover someone playing with several
					logins, then all of them will be deleted. We have implemented architecture elements to help
					us detect this. There will be no toleration for multis! If an player is caught using a multi
					that player's accounts WILL be deleted without ANY warning.<br />
					<a href='http://smrcnn.smrealms.de/viewtopic.php?t=382' style='font-weight:bold;'>Click HERE</a> for more information.
				</p>

				<form action='login_create_processing.php' method='POST'>
					<div align='center' style='color:red;'>*** Any personal information is confidential and will not be sold to third parties. ***</div>

					<table border='0' cellspacing='0' cellpadding='1'>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<th colspan='2'>Game Information</th>
					</tr>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<?php //BETA
//					<tr>
//						<td width='27%'>Beta Key:</td>
//						<td width='73%'><input type='text' name='beta_key' size='20' maxlength='50' id='InputFields'></td>
//					</tr>
					?>
					<tr>
						<td width='27%'>User name:</td>
						<td width='73%'><input type='text' name='login' size='20' maxlength='32' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Password:</td>
						<td width='73%'><input type='password' name='password' size='20' maxlength='32' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Verify:</td>
						<td width='73%'><input type='password' name='pass_verify' size='20' maxlength='32' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>E-Mail Address:</td>
						<td width='73%'><input type='text' name='email' size='50' maxlength='128' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Verify E-Mail Address:</td>
						<td width='73%'><input type='text' name='email_verify' size='50' maxlength='128' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Local Time:</td>
						<td width='73%'>
							<select name="timez" id="InputFields"><?php
								$time = TIME;
								for ($i = -12; $i<= 11; $i++)
								{
									?><option value="<?php echo $i; ?>"><?php echo date(DEFAULT_DATE_TIME_SHORT, $time + $i * 3600); ?></option><?php
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td width='27%'>Referral ID (Optional):</td>
						<td width='73%'><input type='text' name='referral_id' size='10' maxlength='20' id='InputFields'<?php if(isset($_REQUEST['ref'])){ echo 'value="'.htmlspecialchars($_REQUEST['ref']).'"'; }?>></td>
					</tr>
					<tr>
						<td colspan='2'><?php echo recaptcha_get_html(RECAPTCHA_PUBLIC); ?></td>
					</tr>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<th colspan='2'>User Information (Address Optional)</th>
					</tr>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<td width='27%'>First Name:</td>
						<td width='73%'><input type='text' name='first_name' size='20' maxlength='50' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Last Lame:</td>
						<td width='73%'><input type='text' name='last_name' size='20' maxlength='50' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Address:</td>
						<td width='73%'><input type='text' name='address' size='50' maxlength='255' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>City:</td>
						<td width='73%'><input type='text' name='city' size='20' maxlength='50' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Postal Code:</td>
						<td width='73%'><input type='text' name='postal_code' size='20' maxlength='10' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Country:</td>
						<td width='73%'>
							<select name='country_code' id='InputFields'>
								<option value="None" selected="selected">[Select your country]</option>
								<option value='US'>United States</option>
								<option value='UK'>United Kingdom</option>
								<option value='DE'>Germany</option>
								<option value='CA'>Canada</option>
								<option value='FR'>France</option>
								<option value="0">--------------------</option>
								<option value='AF'>Afghanistan</option>
								<option value='AL'>Albania</option>
								<option value='DZ'>Algeria</option>
								<option value='AS'>American Samoa</option>
								<option value='AD'>Andorra</option>
								<option value='AO'>Angola</option>
								<option value='AI'>Anguilla</option>
								<option value='AQ'>Antarctica</option>
								<option value='AG'>Antigua And Barbuda</option>
								<option value='AR'>Argentina</option>
								<option value='AM'>Armenia</option>
								<option value='AW'>Aruba</option>
								<option value='AU'>Australia</option>
								<option value='AT'>Austria</option>
								<option value='AZ'>Azerbaijan</option>
								<option value='BS'>Bahamas</option>
								<option value='BH'>Bahrain</option>
								<option value='BD'>Bangladesh</option>
								<option value='BB'>Barbados</option>
								<option value='BY'>Belarus</option>
								<option value='BE'>Belgium</option>
								<option value='BZ'>Belize</option>
								<option value='BJ'>Benin</option>
								<option value='BM'>Bermuda</option>
								<option value='BT'>Bhutan</option>
								<option value='BO'>Bolivia</option>
								<option value='BA'>Bosnia and Herzegovina</option>
								<option value='BW'>Botswana</option>
								<option value='BV'>Bouvet Island</option>
								<option value='BR'>Brazil</option>
								<option value='IO'>British Indian Ocean Territory</option>
								<option value='BN'>Brunei</option>
								<option value='BG'>Bulgaria</option>
								<option value='BF'>Burkina Faso</option>
								<option value='BI'>Burundi</option>
								<option value='KH'>Cambodia</option>
								<option value='CM'>Cameroon</option>
								<option value='CV'>Cape Verde</option>
								<option value='KY'>Cayman Islands</option>
								<option value='CF'>Central African Republic</option>
								<option value='TD'>Chad</option>
								<option value='CL'>Chile</option>
								<option value='CN'>China</option>
								<option value='CX'>Christmas Island</option>
								<option value='CC'>Cocos (Keeling) Islands</option>
								<option value='CO'>Columbia</option>
								<option value='KM'>Comoros</option>
								<option value='CG'>Congo</option>
								<option value='CK'>Cook Islands</option>
								<option value='CR'>Costa Rica</option>
								<option value='CI'>Cote D'Ivoire (Ivory Coast)</option>
								<option value='HR'>Croatia (Hrvatska)</option>
								<option value='CU'>Cuba</option>
								<option value='CY'>Cyprus</option>
								<option value='CZ'>Czech Republic</option>
								<option value='KP'>D.P.R. Korea</option>
								<option value='CD'>Dem Rep of Congo (Zaire)</option>
								<option value='DK'>Denmark</option>
								<option value='DJ'>Djibouti</option>
								<option value='DM'>Dominica</option>
								<option value='DO'>Dominican Republic</option>
								<option value='TP'>East Timor</option>
								<option value='EC'>Ecuador</option>
								<option value='EG'>Egypt</option>
								<option value='SV'>El Salvador</option>
								<option value='GQ'>Equatorial Guinea</option>
								<option value='ER'>Eritrea</option>
								<option value='EE'>Estonia</option>
								<option value='ET'>Ethiopia</option>
								<option value='FK'>Falkland Islands (Malvinas)</option>
								<option value='FO'>Faroe Islands</option>
								<option value='FJ'>Fiji</option>
								<option value='FI'>Finland</option>
								<option value='GF'>French Guiana</option>
								<option value='PF'>French Polynesia</option>
								<option value='TF'>French Southern Territories</option>
								<option value='GA'>Gabon</option>
								<option value='GM'>Gambia</option>
								<option value='GE'>Georgia</option>
								<option value='GH'>Ghana</option>
								<option value='GI'>Gibraltar</option>
								<option value='GR'>Greece</option>
								<option value='GL'>Greenland</option>
								<option value='GD'>Grenada</option>
								<option value='GP'>Guadeloupe</option>
								<option value='GU'>Guam</option>
								<option value='GT'>Guatemala</option>
								<option value='GN'>Guinea</option>
								<option value='GW'>Guinea-Bissau</option>
								<option value='GY'>Guyana</option>
								<option value='HT'>Haiti</option>
								<option value='HM'>Heard and McDonald Islands</option>
								<option value='HN'>Honduras</option>
								<option value='HK'>Hong Kong SAR, PRC</option>
								<option value='HU'>Hungary</option>
								<option value='IS'>Iceland</option>
								<option value='IN'>India</option>
								<option value='ID'>Indonesia</option>
								<option value='IR'>Iran</option>
								<option value='IQ'>Iraq</option>
								<option value='IE'>Ireland</option>
								<option value='IL'>Israel</option>
								<option value='IT'>Italy</option>
								<option value='JM'>Jamaica</option>
								<option value='JP'>Japan</option>
								<option value='JO'>Jordan</option>
								<option value='KZ'>Kazakhstan</option>
								<option value='KE'>Kenya</option>
								<option value='KI'>Kiribati</option>
								<option value='KR'>Korea</option>
								<option value='KW'>Kuwait</option>
								<option value='KG'>Kyrgyzstan</option>
								<option value='LA'>Laos</option>
								<option value='LV'>Latvia</option>
								<option value='LB'>Lebanon</option>
								<option value='LS'>Lesotho</option>
								<option value='LR'>Liberia</option>
								<option value='LY'>Libya</option>
								<option value='LI'>Liechtenstein</option>
								<option value='LT'>Lithuania</option>
								<option value='LU'>Luxembourg</option>
								<option value='MO'>Macao</option>
								<option value='MK'>Macedonia</option>
								<option value='MG'>Madagascar</option>
								<option value='MW'>Malawi</option>
								<option value='MY'>Malaysia</option>
								<option value='MV'>Maldives</option>
								<option value='ML'>Mali</option>
								<option value='MT'>Malta</option>
								<option value='MH'>Marshall Islands</option>
								<option value='MQ'>Martinique</option>
								<option value='MR'>Mauritania</option>
								<option value='MU'>Mauritius</option>
								<option value='YT'>Mayotte</option>
								<option value='MX'>Mexico</option>
								<option value='FM'>Micronesia</option>
								<option value='MD'>Moldova</option>
								<option value='MC'>Monaco</option>
								<option value='MN'>Mongolia</option>
								<option value='MS'>Montserrat</option>
								<option value='MA'>Morocco</option>
								<option value='MZ'>Mozambique</option>
								<option value='MM'>Myanmar</option>
								<option value='NA'>Namibia</option>
								<option value='NR'>Nauru</option>
								<option value='NP'>Nepal</option>
								<option value='NL'>Netherlands</option>
								<option value='AN'>Netherlands Antilles</option>
								<option value='NC'>New Caledonia</option>
								<option value='NZ'>New Zealand</option>
								<option value='NI'>Nicaragua</option>
								<option value='NE'>Niger</option>
								<option value='NG'>Nigeria</option>
								<option value='NU'>Niue</option>
								<option value='NF'>Norfolk Island</option>
								<option value='MP'>Northern Mariana Islands</option>
								<option value='NO'>Norway</option>
								<option value='OM'>Oman</option>
								<option value='PK'>Pakistan</option>
								<option value='PW'>Palau</option>
								<option value='PA'>Panama</option>
								<option value='PG'>Papua new Guinea</option>
								<option value='PY'>Paraguay</option>
								<option value='PE'>Peru</option>
								<option value='PH'>Philippines</option>
								<option value='PN'>Pitcairn Island</option>
								<option value='PL'>Poland</option>
								<option value='PT'>Portugal</option>
								<option value='PR'>Puerto Rico</option>
								<option value='QA'>Qatar</option>
								<option value='RE'>Reunion</option>
								<option value='RO'>Romania</option>
								<option value='RU'>Russia</option>
								<option value='RW'>Rwanda</option>
								<option value='SH'>Saint Helena</option>
								<option value='KN'>Saint Kitts And Nevis</option>
								<option value='LC'>Saint Lucia</option>
								<option value='PM'>Saint Pierre and Miquelon</option>
								<option value='VC'>Saint Vincent And The Grenadines</option>
								<option value='WS'>Samoa</option>
								<option value='SM'>San Marino</option>
								<option value='ST'>Sao Tome and Principe</option>
								<option value='SA'>Saudi Arabia</option>
								<option value='SN'>Senegal</option>
								<option value='SC'>Seychelles</option>
								<option value='SL'>Sierra Leone</option>
								<option value='SG'>Singapore</option>
								<option value='SK'>Slovak Republic</option>
								<option value='SI'>Slovenia</option>
								<option value='SB'>Solomon Islands</option>
								<option value='SO'>Somalia</option>
								<option value='ZA'>South Africa</option>
								<option value='GS'>South Georgia And The South Sandwich Islands</option>
								<option value='ES'>Spain</option>
								<option value='LK'>Sri Lanka</option>
								<option value='SD'>Sudan</option>
								<option value='SR'>Suriname</option>
								<option value='SJ'>Svalbard And Jan Mayen Islands</option>
								<option value='SZ'>Swaziland</option>
								<option value='SE'>Sweden</option>
								<option value='CH'>Switzerland</option>
								<option value='SY'>Syria</option>
								<option value='TW'>Taiwan Region</option>
								<option value='TJ'>Tajikistan</option>
								<option value='TZ'>Tanzania</option>
								<option value='TH'>Thailand</option>
								<option value='TG'>Togo</option>
								<option value='TK'>Tokelau</option>
								<option value='TO'>Tonga</option>
								<option value='TT'>Trinidad And Tobago</option>
								<option value='TN'>Tunisia</option>
								<option value='TR'>Turkey</option>
								<option value='TM'>Turkmenistan</option>
								<option value='TC'>Turks And Caicos Islands</option>
								<option value='TV'>Tuvalu</option>
								<option value='UG'>Uganda</option>
								<option value='UA'>Ukraine</option>
								<option value='AE'>United Arab Emirates</option>
								<option value='UM'>United States Minor Outlying Islands</option>
								<option value='UY'>Uruguay</option>
								<option value='UZ'>Uzbekistan</option>
								<option value='VU'>Vanuatu</option>
								<option value='VA'>Vatican City State (Holy See)</option>
								<option value='VE'>Venezuela</option>
								<option value='VN'>Vietnam</option>
								<option value='VG'>Virgin Islands (British)</option>
								<option value='VI'>Virgin Islands (US)</option>
								<option value='WF'>Wallis And Futuna Islands</option>
								<option value='EH'>Western Sahara</option>
								<option value='YE'>Yemen</option>
								<option value='YU'>Yugoslavia</option>
								<option value='ZM'>Zambia</option>
								<option value='ZW'>Zimbabwe</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<th colspan='2'>Various Information (Optional)</th>
					</tr>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<td width='27%'>ICQ:</td>
						<td width='73%'><input type='text' name='icq' size='20' maxlength='15' id='InputFields'></td>
					</tr>
					</table>

					<p>&nbsp;</p>

					<div align='center' style='font-size=80%;'>
						I have read and accepted the User Agreement above,<br />
						made sure all information submitted is correct,<br />
						and understand that my account can be closed or deleted<br />
						with no warning should it contain<br />
						invalid or false information.<br /><br />
						<input type='checkbox' name='agreement' value='checkbox'>
					</div>

					<p><input type='submit' name='create_login' value='Create Login'></p>
				</form>

			</td>
		</tr>
		</table>
	</td>
	<td width='1' bgcolor='#0B8D35'></td>
	<td width='135'>&nbsp;</td>
</tr>
<tr>
	<td></td>
	<td colspan='3' height='1' bgcolor='#0b8d35'></td>
	<td></td>
</tr>
</table>

</body>
</html>