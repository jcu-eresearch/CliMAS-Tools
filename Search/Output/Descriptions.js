/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function descriptionSelect(divID)
{

    var selectColour = "red";
    var unselectColour = "green";

    var selectorDivID = 'Selector' + divID;

    var color = document.getElementById(selectorDivID).style.backgroundColor;
    if (color == selectColour)
        document.getElementById(selectorDivID).style.backgroundColor = unselectColour
    else
        document.getElementById(selectorDivID).style.backgroundColor = selectColour;

    select(divID);

}

/**
 * Response to server sending a set of IDS thru to the GUI
 * 
 */
function doReselect(ids)
{
    var selectColour = "red";

    var i=0;
    for (i=0;i < ids.length;i++)
        document.getElementById('Selector' + ids[i]).style.backgroundColor = selectColour;

}


