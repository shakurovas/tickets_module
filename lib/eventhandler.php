<?php

namespace MCart\CreatingTaskFromTiket;
IncludeModuleLangFile(__FILE__);

class EventHandler
{
    function MyOnAdminContextMenuShow(&$items)
    {
        \CJSCore::Init(array("jquery"));
        $isTaskCreated = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields('SUPPORT', $_GET['ID'])['UF_TASK_FLAG']['VALUE'];

        // добавим кнопку "создать задачу" на страницу редактирования (уже созданного) обращения в техподдержку
        if($GLOBALS["APPLICATION"]->GetCurPage(true) == "/bitrix/admin/ticket_edit.php" && !empty($_GET['ID'])) {
            if (!$isTaskCreated) { // если задача для этого обращения ещё не была создана, она создастся
                $items[] = array("TEXT" => GetMessage('CREATE_TASK'), "ICON" => "", "TITLE" => GetMessage('CREATE_TASK'), "LINK" => '#', "LINK_PARAM" => 'class="create-task-btn adm-btn" onclick="
                    $.ajax({
                        url: \'/bitrix/admin/ticket_edit_ajax.php\',
                        type: \'get\',
                        contentType: \'application/json; charset=utf-8\',
                        data: {supportTicketId: ' . $_GET['ID'] . '},
                        success: function(data) {
                            var returnedData = JSON.parse(data);
                            console.log(returnedData);
                            window.location.replace(\'/company/personal/user/1/tasks/task/edit/\' + returnedData[\'lastTaskId\'] + \'/\');
                        },
                        error: function(data) {
                            var returnedData = JSON.parse(data);
                            console.log(returnedData);
                        }
                    });
                "');
            } else { // если задача уже была создана для этого обращения, будет выскакивать алерт с вопросом, точно ли хотят создать ещё одну задачу 
                $items[] = array("TEXT" => GetMessage('CREATE_TASK'), "ICON" => "", "TITLE" => GetMessage('CREATE_TASK'), "LINK" => '#', "LINK_PARAM" => 'class="create-task-btn adm-btn" onclick="
                    if (confirm(\''. GetMessage('QUESTION_ABOUT_NEW_ONE_TASK') . '\')) {
                        $.ajax({
                            url: \'/bitrix/admin/ticket_edit_ajax.php\',
                            type: \'get\',
                            contentType: \'application/json; charset=utf-8\',
                            data: {supportTicketId: ' . $_GET['ID'] . '},
                            success: function(data) {
                                var returnedData = JSON.parse(data);
                                console.log(returnedData);
                                window.location.replace(\'/company/personal/user/1/tasks/task/edit/\' + returnedData[\'lastTaskId\'] + \'/\');
                            },
                            error: function(data) {
                                var returnedData = JSON.parse(data);
                                console.log(returnedData);
                            }
                        });
                    } else {
                        return false;
                    }
                "');
            }
        }
    }
}