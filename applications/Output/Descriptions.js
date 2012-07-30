/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function descriptionSelect(divID)
{
    var selectorDivID = 'Selector' + divID;
    
    var isSelected = document.getElementById(selectorDivID).className.indexOf('selected');

    if (isSelected == -1)
        document.getElementById(selectorDivID).classList.add('selected');
    else
        document.getElementById(selectorDivID).classList.remove('selected');
    

    select(divID);

}

/**
 * Response to server sending a set of IDS thru to the GUI
 * 
 */
function doReselect(ids)
{
    var i=0;
    for (i=0;i < ids.length;i++)
        document.getElementById('Selector' + ids[i]).className += " selected";

}




