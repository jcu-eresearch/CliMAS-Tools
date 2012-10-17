function action(action)
{
    location.href = "popup.php?a=" + action;
}
function value(action)
{
    location.href = "popup.php?a=" + action;
}

function selectIDs(ids)
{
    ids = ids.trim();

    if (ids == '') return;

    var idArray = ids.split(' ');

    var i=0;
    for (i=0;i < idArray.length;i++)
    {
        select(idArray[i]);
    }


    // if other pages define doReselect(ids)
    // it will be called - this is so a page can "reselect items that have been selected"
    if (window.doReselect) doReselect(idArray); // call other "reselect" rouitines

}


function select(id)
{
    id = id.trim();

    if (id == '') return;

    var fieldID = "selectedIDs";

    // add / remove this id from the form field selectedIDs
    var ids = document.getElementById(fieldID).value;

    //alert(ids.indexOf(id));

    if ( ids.indexOf(id) == -1)
        document.getElementById(fieldID).value += id + " ";
    else
        document.getElementById(fieldID).value = document.getElementById(fieldID).value.replace(id + " ","");



}
