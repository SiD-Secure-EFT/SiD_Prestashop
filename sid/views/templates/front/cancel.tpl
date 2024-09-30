{*
 * Copyright (c) 2024 Payfast (Pty) Ltd
 *
 * Author: App Inlet (Pty) Ltd
 *
 * Released under the GNU General Public License
*}

{extends file='page.tpl'}

{block name="page_content"}
<div>
	<h2>{l s='Transaction Cancelled' mod='sid'}</h2>
	<p>{l s='Please' mod='sid'} <a href="{$link->getPageLink('order')}">{l s='click here' mod='sid'}</a> {l s='to try again' mod='sid'}</p>
</div>
{/block}
