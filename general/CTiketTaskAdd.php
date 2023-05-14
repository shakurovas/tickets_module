<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Type;


class CTiketTaskAdd
{
    function OnAfterTicketAddHandlerTaskAdd($arFields)
    {
        $rs = CTicket::GetList($by = "ID", $order = "asc", array("ID" => $arFields["ID"]), $isFiltered, "N", "N", "N", false, Array("SELECT" => array("UF_TASK_FLAG")));

        while ($ar = $rs->Fetch()) {
            $taskflag = $ar["UF_TASK_FLAG"];
            $title = $ar["TITLE"];
            $owner = $ar["OWNER_USER_ID"];
            $tiketid = "<a href='".$_SERVER["REQUEST_SCHEME"]. "://" .$_SERVER["SERVER_NAME"]. "/services/support.php?ID=" .$ar["ID"]. "'>".$_SERVER["REQUEST_SCHEME"]. "://" .$_SERVER["SERVER_NAME"]. "/services/support.php?ID=" .$ar["ID"]. "</a>";
        }

        if(!empty($_POST["task"]) && empty($taskflag)) {

            if (!CModule::IncludeModule("iblock"))
                return false;

            $mess = CTicket::GetMessageList($by = "ID", $order = "asc", array("TICKET_ID" => $arFields["ID"]), $c, "N");
            
            while ($ar = $mess->GetNext()) {
                $arMess[] = $ar;
            }

            $message = nl2br($arMess[0]["MESSAGE"]);
            $mesID = $arMess[0]["ID"];

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

            $rsSectEx = CIBlockSection::GetList(array('left_margin' => 'asc'), array("IBLOCK_ID" => $departmentsIBlockId, "ID" => $_POST["DEP_ID"]), false, array("UF_HEAD"));
            while ($arSect = $rsSectEx->GetNext())
            {
                $arDepMain = $arSect["UF_HEAD"];
            }

            global $USER;

            $filter = Array("UF_DEPARTMENT" => Array($_POST["DEP_ID"]));
            $rsUsers = CUser::GetList(($by = "ID"), ($order = "desc"), $filter);
            while ($arUser = $rsUsers->Fetch()) {
                $arSpecUser[] = $arUser["ID"];
            }

            $id_created = $USER->GetID();

            $arFieldsTask = Array(
                "TITLE" => $title,
                "DESCRIPTION" => $_POST["l_task"]." ".$tiketid."<br>".$message,
                "CREATED_BY" => $id_created,
                "RESPONSIBLE_ID" => $arDepMain,
                "GROUP_ID" => $_POST["grp"],
                "ACCOMPLICES" => $arSpecUser,
                "AUDITORS" => array($owner)
            );

            $obTask = new CTasks;
            $ID = $obTask->Add($arFieldsTask);
            $success = ($ID > 0);

            $rsUser = CUser::GetByID($id_created);
            $arUser = $rsUser->Fetch();
            $linktotask = "";
            if(!empty($arUser) && ($arUser["ID"] > 0)) {
                $linktotask = $_SERVER["REQUEST_SCHEME"]. "://" .$_SERVER["SERVER_NAME"]. "/company/personal/user/".$arUser["ID"]."/tasks/task/view/".$ID."/";

            }
            if ($success) {

                //���������� ������ � ������
                if (CModule::IncludeModule('disk')) {
                    global $USER_FIELD_MANAGER;
                    global $DB;

                    $z = $DB->Query("SELECT FILE_ID FROM b_ticket_message_2_file WHERE MESSAGE_ID='$mesID'", false, $err_mess.__LINE__);

                    $arrAttachments = array();
                    while ($zr = $z->Fetch()) {
                        $pathFile = CFile::GetPath($zr["FILE_ID"]);
                        $arFile = CFile::MakeFileArray($pathFile);

                        $storage = Bitrix\Disk\Driver::getInstance()->getStorageByUserId($id_created);
                        $folder = $storage->getFolderForUploadedFiles();

                        $file = $folder->uploadFile($arFile, array(
                            'NAME' => $arFile["name"],
                            'CREATED_BY' => $id_created
                        ), array(), true);


                        if ($folder->getErrors()) {

                        } else {
                            $entityID = $file->getId();
                            if (intval($entityID) > 0) {
                                $arrAttachments[] = "n" . $entityID;

                            }
                        }
                    }

                    if (!empty($arrAttachments))
                        $USER_FIELD_MANAGER->Update("TASKS_TASK", $ID, array("UF_TASK_WEBDAV_FILES" =>$arrAttachments), $id_created);
                }


                if(!empty($_POST["email"])){
                    $arSendFields=array(
                        'TITLE'=>$_POST["l_mess"]." ".$ID,
                        'TEXT'=>$_POST["l_mess"]." ".$linktotask.". ".$_POST["l_mess2"],
                        'EMAIL'=>$_POST["email"]
                    );
                    CEvent::Send("TASK_TIKET", "s1", $arSendFields);
                } else {
                    $arSendFields=array(
                        'TITLE'=>$_POST["l_mess"]." ".$ID,
                        'TEXT'=>$_POST["l_mess"]." ".$linktotask.". ".$_POST["l_mess2"],
                        'EMAIL'=>$arUser["EMAIL"]
                    );
                    CEvent::Send("TASK_TIKET", "s1", $arSendFields);
                }
                $arFieldsTicket = array(
                    "CLOSE" => "Y",
                    "UF_TASK_FLAG" => "Y",
                    "UF_TASK_ID" => $ID,
                    "MESSAGE" => $_POST["l_mess"]." ".$linktotask,
                );
                $TICKET_ID = $arFields["ID"];
                CTicket::Set($arFieldsTicket, $MESS_ID, $TICKET_ID, "N");
                header("Location: /tasktiket/?ID=".$ID);
                exit();
            }
        }


    }

}