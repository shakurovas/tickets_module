<?php

namespace MCart\CreatingTaskFromTiket;
IncludeModuleLangFile(__FILE__);

class EventHandler
{
    function MyOnAdminContextMenuShow(&$items)
    {
        \CJSCore::Init(array("jquery"));
        $isTaskCreated = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields('SUPPORT', $_GET['ID'])['UF_TASK_FLAG']['VALUE'];

        // ������� ������ "������� ������" �� �������� �������������� (��� ����������) ��������� � ������������
        if($GLOBALS["APPLICATION"]->GetCurPage(true) == "/bitrix/admin/ticket_edit.php" && !empty($_GET['ID'])) {
            if (!$isTaskCreated) { // ���� ������ ��� ����� ��������� ��� �� ���� �������, ��� ���������
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
            } else { // ���� ������ ��� ���� ������� ��� ����� ���������, ����� ����������� ����� � ��������, ����� �� ����� ������� ��� ���� ������ 
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