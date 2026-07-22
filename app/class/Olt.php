<?php
class OltManager{
    public $host = [
        "cardenasChihuahua"=>"192.168.90.82",
        "central"=>"192.168.90.2",
        "meoqui"=>"192.168.90.138",
        "allende"=>"192.168.90.86",
        "parrilla"=>"192.168.90.38",
        "campestre"=>"192.168.90.34",
        "pichucalco"=>"192.168.90.58",
        "carmen"=>"192.168.90.70",//201.151.119.134
        "nacajuca"=>"192.168.90.78",
        "deliciasChihuahua"=>"192.168.90.74",
        "tacotalpa"=>"192.168.90.46",
        "paraiso"=>"192.168.90.54",
        "jalapa"=>"192.168.90.42",
        "teapa"=>"192.168.90.26",
        "comalcalco"=>"192.168.90.50",
        "pomoca"=>"192.168.90.14",
        "jalpa"=>"192.168.90.22",
        "cunduacan"=>"192.168.90.162", 
        "bosques" =>"192.168.90.98"
    ];
    public $community = [
        "cardenasChihuahua"=>"pnNe34oqcrbD",
        "central"=>"gl1v4qctFYad",
        "meoqui"=>"j6pxMuCBSXEO",
        "allende"=>"j6pxMuCBSXEp",
        "parrilla"=>"fTdl8ybpijkX",
        "campestre"=>"Qrh7puo6RIF8",
        "pichucalco"=>"JLZykMjYD6wT",
        "carmen"=>"wdGBb2PMm7no",
        "nacajuca"=>"3fDruzl6jUmo",
        "deliciasChihuahua"=>"z40hobTnfaeZ",
        "tacotalpa"=>"eqmRPZwDhbA3",
        "paraiso"=>"fk8JBmVe1XgR",
        "jalapa"=>"DzEbw9k5jxtV",
        "teapa"=>"ZJE86FOs5mhI",
        "comalcalco"=>"etjnWH1UYIkJ",
        "pomoca"=>"lraMC859OYUe",
        "jalpa"=>"ZMJOrfPp98ou",
        "cunduacan"=>"ndegKYks67dl1",
        "bosques"=>"gaSTv4qctFYad"
    ];

    public $communityWrite = [
        "cardenasChihuahua"=>"kfh7ers8WvFf",
        "central"=>"1ayAVgTthMv2",
        "meoqui"=>"1ayAVgTthMv2",
        "allende"=>"1ayAVgTthMv3",
        "parrilla"=>"ZTBdhq9xI1oi",
        "campestre"=>"zkrRJNiW8Z6T",
        "pichucalco"=>"UwlVANfIBj53",
        "carmen"=>"rRiajdpU2bgl",
        "nacajuca"=>"f0nezpwMNg1a",
        "deliciasChihuahua"=>"niwlgpae7Y6D",
        "tacotalpa"=>"OuCqsa127NEH",
        "paraiso"=>"2a64OF35Ijcg",
        "jalapa"=>"69GNIVPWpmox",
        "teapa"=>"1FxryJtsIKd4",
        "comalcalco"=>"DF9KjUJqQxW5",
        "pomoca"=>"3EAhqzX4ryb8",
        "jalpa"=>"DTRylgIwj2FW",
        "cunduacan"=>"1ayAVgTthMv5",
        "bosques"=>"1ayAVgotyhMv2"
    ];

    public $connUser = [
        "cardenasChihuahua"=>"cardenas",
        "central"=>"central",
        "meoqui"=>"meoqui",
        "allende"=>"allende",
        "parrilla"=>"parrilla",
        "campestre"=>"campestre",
        "pichucalco"=>"pichucalco",
        "carmen"=>"carmen",
        "nacajuca"=>"nacajuca",
        "deliciasChihuahua"=>"chihuahua",
        "tacotalpa"=>"tacotalpa",
        "paraiso"=>"paraiso",
        "jalapa"=>"jalapa",
        "teapa"=>"teapa",
        "comalcalco"=>"comalcalco",
        "pomoca"=>"smartoltusr",
        "jalpa"=>"smartoltusr",
        "cunduacan"=>"cunduacan", 
        "bosques"=>"bosques"
    ];

    public $connPass = [
        "cardenasChihuahua"=>"Mko0nji9!",
        "central"=>"Mko0nji9!",
        "meoqui"=>"Mko0nji9!",
        "allende"=>"Mko0nji9!",
        "parrilla"=>"Mko0nji9!",
        "campestre"=>"Mko0nji9!",
        "pichucalco"=>"Mko0nji9!",
        "carmen"=>"Mko0nji9!",
        "nacajuca"=>"Mko0nji9!",
        "deliciasChihuahua"=>"Mko0nji9!",
        "tacotalpa"=>"Mko0nji9!",
        "paraiso"=>"Mko0nji9!",
        "jalapa"=>"Mko0nji9!",
        "teapa"=>"Mko0nji9!",
        "comalcalco"=>"Mko0nji9!",
        "pomoca"=>"1qaz2wsx",
        "jalpa"=>"1qaz3edc",
        "cunduacan"=>"Mko0nji9!",
        "bosques"=>"Mko0nji9!"
    ];

    public function hostOlt(){
        return $this->host;
    }

    public function communityOlt(){
        return $this->community;
    }
    public function communityWrite(){
        return $this->communityWrite;
    }

    public function telUser(){
        return $this->connUser;
    }
    public function telPass(){
        return $this->connPass;
    }
}
