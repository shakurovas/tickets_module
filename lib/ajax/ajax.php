<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\CJSCore::Init(array("jquery"));
IncludeModuleLangFile(__FILE__);

$supportTicketId = $_GET['supportTicketId'];

if (CModule::IncludeModule("support") && CModule::IncludeModule("tasks") && CModule::IncludeModule("intranet")) {
    $ticket = \CTicket::GetList($by, $order, array('ID' => $supportTicketId))->fetch();

    // получим сообщения в этом обращении (т. е. что будет описанием в задаче: текст обращения и ссылка на него)
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

    // получим id элемента инфоблока, который соответствует подразделению, глава которого будет ответственным по задаче
    // значение заполняется в обращении в админке (пользовательское поле) / в публичке в настройках компонента
    $entityId = 'SUPPORT'; // Объект (Техподдержка)
    $valueId = $supportTicketId; // ID экземпляра (ID обращения в техподдержку)
    $fieldId = 'UF_DEPARTMENT_SECTION'; // Код поля

    $result = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields($entityId, $valueId);
    $chosenDepIBlockId = $result[$fieldId]['VALUE'];

    // получим id инфоблока с подразделениями
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

    // получим подчинённых руководителя, на которого назначен тикет (чтобы сделать их соисполнителями)
    $accompliciesIds = [];

    $arUsers = \CIntranetUtils::GetSubordinateEmployees($arDepMain, true);
    while($user = $arUsers->fetch()){
        $accompliciesIds[] = $user['ID'];
    }

    // добавим новую задачу, связанную с тикетом
    $arFields = Array(
        "TITLE" => $ticket['TITLE'],
        "DESCRIPTION" => $messagesString,
        "RESPONSIBLE_ID" => $arDepMain ?: ($ticket['RESPONSIBLE_USER_ID'] ?: $ticket['CREATED_USER_ID']), // если указано подразделение, ответственным будет его глава, если не указан, то отвественный по обращению, если и он был не указан. то автор сообщения
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

    // номер последней (только что поставленной задачи)
    $lastTaskId = $obNewTask['ID'];

    // получим файлы, прикреплённые к обращению, и добавим их в создаваемую задачу
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
        
        if (!$taskData['UF_TASK_WEBDAV_FILES']) { // если массив с файлами ещё пустой, то не мёрджим массивы, просто добавляем одно новое значение
            $oTaskItem->update(array("UF_TASK_WEBDAV_FILES" => [0 => "n{$fileId}"]));
        } else { // если массив с файлами уже не пустой, то смёрджим массивы - новый и существующий
            $oTaskItem->update(array("UF_TASK_WEBDAV_FILES" => array_merge($taskData['UF_TASK_WEBDAV_FILES'], ["n{$fileId}"])));
        }
    }

    // обновим текщуее обращение - проставим Y в поле UF_TASK_FLAG (то, что задача создана)
    // и номер созданной задачи в UF_TASK_ID
    $arFields = array(
        "UF_TASK_FLAG" => "Y",
        "UF_TASK_ID" => $lastTaskId,
        "CLOSE" => "Y"
    );

    $updatedTicket = \CTicket::Set($arFields, $MESSAGE_ID, $ticket['ID'], "N");

}

echo json_encode(array('success' => 'true', 'lastTaskId' => $lastTaskId));
