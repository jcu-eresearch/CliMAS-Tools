/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function string2Array(str,delim)
{
    var result = null;
    if (str.indexOf(delim) == -1 )
    {
        result = new Array();
        result[0] = str;
    }
    else
        result = str.split(delim);
    
    return result;
}

function exists(selector)
{
    if ( $(selector).length ) return true;
    return false;
}


/**
 *  From the list of elements via findSelector
 *  
 *  @param rootSelector    find starts here
 *  @param findSelector    find this selector
 *  @param findClass,      considerit selected if it has this class
 *  @return array          IDs of selected elements
 *  
 */
function selected(rootSelector,findSelector, findClass)
{
    if (rootSelector == null) return null;
    if (findSelector == null) return null;
    if (findClass == null) return null;

    rootSelector = jQuery.trim(rootSelector);
    findSelector = jQuery.trim(findSelector);
    findClass = jQuery.trim(findClass);
 
    if (rootSelector == "") return null;
    if (findSelector == "") return null;
    if (findClass == "") return null;
    
    $("#temp").html('');
    
    var ids = new Array();
    var count = 0;
    jQuery.each(
        $(rootSelector).find(findSelector),
        function() 
        {
            if ($(this).hasClass(findClass))
            {
                ids[count] = this.id.toString();
                count++;
            }
            
        }
    );
    

    return ids;
}



