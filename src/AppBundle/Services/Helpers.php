<?php

namespace AppBundle\Services;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder; 
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response; 

class Helpers {

    public $manager;


    
    public function __construct($manager) {
        $this->manager = $manager; 
    }
    
    public function holaMundo() {
        return "Hola Mundo desde mi servicio"; 
    }
    
    public function json($data) {
        
        $normalizers = array(new GetSetMethodNormalizer() ); 
        $encoders = array("json" => new JsonEncoder() ); 
        $serializer = new Serializer($normalizers, $encoders); 
        $json = $serializer->serialize($data, 'json'); 
        
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        
        return $response; 
        
           
    }
    
}

