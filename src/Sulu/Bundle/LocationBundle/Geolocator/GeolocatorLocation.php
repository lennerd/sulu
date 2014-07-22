<?php

namespace Sulu\Bundle\LocationBundle\Geolocator;

class GeolocatorLocation
{
    protected $displayTitle;
    protected $street;
    protected $number;
    protected $code;
    protected $town;
    protected $country;
    protected $longitude;
    protected $latitude;

    public function getDisplayTitle() 
    {
        return $this->displayTitle;
    }
    
    public function setDisplayTitle($displaytitle)
    {
        $this->displayTitle = $displaytitle;
    }

    public function getStreet() 
    {
        return $this->street;
    }
    
    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getNumber() 
    {
        return $this->number;
    }
    
    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function getCode() 
    {
        return $this->code;
    }
    
    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getTown() 
    {
        return $this->town;
    }
    
    public function setTown($town)
    {
        $this->town = $town;
    }
    
    public function getCountry() 
    {
        return $this->country;
    }
    
    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function getLongitude() 
    {
        return $this->longitude;
    }
    
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    public function getLatitude() 
    {
        return $this->latitude;
    }
    
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function toArray()
    {
        $res = array();
        foreach (array(
            'displayTitle',
            'street',
            'number',
            'code',
            'town',
            'country',
            'longitude',
            'latitude'
        ) as $propertyName)
        {
            $res[$propertyName] = $this->{'get' . ucfirst($propertyName)}();
        }

        return $res;
    }
}
