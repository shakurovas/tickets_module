<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? $APPLICATION->AddHeadScript($this->GetFolder() . '/ru/script.js');?>
<?=ShowError($arResult["ERROR_MESSAGE"]);?>

<?

if (!empty($arResult["TICKET"])):?>
    <table class="support-ticket-edit data-table medi">
        <tbody>
        <tr class="medi-user-alert" ><th class="medi-user-alert">
<? if (!empty($arResult["ONLINE"]))	{?>
	<p class="medi-info">
		<?$time = intval($arResult["OPTIONS"]["ONLINE_INTERVAL"]/60)." ".GetMessage("SUP_MIN");?>
		<?=str_replace("#TIME#",$time,GetMessage("SUP_USERS_ONLINE"));?>:<br />

		<?foreach($arResult["ONLINE"] as $arOnlineUser):?>
		<small>(<?=$arOnlineUser["USER_LOGIN"]?>) <?=$arOnlineUser["USER_NAME"]?> [<?=$arOnlineUser["TIMESTAMP_X"]?>]</small><br />
		<?endforeach?>

	</p>
<?}?>
            </th></tr>
        <tr><th>
<p><h1><?=$arResult["TICKET"]["TITLE"]?></h1></p>

            </th></tr>

		<tr>
			<th><?=GetMessage("SUP_TICKET")?></th>
		</tr>

		<tr>
			<td>

			<?=GetMessage("SUP_SOURCE")." / ".GetMessage("SUP_FROM")?>:

				<?if (strlen($arResult["TICKET"]["SOURCE_NAME"])>0):?>
					[<?=$arResult["TICKET"]["SOURCE_NAME"]?>]
				<?else:?>
					[web]
				<?endif?>

				<?if (strlen($arResult["TICKET"]["OWNER_SID"])>0):?>
					<?=$arResult["TICKET"]["OWNER_SID"]?>
				<?endif?>

				<?if (intval($arResult["TICKET"]["OWNER_USER_ID"])>0):?>
					[<?=$arResult["TICKET"]["OWNER_USER_ID"]?>]
					(<?=$arResult["TICKET"]["OWNER_LOGIN"]?>)
					<?=$arResult["TICKET"]["OWNER_NAME"]?>
				<?endif?>
			<br />


			<?=GetMessage("SUP_CREATE")?>: <?=$arResult["TICKET"]["DATE_CREATE"]?>

			<?if (strlen($arResult["TICKET"]["CREATED_MODULE_NAME"])<=0 || $arResult["TICKET"]["CREATED_MODULE_NAME"]=="support"):?>
				[<?=$arResult["TICKET"]["CREATED_USER_ID"]?>]
				(<?=$arResult["TICKET"]["CREATED_LOGIN"]?>)
				<?=$arResult["TICKET"]["CREATED_NAME"]?>
			<?else:?>
				<?=$arResult["TICKET"]["CREATED_MODULE_NAME"]?>
			<?endif?>
			<br />


			<?if ($arResult["TICKET"]["DATE_CREATE"]!=$arResult["TICKET"]["TIMESTAMP_X"]):?>
					<?=GetMessage("SUP_TIMESTAMP")?>: <?=$arResult["TICKET"]["TIMESTAMP_X"]?>

					<?if (strlen($arResult["TICKET"]["MODIFIED_MODULE_NAME"])<=0 || $arResult["TICKET"]["MODIFIED_MODULE_NAME"]=="support"):?>
						[<?=$arResult["TICKET"]["MODIFIED_USER_ID"]?>]
						(<?=$arResult["TICKET"]["MODIFIED_BY_LOGIN"]?>)
						<?=$arResult["TICKET"]["MODIFIED_BY_NAME"]?>
					<?else:?>
						<?=$arResult["TICKET"]["MODIFIED_MODULE_NAME"]?>
					<?endif?>

					<br />
			<?endif?>


			<? if (strlen($arResult["TICKET"]["DATE_CLOSE"])>0): ?>
				<?=GetMessage("SUP_CLOSE")?>: <?=$arResult["TICKET"]["DATE_CLOSE"]?>
			<?endif?>


			<?if (strlen($arResult["TICKET"]["STATUS_NAME"])>0) :?>
					<?=GetMessage("SUP_STATUS")?>: <span title="<?=$arResult["TICKET"]["STATUS_DESC"]?>"><?=$arResult["TICKET"]["STATUS_NAME"]?></span><br />
			<?endif;?>


			<?if (strlen($arResult["TICKET"]["CATEGORY_NAME"]) > 0):?>
					<?=GetMessage("SUP_CATEGORY")?>: <span title="<?=$arResult["TICKET"]["CATEGORY_DESC"]?>"><?=$arResult["TICKET"]["CATEGORY_NAME"]?></span><br />
			<?endif?>


			<?if(strlen($arResult["TICKET"]["CRITICALITY_NAME"])>0) :?>
					<?=GetMessage("SUP_CRITICALITY")?>: <span title="<?=$arResult["TICKET"]["CRITICALITY_DESC"]?>"><?=$arResult["TICKET"]["CRITICALITY_NAME"]?></span><br />
			<?endif?>


			<?if (intval($arResult["RESPONSIBLE_USER_ID"])>0):?>
				<?=GetMessage("SUP_RESPONSIBLE")?>: [<?=$arResult["TICKET"]["RESPONSIBLE_USER_ID"]?>]
				(<?=$arResult["TICKET"]["RESPONSIBLE_LOGIN"]?>) <?=$arResult["TICKET"]["RESPONSIBLE_NAME"]?><br />
			<?endif?>


			<?if (strlen($arResult["TICKET"]["SLA_NAME"])>0) :?>
				<?=GetMessage("SUP_SLA")?>:
				<span title="<?=$arResult["TICKET"]["SLA_DESCRIPTION"]?>"><?=$arResult["TICKET"]["SLA_NAME"]?></span>
			<?endif?>


			</td>
		</tr>

		<tr>
			<th><?=GetMessage("SUP_DISCUSSION")?></th>
		</tr>

		<tr>
			<td>
		<?=$arResult["NAV_STRING"]?>

		<?foreach ($arResult["MESSAGES"] as $arMessage):?>
			<div class="ticket-edit-message">

			<div class="support-float-quote">[&nbsp;<a href="#postform" OnMouseDown="javascript:SupQuoteMessage('quotetd<? echo $arMessage["ID"]; ?>')" title="<?=GetMessage("SUP_QUOTE_LINK_DESCR");?>"><?echo GetMessage("SUP_QUOTE_LINK");?></a>&nbsp;]</div>


			<div align="left"><b><?=GetMessage("SUP_TIME")?></b>: <?=$arMessage["DATE_CREATE"]?></div>
			<b><?=GetMessage("SUP_FROM")?></b>:


			<?=$arMessage["OWNER_SID"]?>

			<?if (intval($arMessage["OWNER_USER_ID"])>0):?>
				[<?=$arMessage["OWNER_USER_ID"]?>]
				(<?=$arMessage["OWNER_LOGIN"]?>)
				<?=$arMessage["OWNER_NAME"]?>
			<?endif?>
			<br />


			<?
			$aImg = array("gif", "png", "jpg", "jpeg", "bmp");
			foreach ($arMessage["FILES"] as $arFile):
			?>
			<div class="support-paperclip"></div>
			<?if(in_array(strtolower(GetFileExtension($arFile["NAME"])), $aImg)):?>
				<a title="<?=GetMessage("SUP_VIEW_ALT")?>" href="<?=$componentPath?>/ticket_show_file.php?hash=<?echo $arFile["HASH"]?>&amp;lang=<?=LANG?>"><?=$arFile["NAME"]?></a>
			<?else:?>
				<?=$arFile["NAME"]?>
			<?endif?>
			(<? echo CFile::FormatSize($arFile["FILE_SIZE"]); ?>)
			[ <a title="<?=str_replace("#FILE_NAME#", $arFile["NAME"], GetMessage("SUP_DOWNLOAD_ALT"))?>" href="<?=$componentPath?>/ticket_show_file.php?hash=<?=$arFile["HASH"]?>&amp;lang=<?=LANG?>&amp;action=download"><?=GetMessage("SUP_DOWNLOAD")?></a> ]
			<br class="clear" />
			<?endforeach?>


			<br /><div id="quotetd<? echo $arMessage["ID"]; ?>"><?=$arMessage["MESSAGE"]?></div>

			</div>
		<?endforeach?>

		<?=$arResult["NAV_STRING"]?>

			</td>

		</tr>
	</tbody>
</table>

<br />
<?endif;?>

<form name="support_edit" method="post" action="<?=$arResult["REAL_FILE_PATH"]?>" enctype="multipart/form-data">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="set_default" value="Y" />
	<input type="hidden" name="ID" value=<?=(empty($arResult["TICKET"]) ? 0 : $arResult["TICKET"]["ID"])?> />
	<input type="hidden" name="lang" value="<?=LANG?>" />
	<input type="hidden" name="edit" value="1" />

	<div class="medi-ticket">
		<?if (empty($arResult["TICKET"])):?>
		<h2 class="ticket-title"><?=GetMessage("SUP_TICKET")?></h2>

		<div class="input-group">
			<label class="input-label" for="ticket-title"><?=GetMessage("SUP_TITLE")?> <span class="starrequired">*</span></label>
			<div class="input-box">
				<input class="input-field" type="text" name="TITLE" id="ticket-title" value="<?=htmlspecialcharsbx($_REQUEST["TITLE"])?>" size="48" maxlength="255" />
			</div>
		</div>

		<?else:?>
		<div class="input-group">
			<p><?=GetMessage("SUP_ANSWER")?></p>
		</div>
		<?endif?>

		<?if (strlen($arResult["TICKET"]["DATE_CLOSE"]) <= 0):?>
			<div class="input-group">
				<label class="input-label" for="ticket-content"><?=GetMessage("SUP_MESSAGE")?> <span class="starrequired">*</span></label>
				<div class="input-field">
					<div class="input-buttons editor-buttons">
						<input accesskey="b" type="button" value="<?=GetMessage("SUP_B")?>" onClick="insert_tag('B', document.forms['support_edit'].elements['MESSAGE'])"  name="B" id="B" title="<? echo GetMessage("SUP_B_ALT"); ?>" />
						<input accesskey="i" type="button" value="<?=GetMessage("SUP_I")?>" onClick="insert_tag('I', document.forms['support_edit'].elements['MESSAGE'])" name="I" id="I" title="<? echo GetMessage("SUP_I_ALT"); ?>" />
						<input accesskey="u" type="button" value="<?=GetMessage("SUP_U")?>" onClick="insert_tag('U', document.forms['support_edit'].elements['MESSAGE'])" name="U" id="U" title="<? echo GetMessage("SUP_U_ALT"); ?>" />
						<input accesskey="q" type="button" value="<?=GetMessage("SUP_QUOTE")?>" onClick="insert_tag('QUOTE', document.forms['support_edit'].elements['MESSAGE'])" name="QUOTE" id="QUOTE" title="<? echo GetMessage("SUP_QUOTE_ALT"); ?>" />
						<input accesskey="c" type="button" value="<?=GetMessage("SUP_CODE")?>" onClick="insert_tag('CODE', document.forms['support_edit'].elements['MESSAGE'])" name="CODE" id="CODE" title="<? echo GetMessage("SUP_CODE_ALT"); ?>" />
						<?if (LANG == "ru"):?>
							<input accesskey="t" type="button" accesskey="t" value="<?=GetMessage("SUP_TRANSLIT")?>" onClick="translit(document.forms['support_edit'].elements['MESSAGE'])" name="TRANSLIT" id="TRANSLIT" title="<? echo GetMessage("SUP_TRANSLIT_ALT"); ?>" />
						<?endif?>
					</div>
					<textarea class="input-textarea" id="ticket-content" name="MESSAGE" id="MESSAGE" rows="20" cols="45" wrap="virtual"><?=htmlspecialcharsbx($_REQUEST["MESSAGE"])?></textarea>
				</div>
			</div>

		<div class="input-group">
			<label class="input-label"><?=GetMessage("SUP_ATTACH")?><br />
				(max - <?=$arResult["OPTIONS"]["MAX_FILESIZE"]?> <?=GetMessage("SUP_KB")?>):
				<input type="hidden" name="MAX_FILE_SIZE" value="<?=($arResult["OPTIONS"]["MAX_FILESIZE"]*1024)?>">
			</label>
			<div class="input-field">
				<div class="input-buttons file-upload-buttons">
					<label class="btn-style" for="file-0"><?=GetMessage("SUP_SELECT")?></label>
					<input name="FILE_0" size="30" type="file" id="file-0" /> <br />
					<label class="btn-style" for="file-1"><?=GetMessage("SUP_SELECT")?></label>
					<input name="FILE_1" size="30" type="file" id="file-1" /> <br />
					<label class="btn-style" for="file-2"><?=GetMessage("SUP_SELECT")?></label>
					<input name="FILE_2" size="30" type="file" id="file-2" /> <br />
					<label class="btn-style" for="file-3"><?=GetMessage("SUP_SELECT")?></label>
					<input name="FILE_3" size="30" type="file" id="file-3" /> <br />
					<label class="btn-style" for="file-4"><?=GetMessage("SUP_SELECT")?></label>
					<input name="FILE_4" size="30" type="file" id="file-4" /> <br />
				</div>
			</div>
		</div>
		<?endif?>


        <div class="input-group">
            <label class="input-label"><?=GetMessage("SUP_CRITICALITY")?>:</label>
            <div class="input-field">
                <?
                if (empty($arResult["TICKET"]) || strlen($arResult["ERROR_MESSAGE"]) > 0 )
                {
                    if (strlen($arResult["DICTIONARY"]["CRITICALITY_DEFAULT"]) > 0 && strlen($arResult["ERROR_MESSAGE"]) <= 0)
                        $criticality = $arResult["DICTIONARY"]["CRITICALITY_DEFAULT"];
                    else
                        $criticality = htmlspecialcharsbx($_REQUEST["CRITICALITY_ID"]);
                }
                else
                    $criticality = $arResult["TICKET"]["CRITICALITY_ID"];
                ?>
                <select class="input-select" name="CRITICALITY_ID" id="CRITICALITY_ID">
                    <option value="">&nbsp;</option>
                    <?foreach ($arResult["DICTIONARY"]["CRITICALITY"] as $value => $option):?>
                        <option value="<?=$value?>" <?if($criticality == $value):?>selected="selected"<?endif?>><?=$option?></option>
                    <?endforeach?>
                </select>
            </div>
        </div>

        <div class="input-group">
        <?if(!empty($arResult["SECTION_DEP"]) && empty($arResult["TICKET"]["UF_TASK_FLAG"]) && $arResult["ACSSES_US_GR"] == "Y"):?>

            <label class="input-label"><?=GetMessage("SUP_DEP")?>:</label>
            <div class="input-field">
                    <select name="DEP_ID" id="DEP_ID">

                        <?foreach ($arResult["SECTION_DEP"] as $key => $option):?>
                            <option value="<?=$key?>"><?=$option?></option>
                        <?endforeach?>
                    </select>
            </div>

        <?endif;?>

        </div>




			<div class="input-group">
				<label class="input-label"><?=GetMessage("SUP_CATEGORY")?></label>
				<div class="input-field">
					<? if (strlen($arResult["DICTIONARY"]["CATEGORY_DEFAULT"]) > 0 && strlen($arResult["ERROR_MESSAGE"]) <= 0)
						$category = $arResult["DICTIONARY"]["CATEGORY_DEFAULT"];
					else
						$category = htmlspecialcharsbx($_REQUEST["CATEGORY_ID"]);
					?>
					<select class="input-select" name="CATEGORY_ID" id="CATEGORY_ID">
						<option value="">&nbsp;</option>
						<?foreach ($arResult["DICTIONARY"]["CATEGORY"] as $value => $option):?>
							<option value="<?=$value?>" <?if($category == $value):?>selected="selected"<?endif?>><?=$option?></option>
						<?endforeach?>
					</select>
				</div>
			</div>


			 


		<?if (strlen($arResult["TICKET"]["DATE_CLOSE"])<=0):?>
			<div class="input-group">
				<label for="close-ticket" class="inline-label">
					<input id="close-ticket" type="checkbox" name="CLOSE" value="Y" <?if($arResult["TICKET"]["CLOSE"] == "Y"):?>checked="checked" <?endif?>/> <?=GetMessage("SUP_CLOSE_TICKET")?>
					<span class="checkbox"></span>
				</label>
			</div>
		<?else:?>
			<div class="input-group">
				<label for="open-ticket" class="inline-label">
					<input id="open-ticket" type="checkbox" name="OPEN" value="Y" <?if($arResult["TICKET"]["OPEN"] == "Y"):?>checked="checked" <?endif?>/> <?=GetMessage("SUP_OPEN_TICKET")?>
					<span class="checkbox"></span>
				</label>
			</div>
		<?endif;?>

		<?if ($arParams['SHOW_COUPON_FIELD'] == 'Y' && $arParams['ID'] <= 0){?>
			<div class="input-group">
				<label class="input-label"><?=GetMessage("SUP_COUPON")?></label>
				<div class="input-field">
					<input type="text" name="COUPON" value="<?=htmlspecialcharsbx($_REQUEST["COUPON"])?>" size="48" maxlength="255" />
				</div>
			</div>
		<?}?>

		<? global $USER_FIELD_MANAGER;
			if( isset( $arParams["SET_SHOW_USER_FIELD_T"] ) )
			{
				foreach( $arParams["SET_SHOW_USER_FIELD_T"] as $k => $v )
				{
					$v["ALL"]["VALUE"] = $arParams[$k];
					echo '<div class="field-name">' . htmlspecialcharsbx( $v["NAME_F"] ) . ':</div>';
					$APPLICATION->IncludeComponent(
							'bitrix:system.field.edit',
							$v["ALL"]['USER_TYPE_ID'],
							array(
								'arUserField' => $v["ALL"],
							),
							null,
							array('HIDE_ICONS' => 'Y')
					);
				}
			}
		?>
		
		<div class="input-group">
			<div class="input-buttons submit-buttons">
				<input type="submit" name="save" value="<?=GetMessage("SUP_SAVE")?>" />&nbsp;
				<input type="reset" value="<?=GetMessage("SUP_RESET")?>" />
                <?if((empty($arResult["TICKET"]["UF_TASK_FLAG"])) && $arResult["ACSSES_US_GR"] == "Y"):?>
                    <input type="submit" name="task" value="<?=GetMessage("SUP_CR_TASK")?>" />
                    <input type="hidden" value="<?=GetMessage("SUP_L_MESS");?>" name="l_mess" />
                    <input type="hidden" value="<?=GetMessage("SUP_L_MESS2");?>" name="l_mess2" />
                    <input type="hidden" value="<?=GetMessage("SUP_L_TASK");?>" name="l_task" />
                <?endif;?>
				<input type="hidden" value="Y" name="apply" />

                <?if($arResult["TICKET"]["SOURCE_SID"] == "email"):?>
                    <input type="hidden" value="<?=$arResult["TICKET"]["EMAIL"]?>" name="email" />
                <?endif;?>
                <?if(!empty($arParams["GROUP_ACC"])):?>
                    <input type="hidden" value="<?=$arParams["GROUP_ACC"]?>" name="grp" />
                <?endif;?>
			</div>
		</div>
	</div>






	<script type="text/javascript">
	BX.ready(function(){
		var buttons = BX.findChildren(document.forms['support_edit'], {attr:{type:'submit'}});
		for (i in buttons)
		{
			BX.bind(buttons[i], "click", function(e) {
				setTimeout(function(){
					var _buttons = BX.findChildren(document.forms['support_edit'], {attr:{type:'submit'}});
					for (j in _buttons)
					{
						_buttons[j].disabled = true;
					}

				}, 30);
			});
		}
	});
	</script>

</form>
<p><span class="starrequired">*</span><?=GetMessage("SUP_REQ")?></p>