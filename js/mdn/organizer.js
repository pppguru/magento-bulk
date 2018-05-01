
//****************************************************************************************************************
//Give Admin URL
function addKeyParam()
{
    return '/key/' + FORM_KEY;
}

//****************************************************************************************************************
//Sublit form for task creation
function SubmitForm(taskId, gridGuid)
{
    
    var url;
    url = OrganizerSaveTaskUrl;
	
    //Ajax call
    var request = new Ajax.Request(
        url,
        {
            method:'POST',
            onSuccess: function onSuccess(transport)
            {
                elementValues = eval('(' + transport.responseText + ')');
                alert(elementValues['message']);

                //Refresh list for creation & empty form
                if (taskId == '')
                {
                    var entityType = document.getElementById('ot_entity_type').value;
                    var entityId = document.getElementById('ot_entity_id').value
                    RefreshTaskList(gridGuid, entityType, entityId);
                    document.getElementById('div_edit_task_' + taskId).style.display = 'none';
                    document.getElementById('edit_form_task_' + taskId).reset();
                }
            },
            onFailure: function onFailure()
            {
                alert('Error');
            },
            parameters: Form.serialize((taskId == '')?document.getElementById('edit_form_task_'):document.getElementById('row_edit' + gridGuid + '_task_' + taskId))
        }
        );
}

//****************************************************************************************************************
//Refresh task List
function RefreshTaskList(gridGuid, entityType, entityId)
{
    var div;
    div = document.getElementById('OrganizerGrid' + gridGuid);
    if (div)
    {
        //Define URL
        var url = OrganizerRefreshListUrl;

        new Ajax.Updater('OrganizerGrid' + gridGuid, url, {
            method: 'post',
            parameters : {
                'form_key': FORM_KEY,
                'entity_type': entityType,
                'entity_id': entityId
            },
            evalScripts : true
        });
    }
}

function RefreshTaskListOnDelete()
{
    window.location.reload();
}

//****************************************************************************************************************
//Delete a task
function Delete(taskId, gridGuid)
{
    if (window.confirm('Are you sure ?'))
    {
        var url = OrganizerDeleteTaskUrl;
        var request = new Ajax.Request(
            url,
            {
                method:'POST',
                onSuccess: function onSuccess(transport)
                {
                    //Delete table lines
                    var position = findRowPosition(taskId, gridGuid);
                    document.getElementById('OrganizerGrid' + gridGuid + '_table').deleteRow(position + 1);
                    document.getElementById('OrganizerGrid' + gridGuid + '_table').deleteRow(position);

                    RefreshTaskListOnDelete();
                },
                onFailure: function onFailure()
                {
                    alert('Error');
                },
                parameters : {
                    'form_key': FORM_KEY,
                    'ot_id': taskId
                }
            }
            );
    }
}

//****************************************************************************************************************
//Notify target
function Notify(taskId)
{
    var url = OrganizerNotifyUrl;
		
    var request = new Ajax.Request(
        url,
        {
            method:'POST',
            onSuccess: function onSuccess(transport)
            {
                alert('Target Notified');
            },
            onFailure: function onFailure()
            {
                alert('Error');
            },
            parameters : {
                'form_key': FORM_KEY,
                'ot_id': taskId
            }
        }
        );
}

//****************************************************************************************************************
//Display task to Edit
function editTask(taskId, gridGuid)
{
    //DEfine postion of the line in the table
    var position = findRowPosition(taskId, gridGuid);
	
    //Check for display or Hide
    var rowName = 'row_edit' + gridGuid + '_task_' + taskId;
    var existingRow = document.getElementById(rowName);
    if (existingRow != null)
    {
        if (existingRow.style.display == '')
            existingRow.style.display = 'none';
        else
            existingRow.style.display = '';
    }
    else
    {
        //Add line
        var tableID = 'OrganizerGrid' + gridGuid + '_table';
        var table = document.getElementById(tableID);
        var row = table.insertRow(position + 1);
        row.id = rowName;
        var cell1 = row.insertCell(0);
        cell1.colSpan = 10;
        var editDiv = document.createElement("div");
        editDiv.id = "div_edit" + gridGuid + "_task_" + taskId;
        cell1.appendChild(editDiv);

        //Load form for edition
        var url = OrganizerEditTaskUrl + 'ot_id/' + taskId + '/guid/' + gridGuid + addKeyParam();
        new Ajax.Updater(editDiv.id,
            url,
            {
                evalScripts:true
            }
            );
	    
    }
}

//****************************************************************************************************************
//Find position of the ligne for a task id
function findRowPosition(taskId, gridGuid)
{
    var tableID = 'OrganizerGrid' + gridGuid + '_table';
    var table = document.getElementById(tableID);
    var count = table.rows.length;
    var position = 0;
    var i;
    for (i=0;i<count;i++)
    {
        var colCount = table.rows[i].cells.length;
        if (colCount > 2)
        {
            if (table.rows[i].cells[0].innerHTML == taskId)
                return i;
        }
    }
	
    //alert('Task not found');
    return position;
}

//****************************************************************************************************************
//Display Hide task
function toggleNewTask(gridGuid)
{
    if (document.getElementById('div_edit_task_').style.display == '')
        document.getElementById('div_edit_task_').style.display = 'none';
    else
        document.getElementById('div_edit_task_').style.display = '';
}

//****************************************************************************************************************
//Display Hide method
function toggleDiv(divId)
{
    if (document.getElementById(divId))
    {
        if (document.getElementById(divId).style.display == '')
            document.getElementById(divId).style.display = 'none';
        else
            document.getElementById(divId).style.display = '';
    }
}