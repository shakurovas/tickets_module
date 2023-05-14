<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("iblock"))
    return false;
if(!CModule::IncludeModule("socialnetwork"))
    return false;
$arDep = array();

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


$rsSectEx = CIBlockSection::GetList(array('left_margin' => 'asc'), array("IBLOCK_ID" => $departmentsIBlockId));
while ($arSect = $rsSectEx->GetNext())
{
    $arDep[$arSect['ID']] = $arSect['NAME'];
}


$rsGr = CSocNetGroup::GetList(array("ID" => "DESC"), array(), false, false, array());

while($arGr = $rsGr->Fetch()){
    $arGrW[$arGr["ID"]] = $arGr["NAME"];
}


$arTemplateParameters = array(
    "SECTION_DEP" => array(
        "PARENT" => "ADDITIONAL_SETTINGS",
        "NAME" => GetMessage("SECTION_DEP"),
        "TYPE" => "LIST",
        "VALUES" => $arDep,
        "MULTIPLE" => "Y",
        "DEFAULT" => array(),
    ),
    "GROUP_ACC" => array(
        "PARENT" => "ADDITIONAL_SETTINGS",
        "NAME" => GetMessage("GROUP_ACC"),
        "TYPE" => "LIST",
        "VALUES" => $arGrW,
        "MULTIPLE" => "N",
        "DEFAULT" => array(),
    ),
);
?>