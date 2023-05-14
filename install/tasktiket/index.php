<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Title");
?><?$APPLICATION->IncludeComponent(
	"bitrix:tasks.iframe.popup",
	"wrap",
	Array(
		"ACTION" => "edit",
		"FORM_PARAMETERS" => array("ID"=>$_GET["ID"],"GROUP_ID"=>"",)
	),
false,
Array(
	'HIDE_ICONS' => 'Y'
)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>