#
# CoffeeScript - this file compiles to JavaScript.
# http://coffeescript.org/#installation
#
#
#
$ ->

    # monkey-patch a function into String to capitalise a word. We'll use this later.
    String::capped = -> @.charAt(0).toUpperCase() + @.substring(1).toLowerCase()

    $clazzRadios = $ '#selectionpanel .clazz_selection'
    $taxaSelectors = $ '#selectionpanel .taxa_selector'

    # hide all the taxa selectors
    $taxaSelectors.hide()

    #
    # show taxa selection when a class is selected
    #
    $clazzRadios.each (index, clazzRadio)->

        $taxaSelector = $taxaSelectors.filter('.' + clazzRadio.value)

        #
        # update radio buttons that choose a class
        #
        $(clazzRadio).change (event)->
            $taxaSelectors.hide()

            selectedClazz = $clazzRadios.filter(':checked').prop 'value'

            if selectedClazz != 'all'
                $taxaSelectors.filter('.' + selectedClazz).show 'blind'

        #
        # show family / genus dropdowns when appropriate
        #
        if clazzRadio.value != 'all'

            $taxaRadios = $taxaSelector.find '.taxa'
            $taxaDDs = $taxaSelector.find '.taxa_dd'
            $familyDD = $taxaSelector.find '.taxa_dd.family'
            $genusDD = $taxaSelector.find '.taxa_dd.genus'

            $taxaRadios.change (event)->
                $taxaDDs.css 'visibility', 'hidden'

                switch event.srcElement.value
                    when 'family' then $familyDD.css 'visibility', 'visible'
                    when 'genus'  then $genusDD.css 'visibility', 'visible'

    #
    # disable the emission scenario thingy when they choose "current"
    #
    $('#prebakeform .year').change (event)->
        if $('#prebakeform .year:checked').prop('value') == 'current'
            $('#prebakeform input:radio[name="scenario"]').attr 'disabled', true
            $('#prebakeform .scenario').addClass 'disabled'
        else
            $('#prebakeform input:radio[name="scenario"]').attr 'disabled', false
            $('#prebakeform .scenario').removeClass 'disabled'

    # now trigger that event
    $('#prebakeform .year').first().change()


    #
    # when they click the generate button..
    #
    $generate = $ '#prebakeform .generate'

    #
    # when any form fields change, update the submittable status
    #
    $('#prebakeform input').add('#prebakeform select').change (event)->
        # the only thing that can stop the form from being submittable is if the
        # user wants to see a single family or genus, but hasn't selected the
        # family or genus yet.  So:
        formIncomplete = false

        clazz = $('#prebakeform input:radio[name="clazztype"]:checked').val()
        if clazz? and clazz != 'all'
            taxaLevel = $("#prebakeform input:radio[name='#{clazz}_taxatype']:checked").val()
            if taxaLevel != 'all'
                groupName = $("#prebakeform select[name='chosen_#{taxaLevel}_#{clazz}']").val()
                if groupName == 'invalid'
                    formIncomplete = true

        $('#prebakeform .generate').attr 'disabled', formIncomplete


    $generate.click (e)->
        # collect our request details

        year = $('#prebakeform input:radio[name="year"]:checked').val()
        scenario = $('#prebakeform input:radio[name="scenario"]:checked').val()
        output = $('#prebakeform input:radio[name="output"]:checked').val()

        clazz = $('#prebakeform input:radio[name="clazztype"]:checked').val()
        groupLevel = 'clazz'
        groupName = clazz

        if clazz? and clazz != 'all'
            taxaLevel = $("#prebakeform input:radio[name='#{clazz}_taxatype']:checked").val()
            if taxaLevel == 'all'
                # if the taxa level is 'all', the group can stay 'clazz'
            else
                groupLevel = taxaLevel
                groupName = $("#prebakeform select[name='chosen_#{taxaLevel}_#{clazz}']").val()


        if output == 'download'
            #
            # they want ascii grid and metadata
            #
            # figure out the file name

            # .../ByFamily/COLUMBIDAE/biodiversity/RCP3PD_2015_Columbidae.zip

            path = 'https://eresearch.jcu.edu.au/tdh/datasets/Gilbert/source/'

            prefix = "#{scenario}_#{year}"
            prefix = '1990' if year is 'current'

            if groupLevel is 'clazz' and clazz is 'all'
                path += "biodiversity/#{prefix}_vertebrates.zip"

            else if groupLevel is 'clazz'
                path += "By#{groupLevel.capped()}/#{groupName}/biodiversity/"
                path += "#{prefix}_#{window.clazzinfo[groupName].plural}.zip"

            else
                path += "By#{groupLevel.capped()}/#{groupName}/biodiversity/"
                path += "#{prefix}_#{groupName.toLowerCase().capped()}.zip"                                

            window.location.href = path


        else if output == 'view'
            #
            # they want to see the map
            #

            # hit the prep url to unzip the asciigrid
            $.ajax 'BiodiversityPrep.php', {
                cache: false
                dataType: 'json'
                data: {
                    class: clazz
                    taxon: groupName
                    settings: "#{scenario}_#{year}"
                }
                success: (data, testStatus, jqx) ->

                    if not data.map_path
                        alert "Sorry, data for that selection is not available."

                    else
                        maptitle = 'Biodiversity of terrestrial '
                        if groupLevel is 'clazz' and clazz is 'all'
                            maptitle += 'vertebrates'
                        else if groupLevel is 'clazz'
                            maptitle += clazz.capped()
                        else
                            maptitle += "#{clazz.capped()} #{groupLevel} '#{groupName.capped()}'"

                        if year isnt 'current'
                            maptitle += " in #{year} at emission level #{scenario}"

                        $("""
                            <div class="popupwrapper" style="display: none">
                                <div class="toolbar north">
                                <button class="close">close &times;</button>
                                <div id="maptitle">#{maptitle}</div>
                                </div>
                                <div id="popupmap" class="popupmap"></div>
                                <div class="toolbar south"><button class="close">close &times;</button><div id="legend"></div></div>
                        """).appendTo('body').show('fade', 1000)

                        # pre-figure the layer name - it's the filename portion of the
                        # map_path, without the .map extension.
                        layer_name = data.map_path.replace(/.*\//, '').replace(/\.map$/, '')

                        # fetch the legend as a html template from MapServer
                        $('#legend').load('/cgi-bin/mapserv?mode=browse&layer=' + layer_name + '&map=' + data.map_path);

                        # add close behaviour to the close buttons
                        $('.popupwrapper button.close').click (e)->
                            $('.popupwrapper').hide 'fade', ()->
                                $('.popupwrapper').remove()

                        # create the map
                        map = L.map('popupmap', {
                            minZoom: 3
                        }).setView([-27, 135], 4)

                        # 831e24daed21488e8205aa95e2a14787 is Daniel's CloudMade API key
                        L.tileLayer('http://{s}.tile.cloudmade.com/831e24daed21488e8205aa95e2a14787/997/256/{z}/{x}/{y}.png', {
                            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>'
                            maxZoom: 18
                        }).addTo map

                        # add the selected layer to the map
                        data = new L.TileLayer.WMS("/cgi-bin/mapserv", {
                            layers: layer_name + '&map=' + data.map_path
                            format: 'image/png'
                            opacity: 0.75
                            transparent: true
                        }).addTo map
            }

        e.preventDefault();

