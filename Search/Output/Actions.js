/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function actionsSelect(divID)
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
