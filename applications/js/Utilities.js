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
 *  @param removeString    remove This string from the ID
 *  @param replaceString   replace removed string with this one (default = "")
 *  
 *  @return array          IDs of selected elements
 *  
 */
function selected(rootSelector,findSelector, findClass,removeString, replaceString)
{
    
    
    if (rootSelector == null) return null;
    if (findSelector == null) return null;

    rootSelector = jQuery.trim(rootSelector);
    findSelector = jQuery.trim(findSelector);
 
    if (rootSelector == "") return null;
    if (findSelector == "") return null;
    
    
    if (replaceString == null)  replaceString = '';
    
    var resultID = "";
    
    var ids = new Array();
    var count = 0;
    jQuery.each(
        $(rootSelector).find(findSelector),
        function() 
        {
            
            
            if (findClass == null)
            {
                resultID = this.id.toString();
                if (removeString != null )
                    resultID = resultID.replace(removeString,replaceString);

                ids[count] = resultID;
                count++;

            }
            else
            {
                if ($(this).hasClass(findClass))
                {
                    resultID = this.id.toString();
                    if (removeString != null )
                        resultID = resultID.replace(removeString,replaceString);

                    ids[count] = resultID;
                    count++;
                }
                
            }
            
            
            
        }
    );
    

    return ids;
}


function selectedAsString(rootSelector,findSelector, findClass,delim,removeString, replaceString)
{
    if (delim == null) return null;

    var result = selected(rootSelector,findSelector, findClass,removeString, replaceString);
    
    if (result == null) return null;
    
    return result.join(delim);
    
}


