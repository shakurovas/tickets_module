<?php
IncludeModuleLangFile(__FILE__);

class mcart_creatingtaskfromtiket extends CModule
{
	var $MODULE_ID = "mcart.creatingtaskfromtiket";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	//var $MODULE_GROUP_RIGHTS = "Y";
    var $PARTNER_NAME;
    var $PARTNER_URI;

	function mcart_creatingtaskfromtiket()
	{
        $this->PARTNER_NAME = GetMessage('CREATE_TASK_FROME_TIKET_PARTNER_NAME');
        $this->PARTNER_URI = "http://mcart.ru/";
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = GetMessage("CREATE_TASK_FROME_TIKET_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("CREATE_TASK_FROME_TIKET_MODULE_DESCRIPTION");
	}

	function DoInstall()
	{
		$this->InstallFiles();
		$this->InstallDB();
	}

	function InstallDB()
	{

		RegisterModule("mcart.creatingtaskfromtiket");

		//$eventManager = \Bitrix\Main\EventManager::getInstance();

		//$eventManager->registerEventHandler("support", "OnAfterTicketUpdate", "mcart.creatingtaskfromtiket", "CTiketTaskAdd", "OnAfterTicketAddHandlerTaskAdd");
        RegisterModuleDependences("support", "OnAfterTicketAdd", "mcart.creatingtaskfromtiket", "CTiketTaskAdd", "OnAfterTicketAddHandlerTaskAdd");
        RegisterModuleDependences("support", "OnAfterTicketUpdate", "mcart.creatingtaskfromtiket", "CTiketTaskAdd", "OnAfterTicketAddHandlerTaskAdd");
		RegisterModuleDependences('main', 'OnAdminContextMenuShow', 'mcart.creatingtaskfromtiket', '\\MCart\\CreatingTaskFromTiket\\EventHandler', 'MyOnAdminContextMenuShow');
        return true;
	}

	function InstallFiles()
	{
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.creatingtaskfromtiket/install/templates/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mcart.creatingtaskfromtiket/install/tasktiket/", $_SERVER["DOCUMENT_ROOT"]."/tasktiket", true, true);
		
		if (file_exists('/local/modules/mcart/mcart.creatingtaskfromtiket/admin/ajax/ticket_edit_ajax.php')) {
			copy(
				$_SERVER["DOCUMENT_ROOT"] . '/local/modules/mcart.creatingtaskfromtiket/admin/ajax/ticket_edit_ajax.php',
				$_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin/ticket_edit_ajax.php'
			);
		} else {
			copy(
				$_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/mcart.creatingtaskfromtiket/admin/ajax/ticket_edit_ajax.php',
				$_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin/ticket_edit_ajax.php'
			);
		}

		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function DoUninstall()
	{

			$this->UnInstallDB();
			$this->UnInstallFiles();
	}

	function UnInstallDB()
	{
		//$eventManager = \Bitrix\Main\EventManager::getInstance();
		//$eventManager->unRegisterEventHandler('support', 'OnAfterTicketAdd', 'mcart.creatingtaskfromtiket', 'CTiketTaskAdd', 'OnAfterTicketAddHandler');
        UnRegisterModuleDependences("support", "OnAfterTicketAdd", "mcart.creatingtaskfromtiket", "CTiketTaskAdd", "OnAfterTicketAddHandlerTaskAdd");
        UnRegisterModuleDependences("support", "OnAfterTicketUpdate", "mcart.creatingtaskfromtiket", "CTiketTaskAdd", "OnAfterTicketAddHandlerTaskAdd");
		UnRegisterModuleDependences('main', 'OnAdminContextMenuShow', 'mcart.creatingtaskfromtiket', '\\MCart\\CreatingTaskFromTiket\\EventHandler', 'MyOnAdminContextMenuShow');
        UnRegisterModule("mcart.creatingtaskfromtiket");

		return true;
	}

	function UnInstallFiles()
	{
        DeleteDirFilesEx("/bitrix/templates/bitrix24/components/bitrix/support.wizard/");
        DeleteDirFilesEx("/bitrix/templates/bitrix24/components/bitrix/support.ticket_medi/");
        DeleteDirFilesEx("/tasktiket/");
		unlink($_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin/ticket_edit_ajax.php');
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}
}
