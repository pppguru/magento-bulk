function toggleFilterMultiSelectForm(name)
{
    var id = name + '_checkboxes';
    if (document.getElementById(id).style.display == '')
        document.getElementById(id).style.display = 'none';
    else
        document.getElementById(id).style.display = '';
}