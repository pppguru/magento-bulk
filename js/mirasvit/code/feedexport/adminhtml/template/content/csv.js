var FeedExportMapping = {
    rowRemove: function(e)
    {
        e.ancestors()[1].remove();
    },

    rowUp: function(e)
    {
        FeedExportMapping.rowMove(e, 'up');   
    },

    rowDown: function(e)
    {
        FeedExportMapping.rowMove(e, 'down');   
    },

    rowMove: function (e, direction)
    {
        var tr = e.ancestors()[1];
        var table = tr.parentNode;

        index = table.select('tr').indexOf(tr);
        
        var prev = 1;
        if (index > 0) {
            prev = index - 1; 
        }
        
        var next = table.select('tr').length - 2;
        if (index < table.select('tr').length - 1) {
            next = index + 1;
        } 
            
        prevli = table.select('tr')[prev];
        nextli = table.select('tr')[next];
          
        tr.remove();
            
        switch(direction){
            case 'up':
                prevli.insert({before : tr});
            break;
            case 'down':
                nextli.insert({after : tr});
            break;
        }
    },

    rowAdd: function()
    {
        $$('#mapping-table tr').last().insert({'after' : $$('#mapping-table tr').last().cloneNode(true)});
    },

    changeValueType: function(e)
    {          
        if (e.value == 'pattern') {
            e.parentNode.parentNode.select('[name="csv[mapping][value_pattern][]"]').first().style.display = 'block';
            e.parentNode.parentNode.select('[name="csv[mapping][value_attribute][]"]').first().style.display = 'none';
        } else {
            e.parentNode.parentNode.select('[name="csv[mapping][value_pattern][]"]').first().style.display = 'none';
            e.parentNode.parentNode.select('[name="csv[mapping][value_attribute][]"]').first().style.display = 'block';
        }
    },

    changeFormat: function (e)
    {
        if (e.value == 'xml') {
            $('tabs_xml_section').parentNode.style.display = 'block';
            $('tabs_csv_section').parentNode.style.display = 'none';
        } else if (e.value == 'csv' || e.value == 'txt') {
            $('tabs_xml_section').parentNode.style.display = 'none';
            $('tabs_csv_section').parentNode.style.display = 'block';
        }
    }
};