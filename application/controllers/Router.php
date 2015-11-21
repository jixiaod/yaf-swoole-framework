<?php

class RouterController extends \ImReworks\Controller
{
    public function countryAction()
    {
        $country = $this->getLegalParam('country', 'str');
        
        echo $country;

        return false;
    }

    public function provinceAction()
    {
        $country    = $this->getLegalParam('country', 'str');
        $province   = $this->getLegalParam('province', 'str');
        echo $country;
        echo $province;

        return false;
    }
    
    public function cityAction()
    {
        $country    = $this->getLegalParam('country', 'str');
        $province   = $this->getLegalParam('province', 'str');
        $city       = $this->getLegalParam('city', 'str');

        echo $country;
        echo $province;
        echo $city;

        return false;
    }

}


