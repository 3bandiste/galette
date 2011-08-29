		<form action="gestion_adherents.php" method="get" id="filtre">
{if $error_detected|@count != 0}
		<div id="errorbox">
			<h1>{_T string="- ERROR -"}</h1>
			<ul>
{foreach from=$error_detected item=error}
				<li>{$error}</li>
{/foreach}
			</ul>
		</div>
{/if}
		<div id="listfilter">
			<label for="filter_str">{_T string="Search:"}&nbsp;</label>
			<input type="text" name="filter_str" id="filter_str" value="{$varslist->filter_str}" type="search" placeholder="{_T string="Enter a value"}"/>&nbsp;
		 	{_T string="in:"}&nbsp;
			<select name="filter_field">
				{html_options options=$filter_field_options selected=$varslist->field_filter}
			</select>
		 	{_T string="among:"}&nbsp;
			<select name="filter_membership" onchange="form.submit()">
				{html_options options=$filter_membership_options selected=$varslist->membership_filter}
			</select>
			<select name="filter_account" onchange="form.submit()">
				{html_options options=$filter_accounts_options selected=$varslist->account_status_filter}
			</select>
			<input type="submit" class="inline" value="{_T string="Filter"}"/>
			<input type="submit" name="clear_filter" class="inline" value="{_T string="Clear filter"}"/>
		</div>
		<table class="infoline">
			<tr>
				<td class="left">{$nb_members} {if $nb_members != 1}{_T string="members"}{else}{_T string="member"}{/if}</td>
				<td class="right">
					<label for="nbshow">{_T string="Records per page:"}</label>
					<select name="nbshow" id="nbshow">
						{html_options options=$nbshow_options selected=$numrows}
					</select>
					<noscript> <span><input type="submit" value="{_T string="Change"}" /></span></noscript>
				</td>
			</tr>
		</table>
		</form>
		<form action="gestion_adherents.php" method="post" id="listform">
		<table id="listing">
			<thead>
				<tr>
					<th class="listing" class="id_row">#</th>
                    <th class="listing" class="id_row">
                        <a href="gestion_adherents.php?tri={php}echo Members::ORDERBY_ID;{/php}" class="listing">N°</a>
                    </th>
					<th class="listing left">
						<a href="gestion_adherents.php?tri={php}echo Members::ORDERBY_NAME;{/php}" class="listing">
							{_T string="Name"}
							{if $varslist->orderby eq constant('Members::ORDERBY_NAME')}
								{if $varslist->ordered eq constant('VarsList::ORDER_ASC')}
							<img src="{$template_subdir}images/down.png" width="10" height="6" alt=""/>
								{else}
							<img src="{$template_subdir}images/up.png" width="10" height="6" alt=""/>
								{/if}
							{/if}
						</a>
					</th>
					<th class="listing left">
						<a href="gestion_adherents.php?tri={php}echo Members::ORDERBY_NICKNAME;{/php}" class="listing">
							{_T string="Nickname"}
							{if $varslist->orderby eq constant('Members::ORDERBY_NICKNAME')}
								{if $varslist->ordered eq constant('VarsList::ORDER_ASC')}
							<img src="{$template_subdir}images/down.png" width="10" height="6" alt=""/>
								{else}
							<img src="{$template_subdir}images/up.png" width="10" height="6" alt=""/>
								{/if}
							{/if}
						</a>
					</th>
					<th class="listing left">
						<a href="gestion_adherents.php?tri={php}echo Members::ORDERBY_STATUS;{/php}" class="listing">
							{_T string="Status"}
							{if $varslist->orderby eq constant('Members::ORDERBY_STATUS')}
								{if $varslist->ordered eq constant('VarsList::ORDER_ASC')}
							<img src="{$template_subdir}images/down.png" width="10" height="6" alt=""/>
								{else}
							<img src="{$template_subdir}images/up.png" width="10" height="6" alt=""/>
								{/if}
							{/if}
						</a>
					</th>
					<th class="listing left">
						<a href="gestion_adherents.php?tri={php}echo Members::ORDERBY_FEE_STATUS;{/php}" class="listing">
							{_T string="State of dues"}
							{if $varslist->orderby eq constant('Members::ORDERBY_FEE_STATUS')}
								{if $varslist->ordered eq constant('VarsList::ORDER_ASC')}
							<img src="{$template_subdir}images/down.png" width="10" height="6" alt=""/>
								{else}
							<img src="{$template_subdir}images/up.png" width="10" height="6" alt=""/>
								{/if}
							{/if}
						</a>
					</th>
					<th class="listing">{_T string="Actions"}</th>
				</tr>
			</thead>
{if $nb_members != 0}
			<tfoot>
                <tr>
                    <td colspan="7" id="table_footer">
                        <ul class="selection_menu">
                            <li>{_T string="For the selection:"}</li>
                            <li><input type="submit" id="delete" onclick="return confirm('{_T string="Do you really want to delete all selected accounts (and related contributions)?"|escape:"javascript"}');" name="delete" value="{_T string="Delete"}"/></li>
    {if $pref_mail_method neq constant('Mailing::METHOD_DISABLED')}
                            <li><input type="submit" id="sendmail" name="mailing" value="{_T string="Mail"}"/></li>
    {/if}
                            <li>
                                <input type="submit" id="attendance_sheet" name="attendance_sheet" value="{_T string="Attendance sheet"}"/>
                                {*<span class="exemple">(<input type="checkbox" id="wimages" name="wimages" value="1"/> <label for="wimages">{_T string="Including photos?"}</label>)</span>*}
                            </li>
                            <li><input type="submit" name="labels" value="{_T string="Generate labels"}"/></li>
                            <li><input type="submit" name="cards" value="{_T string="Generate Member Cards"}"/></li>
                        </ul>
                    </td>
                </tr>
				<tr>
					<td colspan="7" class="center">
						{_T string="Pages:"}<br/>
						<ul class="pages">{$pagination}</ul>
					</td>
				</tr>
			</tfoot>
{/if}
			<tbody>
{foreach from=$members item=member key=ordre}
				<tr>
					<td class="{$member->getRowClass()} right">{php}$ordre = $this->get_template_vars('ordre');echo $ordre+1+($varslist->current_page - 1)*$numrows{/php}</td>
                    <td class="{$member->getRowClass()} right">{$member->id}</td>
					<td class="{$member->getRowClass()} nowrap username_row">
						<input type="checkbox" name="member_sel[]" value="{$member->id}"/>
					{if $member->politeness eq constant('Politeness::MR')}
						<img src="{$template_subdir}images/icon-male.png" alt="{_T string="[M]"}" width="16" height="16"/>
					{elseif $member->politeness eq constant('Politeness::MRS') || $member->politeness eq constant('Politeness::MISS')}
						<img src="{$template_subdir}images/icon-female.png" alt="{_T string="[W]"}" width="16" height="16"/>
					{elseif $member->politeness eq constant('Politeness::COMPANY')}
						<img src="{$template_subdir}images/icon-company.png" alt="{_T string="[W]"}" width="16" height="16"/>
					{else}
						<img src="{$template_subdir}images/icon-empty.png" alt="" width="16" height="16"/>
					{/if}
					{if $member->email != ''}
						<a href="mailto:{$member->email}"><img src="{$template_subdir}images/icon-mail.png" alt="{_T string="[Mail]"}" width="16" height="16"/></a>
					{else}
						<img src="{$template_subdir}images/icon-empty.png" alt="" width="16" height="16"/>
					{/if}
					{if $member->isAdmin()}
						<img src="{$template_subdir}images/icon-star.png" alt="{_T string="[admin]"}" width="16" height="16"/>
					{else}
						<img src="{$template_subdir}images/icon-empty.png" alt="" width="16" height="16"/>
					{/if}
					<a href="voir_adherent.php?id_adh={$member->id}">{$member->sname}</a>
					</td>
					<td class="{$member->getRowClass()} nowrap">{$member->nickname|htmlspecialchars}</td>
					<td class="{$member->getRowClass()} nowrap">{$member->sstatus}</td>
					<td class="{$member->getRowClass()}">{$member->getDues()}</td>
					<td class="{$member->getRowClass()} center nowrap actions_row">
						<a href="ajouter_adherent.php?id_adh={$member->id}"><img src="{$template_subdir}images/icon-edit.png" alt="{_T string="[mod]"}" width="16" height="16" title="{_T string="%membername: edit informations" pattern="/%membername/" replace=$member->sname}"/></a>
						<a href="gestion_contributions.php?id_adh={$member->id}"><img src="{$template_subdir}images/icon-money.png" alt="{_T string="[$]"}" width="16" height="16" title="{_T string="%membername: contributions" pattern="/%membername/" replace=$member->sname}"/></a>
						<a onclick="return confirm('{_T string="Do you really want to delete this member from the base? This will also delete the history of his fees. You could instead disable the account.\n\nDo you still want to delete this member ?"|escape:"javascript"}')" href="gestion_adherents.php?sup={$member->id}"><img src="{$template_subdir}images/icon-trash.png" alt="{_T string="[del]"}" width="16" height="16" title="{_T string="%membername: remove from database" pattern="/%membername/" replace=$member->sname}"/></a>
            {* If some additionnals actions should be added from plugins, we load the relevant template file
            We have to use a template file, so Smarty will do its work (like replacing variables). *}
            {if $plugin_actions|@count != 0}
              {foreach from=$plugin_actions item=action}
                {include file=$action}
              {/foreach}
            {/if}
					</td>
				</tr>
{foreachelse}
				<tr><td colspan="7" class="emptylist">{_T string="No member has been found"}</td></tr>
{/foreach}
			</tbody>
		</table>
		</form>
{if $nb_members != 0}
		<div id="legende" title="{_T string="Legend"}">
			<h1>{_T string="Legend"}</h1>
			<table>
				<tr>
					<th><img src="{$template_subdir}images/icon-male.png" alt="{_T string="Mister"}" width="16" height="16"/></th>
					<td class="back">{_T string="Man"}</td>
					<th class="back">{_T string="Name"}</th>
					<td class="back">{_T string="Active account"}</td>
				</tr>
				<tr>
					<th><img src="{$template_subdir}images/icon-female.png" alt="{_T string="Miss"} / {_T string="Mrs"}" width="16" height="16"/></th>
					<td class="back">{_T string="Woman"}</td>
					<th class="inactif back">{_T string="Name"}</th>
					<td class="back">{_T string="Inactive account"}</td>
				</tr>
				<tr>
					<th><img src="{$template_subdir}images/icon-company.png" alt="{_T string="Society"}" width="16" height="16"/></th>
					<td class="back">{_T string="Society"}</td>
					<th class="cotis-never color-sample">&nbsp;</th>
					<td class="back">{_T string="Never contributed"}</td>
				</tr>
				<tr>
					<th><img src="{$template_subdir}images/icon-star.png" alt="{_T string="Admin"}" width="16" height="16"/></th>
					<td class="back">{_T string="Admin"}</td>
					<th class="cotis-ok color-sample">&nbsp;</th>
					<td class="back">{_T string="Membership in order"}</td>
				</tr>
				<tr>
					<th><img src="{$template_subdir}images/icon-edit.png" alt="{_T string="Modify"}" width="16" height="16"/></th>
					<td class="back">{_T string="Modification"}</td>
					<th class="cotis-soon color-sample">&nbsp;</th>
					<td class="back">{_T string="Membership will expire soon (&lt;30d)"}</td>
				</tr>
				<tr>
					<th><img src="{$template_subdir}images/icon-money.png" alt="{_T string="Contribution"}" width="16" height="16"/></th>
					<td class="back">{_T string="Contributions"}</td>
					<th class="cotis-late color-sample">&nbsp;</th>
					<td class="back">{_T string="Lateness in fee"}</td>
				</tr>
				<tr>
					<th><img src="{$template_subdir}images/icon-trash.png" alt="{_T string="Delete"}" width="16" height="16"/></th>
					<td class="back">{_T string="Deletion"}</td>
				</tr>
				<tr>
					<th><img src="{$template_subdir}images/icon-mail.png" alt="{_T string="E-mail"}" width="16" height="16"/></th>
					<td class="back">{_T string="Send a mail"}</td>
				</tr>
			</table>
		</div>
		<script type="text/javascript">
		var _is_checked = true;
		var _bind_check = function(){ldelim}
			$('#checkall').click(function(){ldelim}
				$('#listing :checkbox[name=member_sel[]]').each(function(){ldelim}
					this.checked = _is_checked;
				{rdelim});
				_is_checked = !_is_checked;
				return false;
			{rdelim});
			$('#checkinvert').click(function(){ldelim}
				$('#listing :checkbox[name=member_sel[]]').each(function(){ldelim}
					this.checked = !$(this).is(':checked');
				{rdelim});
				return false;
			{rdelim});
		{rdelim}
		{* Use of Javascript to draw specific elements that are not relevant is JS is inactive *}
		$(function(){ldelim}
			$('#table_footer').parent().before('<tr><td id="checkboxes" colspan="4"><span class="fleft"><a href="#" id="checkall">{_T string="(Un)Check all"}</a> | <a href="#" id="checkinvert">{_T string="Invert selection"}</a></span></td></tr>');
			_bind_check();
			$('#nbshow').change(function() {ldelim}
				this.form.submit();
			{rdelim});
			$('#checkboxes').after('<td class="right" colspan="3"><a href="#" id="show_legend">{_T string="Show legend"}</a></td>');
			$('#legende h1').remove();
			$('#legende').dialog({ldelim}
				autoOpen: false,
				modal: true,
				hide: 'fold',
				width: '40%'
			{rdelim}).dialog('close');

			$('#show_legend').click(function(){ldelim}
				$('#legende').dialog('open');
				return false;
			{rdelim});
		{rdelim});
		</script>
{/if}
