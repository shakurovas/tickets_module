<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\CJSCore::Init(array("jquery"));
IncludeModuleLangFile(__FILE__);

$supportTicketId = $_GET['supportTicketId'];

if (CModule::IncludeModule("support") && CModule::IncludeModule("tasks") && CModule::IncludeModule("intranet")) {
    $ticket = \CTicket::GetList($by, $order, array('ID' => $supportTicketId))->fetch();

    // ������� ��������� � ���� ��������� (�. �. ��� ����� ��������� � ������: ����� ��������� � ������ �� ����)
    $messagesString = '';
    $ticketMessages = \CTicket::GetMessageList(
        $by,
        $order,
        array('TICKET_ID' => $supportTicketId),
        false
    );

    while ($message = $ticketMessages->fetch()) {
        $messagesString .= $message['MESSAGE'] . PHP_EOL . '[URL=/bitrix/admin/ticket_edit.php?ID=' . $supportTicketId . '&lang=' . LANG . ']' . GetMessage('TO_APPEAL') . '[/URL]';
        break;
    }

    // ������� id �������� ���������, ������� ������������� �������������, ����� �������� ����� ������������� �� ������
    // �������� ����������� � ��������� � ������� (���������������� ����) / � �������� � ���������� ����������
    $entityId = 'SUPPORT'; // ������ (������������)
    $valueId = $supportTicketId; // ID ���������� (ID ��������� � ������������)
    $fieldId = 'UF_DEPARTMENT_SECTION'; // ��� ����

    $result = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields($entityId, $valueId);
    $chosenDepIBlockId = $result[$fieldId]['VALUE'];

    // ������� id ��������� � ���������������
    $obDepartmentsIBlock = CIBlock::GetList(
        array(), 
        array(
            'CODE' => 'departments'
        )
    );

    if ($departmentsIBlock = $obDepartmentsIBlock->fetch()) {
        $departmentsIBlockId = $departmentsIBlock['ID'];
    }
    
    $obDepartmentSection = CIBlockSection::GetList(
        array('left_margin' => 'asc'),
        array(
            "IBLOCK_ID" => $departmentsIBlockId,
            "ID" => $chosenDepIBlockId
        ),
        false,
        array("UF_*")
    );

    while ($arSect = $obDepartmentSection->GetNext())
    {
        $arDepMain = $arSect["UF_HEAD"];
    }

    // ������� ���������� ������������, �� �������� �������� ����� (����� ������� �� ���������������)
    $accompliciesIds = [];

    $arUsers = \CIntranetUtils::GetSubordinateEmployees($arDepMain, true);
    while($user = $arUsers->fetch()){
        $accompliciesIds[] = $user['ID'];
    }

    // ������� ����� ������, ��������� � �������
    $arFields = Array(
        "TITLE" => $ticket['TITLE'],
        "DESCRIPTION" => $messagesString,
        "RESPONSIBLE_ID" => $arDepMain ?: ($ticket['RESPONSIBLE_USER_ID'] ?: $ticket['CREATED_USER_ID']), // ���� ������� �������������, ������������� ����� ��� �����, ���� �� ������, �� ������������ �� ���������, ���� � �� ��� �� ������. �� ����� ���������
        "ACCOMPLICES" => $arDepMain ? array_diff($accompliciesIds, array($arDepMain)) : array_diff($accompliciesIds, array($ticket['RESPONSIBLE_USER_ID'])),
        "AUDITORS" => array($ticket['OWNER_USER_ID']),
        "DEADLINE" => $ticket['AUTO_CLOSE_DATE'],
        "PRIORITY" => $ticket['CRITICALITY_ID'],
        "ALLOW_TIME_TRACKING" => "Y",
        "CREATED_BY" => $ticket['CREATED_USER_ID'],
        "END_DATE_PLAN" => $ticket['AUTO_CLOSE_DATE'],
        "ALLOW_CHANGE_DEADLINE" => "Y"
    );

    $obNewTask = \CTaskItem::add($arFields, $USER->GetID());

    // ����� ��������� (������ ��� ������������ ������)
    $lastTaskId = $obNewTask['ID'];

    // ������� �����, ������������ � ���������, � ������� �� � ����������� ������
    $arFilesIds = [];
    $rsFiles = \CTicket::GetFileList($v1="s_1", $v2="asc", array("TICKET_ID" => $supportTicketId));
    while ($arFile = $rsFiles->Fetch())
    {
       $arFilesIds[] = $arFile['ID'];
    }

    foreach ($arFilesIds as $num => $id) {
        $storage = Bitrix\Disk\Driver::getInstance()->getStorageByUserId($USER->GetID());
        $folder = $storage->getFolderForUploadedFiles();
        $arFile = CFile::MakeFileArray($id);
        $file = $folder->uploadFile($arFile, array(
            'NAME' => $arFile["name"],
            'CREATED_BY' => $USER->GetID()
        ), array(), true);
        $fileId = $file->getId();
    
        $oTaskItem = new CTaskItem($lastTaskId, $USER->GetID());
        $taskData = $oTaskItem->getData(false);
        
        if (!$taskData['UF_TASK_WEBDAV_FILES']) { // ���� ������ � ������� ��� ������, �� �� ������ �������, ������ ��������� ���� ����� ��������
            $oTaskItem->update(array("UF_TASK_WEBDAV_FILES" => [0 => "n{$fileId}"]));
        } else { // ���� ������ � ������� ��� �� ������, �� ������� ������� - ����� � ������������
            $oTaskItem->update(array("UF_TASK_WEBDAV_FILES" => array_merge($taskData['UF_TASK_WEBDAV_FILES'], ["n{$fileId}"])));
        }
    }

    // ������� ������� ��������� - ��������� Y � ���� UF_TASK_FLAG (��, ��� ������ �������)
    // � ����� ��������� ������ � UF_TASK_ID
    $arFields = array(
        "UF_TASK_FLAG" => "Y",
        "UF_TASK_ID" => $lastTaskId,
        "CLOSE" => "Y"
    );

    $updatedTicket = \CTicket::Set($arFields, $MESSAGE_ID, $ticket['ID'], "N");

}

echo json_encode(array('success' => 'true', 'lastTaskId' => $lastTaskId));
