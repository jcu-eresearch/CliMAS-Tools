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
    
}

function openPopup(action)
{
    document.getElementById('popup_content').src = "popup.php?a=" + action;
    ToggleDisplay('popup');
}

function closePopup()
{
    ToggleDisplay('popup');
    
}

function ToggleDisplay(id)
{

    if (document.getElementById(id).style.display == "block")
        document.getElementById(id).style.display = "none";
    else
        document.getElementById(id).style.display = "block";
}


function okPopup()
{
    ToggleDisplay('popup');

    var iframe = document.getElementById('popup_content');
    var innerDoc = iframe.contentDocument || iframe.contentWindow.document;

    var selectedForm = innerDoc.getElementById('popupSelectedForm');
    
    selectedForm.submit();

}


function run(action)
{
    document.getElementById('DataSummary').src = "SearchResults.php?a=" + action;
}


