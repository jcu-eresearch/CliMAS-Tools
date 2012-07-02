<?php
session_start();

    include_once 'includes.php';

    if (!array_key_exists("name", $_GET)) return;

    $info = $_SESSION[$_GET['name']];

    //** print_r($info);

    header("Content-type: application/**vnd.google-earth.kml+xml");
    header('Content-Disposition: attachment; filename="'.$info['fn'].'.kml"');

    $link = database::connect($info['db']);
    $result = database::query($info['sql'], $link);
    database::disconnect($link);

    echo kmlHeader();

    if (!($result == "" || $result == FALSE))
        echo toPlacemarkers($info, $result);

    echo kmlFooter();

    unset($_SESSION[$_GET['name']]);


    function toPlacemarkers($info, $src)
    {

        $col_id  = array_key_exists("id"   , $info) ? $info["id" ] : "id";
        $col_lat = array_key_exists("lat"  , $info) ? $info["lat"] : "lat";
        $col_lon = array_key_exists("lon"  , $info) ? $info["lon"] : "lon";

        foreach ($src as $rowID => $row)
        {
            $result .= toPlacemarker($row, $col_id, $col_lat, $col_lon);
        }

        return $result;


    }

    function toPlacemarker($row, $col_id, $col_lat, $col_lon)
    {

        $id  = $row[$col_id];
        $lat = $row[$col_lat];
        $lon = $row[$col_lon];

$placeMark = <<<PLACEMARK
<Placemark>
    <name>$id</**name>
    <description>$id </**description>
    <LookAt>
            <longitude>$lon</**longitude>
            <latitude>$lat</**latitude>
            <altitude>0</**altitude>
            <heading>0.0</**heading>
            <tilt>0</**tilt>
            <range>100</**range>
            <altitudeMode>relativeToGround</**altitudeMode>
            <gx:altitudeMode>relativeToSeaFloor</**gx:altitudeMode>
    </**LookAt>
    <styleUrl>#msn_ylw-pushpin</**styleUrl>
    <Point>
            <altitudeMode>absolute</**altitudeMode>
            <coordinates>$lon,$lat,100</**coordinates>
    </**Point>
</**Placemark>        
PLACEMARK;

        return $placeMark."\n";
        
    }


    function kmlHeader()
    {

$result=<<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://**www.opengis.net//**2.2" xmlns:gx="http://**www.google.com//**ext//**//**kml//**//**2005/**Atom">
<Document>
<name>from sql</**name>
<StyleMap id="msn_ylw-pushpin">
        <Pair>
                <key>normal</**key>
                <styleUrl>#sn_ylw-pushpin</**styleUrl>
        </**Pair>
        <Pair>
                <key>highlight</**key>
                <styleUrl>#sh_ylw-pushpin</**styleUrl>
        </**Pair>
</**StyleMap>
<Style id="sh_ylw-pushpin">
        <IconStyle>
                <scale>1.3</**scale>
                <Icon>
                        <href>http://**maps.google.com//**kml//**ylw-pushpin.png</**href>
                </**Icon>
                <hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/**>
        </**IconStyle>
</**Style>
<Style id="sn_ylw-pushpin">
        <IconStyle>
                <scale>1.1</**scale>
                <Icon>
                        <href>http://**maps.google.com//**kml//**ylw-pushpin.png</**href>
                </**Icon>
                <hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/**>
        </**IconStyle>
</**Style>
KML;

    return $result;

    }


    function kmlFooter()
    {
        return "<//**kml>";

    }

?>