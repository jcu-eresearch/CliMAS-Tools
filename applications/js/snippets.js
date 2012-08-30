/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function toggleSpeciesData(src)
{
    // gets the data area
    var dataID = src.id.toString().replace('species_header_for_','species_data_for_');


    var options = {};
    $( "#" + dataID ).toggle( 'blind', options, 500 );

    $(src).toggleClass("ui-selected");


}

function postAddSpeciesScenarioModels(species_id,scenarioModelsStr,data)
{

    // data - all the fileid's that we are goiong to look at '

    var div = "";
    var div_header = "";
    var div_content = "";
    var div_timeline = "";



    var timesStr = "";

    var timeStrValues = null;

    var firstScenarioModelTime = "";  // holds the id of the quikclook image for the first time zone from the scenario model

    var selected = "";

    var firstTimeName = '';
    var firstAsciiGridID = '';
    var firstFullname = '';

    var sm = '';

    var scenario = '';
    var model = '';


    var data =  string2Array(Value(data['data']), "~");


    for (s = 0; s < data.length;  s++)
    {
        var smd = string2Array(Value(data[s]),'!');

        var scenario_model = string2Array(smd[0],'_');

        var scenario = scenario_model[0];
        var model = scenario_model[1];

        postAddSpeciesDisplaySingleScenarioModel(species_id,scenario,model,smd);

    }

        scenario_model = scenarios_models[s]; // get the scenario model pair


        timeStrValues = null;
        timesStr = "";

        firstScenarioModelTime = ''; // QuickLookID
        firstTimeName = '';

        firstAsciiGridID = '';
        firstFullname = '';

        sm = string2Array(scenario_model, '_');

        scenario = sm[0];
        model = sm[1];


        // get the timeline from data for this scenario_model
        for (d in data)
        {




            if (d.indexOf(scenarioModelname) != -1)
            {
                // here should be a time elemnt of the current scenaro_model

                timeStrValues = string2Array(data[d], '_');


                selected = "";
                if (firstScenarioModelTime == '')
                {
                    firstTimeName = timeStrValues[3];
                    firstScenarioModelTime = timeStrValues[4]; // set first time point
                    firstAsciiGridID = timeStrValues[5];
                    firstFullname = timeStrValues[6];
                    selected = ' checked="checked"  ';
                }

                timesStr += '<input onClick="scenarioModelSelectedButtonSet(this)" class="radio_'+speciesID + "_"+scenarioModelname+'" '+selected+' type="radio" name="radio_'+speciesID + "_"+scenarioModelname+'" id="'+data[d]+'" /><label class="time_radio" for="'+data[d]+'">'+ timeStrValues[3]+'</label>';

            }


        }


        var loading_img_src = IconSource +'wait.gif';
        var loading_msg = '<div id="loading_'+firstAsciiGridID +'"><img style="margin-left: 70px; margin-top: 50px; width:100px; height=100px;" src="'+loading_img_src+'"></div>';


        var firstImageSrc = ApplicationFolderWeb + 'applications/file.php?id=' + firstScenarioModelTime;

        div_header = '<div id="'+speciesID + '_' +scenarioModelname+'_image_header" style="padding:3px; height: 24px; float:none; clear: both; background-color: black; color: white;" >' + sm[0] + "&nbsp;&nbsp;&nbsp;&nbsp;" + sm[1] + '</div>';

        div_content = '<div id="'+speciesID + '_' +scenarioModelname+'_image_container" style="margin-left: 80px; height: 220px; float:none; clear: both;  overflow: hidden; " >'+loading_msg+'<img  onload="layerImageLoaded(\''+firstAsciiGridID +'\')"   id="'+speciesID + '_' +scenarioModelname+'_'+ firstTimeName +'_image" style="width: 70%; height: 300px;" src="'+firstImageSrc+'"></div>';

        div_timeline = '<div id="'+speciesID + '_' +scenarioModelname+'_times" style="margin-left: 1px; height: 40px;float:none; clear: both;" >'+timesStr+'</div>';

        div  = '<div class="scenaro_model_container" style="width: 100%; height: 280px;" >';
        div += div_header + div_content + div_timeline;
        div += '</div>';


        $('#'+species_data_id).append(div);

        $('#'+speciesID + '_' +scenarioModelname+'_times').buttonset();

        $('#'+speciesID + '_' +scenarioModelname+'_image_header').addClass("ui-corner-all");



        $('.time_radio')
            .css("font-size","0.8em");

        var firstImageId = speciesID + '_' +scenarioModelname+'_'+ firstTimeName +'_image';
        // tie data to first image
        $('#' + firstImageId).data("speciesID",speciesID);
        $('#' + firstImageId).data("AsciiGridID",firstAsciiGridID);
        $('#' + firstImageId).data("FullName",firstFullname);


        $('#'+firstImageId).data("scenario",  sm[0]);
        $('#'+firstImageId).data("model",     sm[1]);
        $('#'+firstImageId).data("time",      firstTimeName);

        $('#' + firstImageId).click(function() {userSelectedLayer(this); return false;})






}


function postAddSpeciesDisplaySingleScenarioModel(species_id,scenario,model,smd)
{


        var times_available = string2Array(smd[1],',');

        for (t = 0; t < time_available.length;  t++)
        {

            var time_available = string2Array(times_available[t],'=');

            var time = time_available[0];
            var available = time_available[1];



        }

        var prefix = species_id+'_'+scenario+'_'+model;


        var loading_img_src = IconSource +'wait.gif';
        var loading_msg = '<div id="loading_'+firstAsciiGridID +'"><img style="margin-left: 70px; margin-top: 50px; width:100px; height=100px;" src="'+loading_img_src+'"></div>';


        div_header = '<div id="'+prefix+'_image_header" style="padding:3px; height: 24px; float:none; clear: both; background-color: black; color: white;" >' + scenario + "&nbsp;&nbsp;&nbsp;&nbsp;" + model + '</div>';

        div_content = '<div id="'+prefix+'_image_container" style="margin-left: 80px; height: 220px; float:none; clear: both;  overflow: hidden; " >'+loading_msg+'<img  onload="layerImageLoaded(\''+firstAsciiGridID +'\')"   id="'+speciesID + '_' +scenarioModelname+'_'+ firstTimeName +'_image" style="width: 70%; height: 300px;" src="'+firstImageSrc+'"></div>';

        div_timeline = '<div id="'+prefix+'_times" style="margin-left: 1px; height: 40px;float:none; clear: both;" >'+timesStr+'</div>';

        div  = '<div class="scenaro_model_container" style="width: 100%; height: 280px;" >';
        div += div_header + div_content + div_timeline;
        div += '</div>';


        $('#'+species_data_id).append(div);

        $('#'+speciesID + '_' +scenarioModelname+'_times').buttonset();

        $('#'+speciesID + '_' +scenarioModelname+'_image_header').addClass("ui-corner-all");



        $('.time_radio')
            .css("font-size","0.8em");





}


function scenarioModelSelectedButtonSet(src)
{

    var id = src.id.toString();

    var values = id.split("_");  // speciesID_scenario_model_time_QuickLookFileID_AsciiGridFileID

    var speciesID = values[0];
    var scenario  = values[1];
    var model     = values[2];
    var time      = values[3];
    var QuickLookFileID = values[4];
    var AsciiGridFileID = values[5];
    var FullName = values[6];

    // I want to replace the src element of speciesID + '_' +scenarioModelname+'_image  with   this  QuickLookFileID
    var newImageSrc = ApplicationFolderWeb + 'applications/file.php?id=' + QuickLookFileID;

    var imageHolder = speciesID + '_' + scenario + "_" + model +'_image_container';

    var newImageID = speciesID + '_' + scenario + "_" + model + '_'+ time + '_image';


    $('#' + imageHolder).children().hide();

    if (exists('#'+newImageID))
    {
        $('#' + newImageID).show(); // show this one
    }
    else
    {

        var loading_img_src = IconSource +'wait.gif';

        var loading_msg = '<div id="loading_'+AsciiGridFileID +'"><img style=" margin-left: 70px; margin-top: 50px;  width:100px; height=100px;" src="'+loading_img_src+'"></div>';

        var div = loading_msg + '<img  onload="layerImageLoaded(\''+AsciiGridFileID +'\')"    id="'+newImageID + '" style=" width: 70%; height: 300px;" src="'+newImageSrc+'">';

        // setup image onloaded  -
        $('#' + imageHolder).append(div);

        // tie data to image
        $('#' + newImageID).data("speciesID",speciesID);
        $('#' + newImageID).data("AsciiGridID",AsciiGridFileID);
        $('#' + newImageID).data("FullName",FullName);
        $('#' + newImageID).data("scenario",  scenario);
        $('#' + newImageID).data("model",     model);
        $('#' + newImageID).data("time",      time);


        $('#' + newImageID).click(function() {userSelectedLayer(this); return false;})

    }



}

