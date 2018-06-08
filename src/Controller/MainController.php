<?php

// src/Controller/main.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller{
  /**
  * @Route("/", name="homepage")
  */
  public function index(){
    return $this->render('index.html.twig');
  }

  public function users(){
    return $this->render('users.html.twig');
  }

}

?>
