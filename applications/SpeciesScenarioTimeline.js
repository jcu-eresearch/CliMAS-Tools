
function screenSetup()
{

    if ($(window).width() > 1200)
        $('#thecontent').css("width",1200);
    else
        $('#thecontent').css("width",$(window).width());
        
}

function selectSpecies(src)
{
    $('#futures').attr('src',"SpeciesScenarioTimelineFutures.php?id=" + $(src).attr('tag'));
} 

$(document).ready(function(){

    screenSetup();


    $('.SpeciesButton')
        .click( function() {selectSpecies(this)} )
        ;
    

});
