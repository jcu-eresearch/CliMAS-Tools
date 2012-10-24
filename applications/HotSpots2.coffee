
# CoffeeScript - this file compiles to JavaScript.
# http://coffeescript.org/#installation
#
#
#
$ ->
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

        console.log [year, scenario, output, groupLevel, groupName]

        if output == 'view'
            #
            # they want to see the map
            #
            $("""
                <div class="popupwrapper" style="display: none">
                    <div class="toolbar north"><button class="close">close &times;</button></div>
                    <div id="popupmap" class="popupmap"></div>
                    <div class="toolbar south"><button class="close">close &times;</button></div>
            """).appendTo('body').show('fade', 1000)

            $('.popupwrapper button.close').click (e)->
                $('.popupwrapper').hide 'fade', ()->
                    $('.popupwrapper').remove()

            map = L.map('popupmap', {
                minZoom: 3
#                crs: L.CRS.EPSG3857
                crs: L.CRS.EPSG4326
#                crs: L.CRS.Simple
            }).setView([-27, 135], 4)

            # 831e24daed21488e8205aa95e2a14787 is Daniel's CloudMade API key
            L.tileLayer('http://{s}.tile.cloudmade.com/831e24daed21488e8205aa95e2a14787/997/256/{z}/{x}/{y}.png', {
                attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>'
                maxZoom: 18
            }).addTo map

#            imageUrl = "http://tdh-tools-1.hpc.jcu.edu.au:81/eresearch/output/MapserverImages/5084c927_5e09_ffffffff.png"
#            imageUrl = "http://tdh-tools-1.hpc.jcu.edu.au:81/eresearch/output/MapserverImages/50878b8d_5e0a_2.png"
#            imageUrl = "http://tdh-tools-1.hpc.jcu.edu.au:81/eresearch/output/MapserverImages/50878d11_5e03_4.png"
            imageUrl = "http://tdh-tools-1.hpc.jcu.edu.au:81/eresearch/output/MapserverImages/50878f91_512_ffffffff.png"

            #
            # image bounds: [[-8.2688444363376, 111.975], [-46.231133563662, 156.2749]]
            # or maybe bounds should be: [[-9.9749780, 111.975], [-44.525, 156.2749]]
            #
#            imageBounds = [[-8.2688444363376, 111.975], [-46.231133563662, 156.2749]]
#            imageBounds = [[-9.9749780, 111.975], [-44.525, 156.2749]]
            imageBounds = [[-8.2687905894135, 111.9749], [-46.231165, 156.2749]]
            L.imageOverlay(imageUrl, imageBounds, {
                opacity: 0.5
            }).addTo map

        e.preventDefault();





