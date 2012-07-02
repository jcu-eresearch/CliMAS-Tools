<?php
class location {
    //put your code here

    private $name;
    private $coordinate;
    private $elevation;
    private $dynamic;
    private $relativeTo;


    /*
    * @method __construct
    * @param $_name {
    * @return mixed
    */
    public function __construct($_name) {
        $this->name = $_name;
    }



    /*
    * @method setName
    * @param _$value
    * @return mixed
    */
    public function setName(_$value)
    {
        $this->name = $_value;
    }


    /*
    * @method Name
    * @return mixed
    */
    public function Name()
    {
        return $this->name;
    }



    /*
    * @method setCoordinate
    * @param _$value
    * @return mixed
    */
    public function setCoordinate(_$value)
    {
        $this->coordinate = $_value;
    }


    /*
    * @method setCoordinateLatLong
    * @param $_lat
    * @param $_long
    * @return mixed
    */
    public function setCoordinateLatLong($_lat, $_long)
    {
        $this->coordinate = location_coordinate::WGS84($_lat, $_long);

    }



    /*
    * @method Coordinate
    * @param _$value
    * @return mixed
    */
    public function Coordinate(_$value)
    {
        return $this->coordinate;
    }



    /*
    * @method setElevation
    * @param _$value
    * @return mixed
    */
    public function setElevation(_$value)
    {
        $this->elevation = $_value;
    }


    /*
    * @method Elevation
    * @param _$value
    * @return mixed
    */
    public function Elevation(_$value)
    {
        return $this->elevation;
    }


    /*
    * @method setDynamic
    * @param _$value
    * @return mixed
    */
    public function setDynamic(_$value)
    {
        $this->dynamic = $_value;
    }


    /*
    * @method Dynamic
    * @return mixed
    */
    public function Dynamic()
    {
        return $this->dynamic;
    }



    /*
    * @method setRelativeTo
    * @param _$value
    * @return mixed
    */
    public function setRelativeTo(_$value)
    {
        $this->relativeTo = $_value;
    }


    /*
    * @method RelativeTo
    * @return mixed
    */
    public function RelativeTo()
    {
        return $this->dynamic;
    }




    /*
    * @method fromLatLong
    * @param $_name
    * @param $_lat
    * @param $_long
    * @return mixed
    */
    public static function fromLatLong($_name, $_lat, $_long)
    {
        $result = new location($_name);
        $result->setCoordinateLatLong($_lat, $_long);

        return $result;

    }

}
?>