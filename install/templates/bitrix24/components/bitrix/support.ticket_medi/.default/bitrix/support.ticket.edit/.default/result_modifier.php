<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!empty($arResult["TICKET"])) {
    $rs = CTicket::GetList($by = "ID", $order = "asc", array("ID" => $arResult["TICKET"]["ID"]), $isFiltered, "N", "N", "N", false, Array("SELECT" => array("UF_TASK_FLAG")));

    while ($ar = $rs->Fetch()) {
        $taskflag = $ar["UF_TASK_FLAG"];
    }

    $arResult["TICKET"]["UF_TASK_FLAG"] = $taskflag;
}
$textt = $arResult["TICKET"]["OWNER_SID"];

if (preg_match_all('~[-a-z0-9_]+(?:\\.[-a-z0-9_]+)*@[-a-z0-9]+(?:\\.[-a-z0-9]+)*\\.[a-z]+~i', $textt, $M, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
    foreach ($M as $m) {
        $arResult["TICKET"]["EMAIL"] = $m[0][0];
    }
}

if(!empty($arParams["SECTION_DEP"])){
    if (!CModule::IncludeModule("iblock"))
        return false;

    // получим id инфоблока подразделений
    $obDepartmentsIBlock = CIBlock::GetList(
        array(), 
        array(
            'CODE' => 'departments'
        )
    );

    if ($departmentsIBlock = $obDepartmentsIBlock->fetch()) {
        $departmentsIBlockId = $departmentsIBlock['ID'];
    }

    $rsSectEx = CIBlockSection::GetList(array('left_margin' => 'asc'), array("IBLOCK_ID" => $departmentsIBlockId, "ID" => $arParams["SECTION_DEP"]));
    $arDep = array();
    while ($arSect = $rsSectEx->GetNext())
    {
        $arDep[$arSect['ID']] = $arSect['NAME'];
    }

    $arResult["SECTION_DEP"] = $arDep;
}

global $USER;
$arGroups = CUser::GetUserGroup($USER->GetID());

$rsGroups = CGroup::GetList(($by="c_sort"), ($order="desc"), array("NAME" => "Техподдержка"));
if ($supportGroup = $rsGroups->fetch()) {
	$grSup = $supportGroup['ID'];
}

if(in_array($grSup, $arGroups)){
    $arResult["ACSSES_US_GR"] = "Y";
}

if(!empty($arParams["GROUP_ACC"])) {
    if (!CModule::IncludeModule("socialnetwork"))
        return false;

    $dbRequests = CSocNetUserToGroup::GetList(
        array(),
        array("GROUP_ID" => $arParams["GROUP_ACC"], "USER_ACTIVE" => "Y"),
        false,
        false,
        array("ID", "USER_ID")
    );

    while ($arRequests = $dbRequests->GetNext()) {
        $arUserGr[] = $arRequests["ID"];
    }

    global $USER;
    $us = $USER->GetID();

    if(in_array($us, $arUserGr)){
        $arResult["ACSSES_GR"] = "Y";
    }
}


?>
