<?php
/**
 * Hotspots / Species Richness Tool
 */
session_start();
include_once dirname(__FILE__).'/includes.php';
if (php_sapi_name() == "cli") return;

$cmd = htmlutil::ValueFromGet('cmd',''); // if we have a command_id on the url then they have returned.

$pagetitle = "CliMAS Biodiversity";
$pagesubtitle = "Visualising biodiversity across Australia";

if (array_key_exists('page', $_GET)) {
    $page = $_GET['page'];
} else {
    $page = null;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo preg_replace("/<[^>]*>/", "", $pagetitle); ?></title>

    <link rel="shortcut icon" href="<?php echo configuration::IconSource(); ?>favicon-trans.png" />

    <link type="text/css" href="css/start/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <link type="text/css" href="css/selectMenu.css" rel="stylesheet" />
    <link type="text/css" href="styles.css"         rel="stylesheet" />
    <link type="text/css" href="HotSpots.css"       rel="stylesheet" />

    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.css" />
    <!--[if lte IE 8]>
        <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.ie.css" />
    <![endif]-->
    <script src="http://cdn.leafletjs.com/leaflet-0.4/leaflet.js"></script>

    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.8.21.custom.min.js"></script>

    <script type="text/javascript" >
    <?php
    /*
    <script type="text/javascript" src="js/jquery.pulse.min.js"></script>
    <script type="text/javascript" src="js/selectMenu.js"></script>
    <script type="text/javascript" src="js/Utilities.js"></script>


    <script type="text/javascript" src="HotSpots.js"></script>
    */
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::Maxent_Species_Data_folder().'richness/' ,'mapfileRoot');

        echo htmlutil::AsJavaScriptSimpleVariable(configuration::ApplicationFolderWeb(),'ApplicationFolderWeb');
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::Maxent_Species_Data_folder_web().'richness/' ,'richness_folder');
        echo htmlutil::AsJavaScriptArrayFromFile(configuration::SourceDataFolder()."clazz_list.txt",'availableTaxa',true);
        echo htmlutil::AsJavaScriptArrayFromFile(configuration::SourceDataFolder()."family_list.txt",'availableFamily',true);
        echo htmlutil::AsJavaScriptArrayFromFile(configuration::SourceDataFolder()."genus_list.txt",'availableGenus',true);

        $species_taxa_data = matrix::Load(configuration::SourceDataFolder()."species_to_id.txt", ",");

        echo htmlutil::AsJavaScriptObjectArray($species_taxa_data,"name","id","availableSpecies");
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::IconSource(),'IconSource');
        echo htmlutil::AsJavaScriptSimpleVariable($cmd,'cmd');
        echo htmlutil::AsJavaScriptSimpleVariable(configuration::Maxent_Species_Data_folder_web(),'Maxent_Species_Data_folder_web');
     ?>
    </script>
    <script type="text/javascript" src="biodiversity.js"></script>
</head>
<body>

<div class="header clearfix">
    <a href="http://tropicaldatahub.org/"><img class="logo"
        src="../images/TDH_logo_medium.png"></a>
    <h1><?php echo $pagetitle; ?></h1>
    <h2><?php echo $pagesubtitle; ?></h2>
</div>

<?php
    $navSetup = array(
        'tabs' => array(
            'quick map' => 'biodiversity.php',
            'custom map' => 'HotSpots.php',
            'about the map tool' => 'biodiversity.php?page=about',
            'using the tool' => 'biodiversity.php?page=using',
            'the science' => 'biodiversity.php?page=science',
            'credits' => 'biodiversity.php?page=credits'
        ),
        'current' => 'biodiversity.php' . ( ($page) ? ('?page=' . $page) : '' )
    );
    include 'NavBar.php';
?>

<?php if ($page == 'about') { // ============================================== ?>

<div class="maincontent">
    <?php include 'biodiversity-about.html'; ?>
</div>

<?php } else if ($page == 'using') { // ======================================= ?>

<div class="maincontent">
    <?php include 'biodiversity-using.html'; ?>
</div>


<?php } else if ($page == 'science') { // ===================================== ?>

<div class="maincontent">
    <?php include 'biodiversity-science.html'; ?>
</div>


<?php } else if ($page == 'credits') { // ===================================== ?>

<div class="maincontent">
    <?php include 'biodiversity-credits.html'; ?>
</div>

<?php } else { // ============================================================= ?>

<div id="selectionpanel">

    <p class="toolintro">
        Examine biodiversity of land-based Australian birds, mammals, reptiles and amphibians, and projections of how biodiversity will change in the future.
    </p>

    <form id="prebakeform" action="">
        <div class="formsection taxon">

            <div class="onefield">
                <h3>Select a class</h3>
                    <?php

                        $clazzes = ClazzData::GetList();
                        $clazzesPlusAll = array_merge(array('all'), $clazzes);

                        foreach ($clazzesPlusAll as $clazz) {
                            echo "<label><input type='radio' class='clazz_selection " . $clazz . "'";
                            echo " name='clazztype' value='" . $clazz;
                            if ($clazz == 'all') {
                                echo "' checked='checked'>all vertebrates";
                            } else {
                                echo "'>";
                                echo ClazzData::clazzCommonName($clazz);
                            }
                            echo "</label>";
                            echo "";
                        }
                    ?>
            </div>

            <?php
                foreach ($clazzes as $clazz) {
                    $singleclazzname = ClazzData::clazzCommonName($clazz, false);
                    $pluralclazzname = ClazzData::clazzCommonName($clazz, true);
                    ?>
                    <div class="onefield taxa_selector <?php echo $clazz; ?>">
                        <h3>&hellip;and a taxon</h3>

                        <label><input type='radio' class='taxa all' name='<?php echo $clazz ?>_taxatype'
                            value='all' checked='checked'
                        >all <?php echo $pluralclazzname ?></label>
                        <label><input type='radio' class='taxa family' name='<?php echo $clazz ?>_taxatype'
                            value='family'
                        ><?php echo grammar::IndefiniteArticle($singleclazzname) ?> family</label>
                        <select class="taxa_dd family" name="chosen_family_<?php echo $clazz ?>">
                            <option disabled="disabled" selected="selected" value="invalid">choose a family...</option>
                            <?php
                                $families = FamilyData::GetList($clazz);
                                foreach ($families as $family) {
                                    echo "<option value='" . $family . "'>" . ucfirst(strtolower($family)) . "</option>\n";
                                }
                            ?>
                        </select>

                        <label><input type='radio' class='taxa genus' name='<?php echo $clazz ?>_taxatype'
                            value='genus'
                        ><?php echo grammar::IndefiniteArticle($singleclazzname) ?> genus</label>
                        <select class="taxa_dd genus" name="chosen_genus_<?php echo $clazz ?>">
                            <option disabled="disabled" selected="selected" value="invalid">choose a genus...</option>
                            <?php
                                $genuses = GenusData::GetList($clazz);
                                foreach ($genuses as $genus) {
                                    echo "<option value='" . $genus . "'>" . $genus . "</option>\n";
                                }
                            ?>
                        </select>

                    </div>
                    <?php
                }
            ?>
        </div>

        <div class="formsection">
            <div class="onefield year">
                <h3>Select a year</h3>

                <?php
                    echo "<label><input type='radio' class='year' name='year' checked='checked' value='current'>current</label>";

                    $yearFormat = "<label><input type='radio' class='year' name='year' value='{DataName}'>{DataName} {Description}</label>";
                    echo DatabaseClimate::GetFutureTimesDescriptions()->asFormattedString($yearFormat);
                ?>
            </div>
        </div>

        <div class="formsection">
            <div class="onefield scenario">
                <h3>Select an emission scenario</h3>

                <?php
                    $scenarios = array(
                        'RCP3PD' => 'RCP 2.6: Emissions reduce substantially',
                        'RCP45' => 'RCP 4.5: Emissions stabilise before 2100',
                        'RCP6'  => 'RCP 6: Emissions stabilise after 2100',
                        'RCP85' => 'RCP 8.5: Emissions increase, "business as usual"'
                    );
                    foreach ($scenarios as $name => $desc) {
                        echo "<label><input type='radio' class='scenario' name='scenario' checked='checked' value='".$name."'>".$desc."</label>";
                    }
                ?>
            </div>
        </div>

        <div class="formsection">
            <div class="onefield output">
                <h3>Select an output option</h3>

                <?php
                    $outputs = array(
                        'download' => 'download ASCII grid',
                        'view' => 'view biodiversity map'
                    );
                    foreach ($outputs as $name => $desc) {
                        echo "<label ";
                        if ($name == 'download') echo " class='disabled' ";
                        echo "><input type='radio' class='ouput' name='output' checked='checked' ";
                        if ($name == 'download') echo " disabled='disabled' ";
                        echo " value='".$name."'>".$desc."</label>";
                    }
                ?>
                <button class="generate">fetch biodiversity map</button>
            </div>
        </div>

    </form>

    <p class="linkselsewhere">
        <a href="HotSpots.php">customised maps &raquo;</a>
    </p>

</div>

<?php } // ==================================================================== ?>
<?php include 'ToolsFooter.php' ?>

</body>
</html>
