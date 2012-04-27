function GetExtentText() 
{
    var iframe = document.getElementById('GUI');
    var innerDoc = iframe.contentDocument || iframe.contentWindow.document;   
    return innerDoc.getElementById('extent').value
}

function ReloadDiv(divID) 
{
    document.getElementById(divID).src = document.getElementById(divID).src
}

function SetContent(url, divID) 
{
    document.getElementById(divID).src = url;    
}


function ReloadGUI() 
{
    ReloadDiv('GUI') ;
}

function SetDataSummary(url) 
{
    SetContent(url, "DataSummary");
}





function SetZoom(caller,zoom_value) {
    document.getElementById('ZoomFactor').value = zoom_value;   
}

function SetFullExtent() {
    ReloadGUI();
}


function zoomOut()
{
    var iframe = document.getElementById('GUI');
    var innerDoc = iframe.contentDocument || iframe.contentWindow.document;
    
    var map_form = innerDoc.getElementById("MAP_FORM");
    
    innerDoc.getElementById('ZoomFactor').value  = -2.0;
    
    innerDoc.getElementById('mapa').click();
    
    
    //map_form.submit();
    
    //var se = innerDoc.getElementById("extent");
    
    //alert(se.value);
    
    
}

