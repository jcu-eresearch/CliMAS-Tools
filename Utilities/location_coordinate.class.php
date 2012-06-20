<?php
class location_coordinate {


    private $delimiter = "/";
    private $projection;
    private $x;
    private $y;
    private $units;

    private $xName;
    private $yName;


    /*
    * @method __construct
    * @param $_projection = "WGS84"
    * @param $_xName = "latitude"
    * @param $_yName = "longitude"
    * @param $_units = "decimal degree" {
    * @return mixed
    */
    public function __construct($_projection = "WGS84", $_xName = "latitude", $_yName = "longitude", $_units = "decimal degree") {

        $this->xName = $_xName;
        $this->yName = $_yName;
        $this->projection = $_projection;
        $this->units = $_units;

    }


    /*
    * @method X
    * @return mixed
    */
    public function X()
    {
        return $this->x;
    }


    /*
    * @method setX
    * @param $_value
    * @return mixed
    */
    public function setX($_value)
    {
        $this->x = $_value;
    }


    /*
    * @method Y
    * @return mixed
    */
    public function Y()
    {
        return $this->y;
    }


    /*
    * @method setY
    * @param $_value
    * @return mixed
    */
    public function setY($_value)
    {
        $this->y = $_value;
    }


    /*
    * @method setXY
    * @param $_valueX
    * @param $_valueY
    * @return mixed
    */
    public function setXY($_valueX, $_valueY)
    {
        $this->x = $_valueX;
        $this->y = $_valueY;
    }



    /*
    * @method Projection
    * @return mixed
    */
    public function Projection()
    {
        return $this->projection;
    }


    /*
    * @method setProjection
    * @param $_value
    * @return mixed
    */
    public function setProjection($_value)
    {
        $this->projection = $_value;
    }


    /*
    * @method Units
    * @return mixed
    */
    public function Units()
    {
        return $this->units;
    }


    /*
    * @method setUnits
    * @param $_value
    * @return mixed
    */
    public function setUnits($_value)
    {
        $this->units = $_value;
    }


    /*
    * @method __toString
    * @return string
    */
    public function  __toString() {
        return $this->xName."/".$this->yName.":".$this->x."/".$this->y;
    }


    /*
    * @method XY
    * @param {
    * @return mixed
    */
    public function XY() {
        return $this->x."/".$this->y;
    }



    /*
    * @method WGS84
    * @param $_lat
    * @param $_long
    * @return mixed
    */
    public static function WGS84($_lat, $_long)
    {
        $result = new location_coordinate("WGS84",$_lat,$_long);
        return $result;
    }
}
?>