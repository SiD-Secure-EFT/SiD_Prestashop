{*
 * Copyright (c) 2018 PayGate (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 * 
 * Released under the GNU General Public License
 *}
<html>
	<head>
		<script type="text/javascript" src="{$url}js/jquery/jquery-1.2.6.pack.js"></script>
	</head>
	<body>
		<p>{$redirect_text}<br /><a href="javascript:history.go(-1);">{$cancel_text}</a></p>
		<form action="{$SID_URL}" method="post" id="SID_form" class="hidden">
			<input type="HIDDEN" name="SID_MERCHANT" value="{$SID_MERCHANT}" />
			<input type="HIDDEN" name="SID_CURRENCY" value="{$SID_CURRENCY}">
			<input type="HIDDEN" name="SID_COUNTRY" value="{$SID_COUNTRY}">
			<input type="HIDDEN" name="SID_REFERENCE" value="{$SID_REFERENCE}">
			<input type="HIDDEN" name="SID_AMOUNT" value="{$SID_AMOUNT}" />
			<input type="HIDDEN" name="SID_PRIVATE_KEY" value="{$SID_PRIVATE_KEY}" />
			<input type="HIDDEN" name="SID_CONSISTENT" value="{$SID_CONSISTENT}">
		</form>
		<script type="text/javascript">
			function submitSID() {
				document.getElementById("SID_form").submit();
			}
			setTimeout("document.getElementById('SID_form').submit();", 500);
		</script>
	</body>
</html>