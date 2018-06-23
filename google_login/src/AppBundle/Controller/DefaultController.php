<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

require_once '../vendor/autoload.php';

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $session = null;
        $data = array("title"=>"Home", "session" => $session);
        return $this->render('default/welcome.html.twig', $data);
    }

    /**
     * @Route("/registerForm", name="registerForm")
     */
     public function registerForm(Request $request){
       $form = $this->createFormBuilder(null)
       ->setAction($this->generateUrl("registerForm"))
       ->add("name",TextType::class, array("required"=>true,"constraints"=>[
         new NotBlank(array("message"=>"Can not be blank"))
         ]))
       ->add("email",TextType::class, array("required"=>true,"constraints"=>[
         new EmailConstraint(array("message"=>"This is not correct way of typing email")),
         new NotBlank(array("message"=>"Can not be blank"))
         ]))
       ->add("myfile",FileType::class,array("constraints"=>[
           new File(array(
             "maxSize"=>"2M",
             "mimeTypes"=>[
               "application/pdf",
               "application/x-pdf"],
               "mimeTypesMessage"=>"Please upload a valid PDF"
           ))
        ]))
       ->add("save", SubmitType::class)
       ->getForm();

       $form->handleRequest($request);

       if($request->isMethod("POST")){

         if($form->isValid()){
           $file = $form->get("myfile")->getData();
           $filename = md5(uniqid ()).".".$file->guessExtension();
           $file->move("/Users/malcolmross/Documents/proj",$filename);

           return $this->render("default/regdone.html.twig", array("title"=>"Register"));
         }

       }
       return $this->render("default/index.html.twig", array("title"=>"Register", "form"=>$form->createView()));
     }

     /**
     *  @Route("/users", name="users")
     */
     public function users(Request $request){
       $em = $this->getDoctrine()->getManager();
       $conn = $em->getConnection();
       $stmt = $conn->prepare("select * from users");
       $stmt->execute();
       $results = $stmt->fetchAll();

       $data = array("title"=>"Users", "users"=>$results);
       return $this->render("default/users.html.twig", $data);
     }

     /**
     * @Route("/addForm", name="addForm")
     */
     public function addForm(Request $request){
       $data = array("title"=>"Users");
       return $this->render("default/addForm.html.twig", $data);
     }

     /**
     * @Route("/addUser", name="addUser")
     */
     public function addUser(Request $request){
       $em = $this->getDoctrine()->getManager();
       $conn = $em->getConnection();
       $stmt = $conn->prepare("insert into users (name, lastname, email) values (:name, :lastname, :email)");
       $stmt->bindValue("name", $request->get("name"));
       $stmt->bindValue("lastname", $request->get("lastname"));
       $stmt->bindValue("email", $request->get("email"));
       $stmt->execute();

       return $this->redirect("/users");
     }

     /**
     *  @Route("/getUser/{id}", name="gUser")
     */
     public function gUser(Request $request, $id){
       $em = $this->getDoctrine()->getManager();
       $conn = $em->getConnection();
       $stmt = $conn->prepare("select * from users where id = :id");
       $stmt->bindValue("id", $id);
       $stmt->execute();
       $results = $stmt->fetchAll();

       $data = array("title"=>"Users", "users"=>$results);
       return $this->render("default/updateForm.html.twig", $data);
     }

     /**
     *  @Route("/updateUser/{id}", name="updateUser")
     */
     public function updateUser(Request $request, $id){
       $em = $this->getDoctrine()->getManager();
       $conn = $em->getConnection();
       $stmt = $conn->prepare("update users set name = :name, lastname = :lastname, email = :email where id = :id");
       $stmt->bindValue("id", $id);
       $stmt->bindValue("name", $name);
       $stmt->bindValue("lastname", $lastname);
       $stmt->bindValue("email", $email);
       $stmt->execute();
       return $this->redirect("/users");
     }

     /**
     *  @Route("/deleteUser/{id}", name="deleteUser")
     */
     public function deleteUser(Request $request, $id){
       $em = $this->getDoctrine()->getManager();
       $conn = $em->getConnection();
       $stmt = $conn->prepare("delete from users where id = :id");
       $stmt->bindValue("id", $id);
       $stmt->execute();
       return $this->redirect("/users");
     }

     /**
      * @Route("/login", name="login")
      */
     public function login(){
        $client= new \Google_Client();
        $client->setApplicationName("Google Login");
        $client->setClientId("279228047266-1k18bvpvsthsdghbrd2h2mud4ggvtbe0.apps.googleusercontent.com");
        $client->setClientSecret("zBvGwtOs7iyJ96OMg9j2Q8kB");
        $client->setRedirectUri("http://localhost:8000/loginSuccess");
        $client->addScope("email");
        $url= $client->createAuthUrl();
        echo '<a href="' . $url . '">Log in with Google!</a>';die;
     }

     /**
      * @Route("/loginSuccess", name="loginSuccess")
      */
     public function loginSuccess(Request $request){
        $client= new \Google_Client();
        $client->setApplicationName("Google Login");
        $client->setClientId("279228047266-1k18bvpvsthsdghbrd2h2mud4ggvtbe0.apps.googleusercontent.com");
        $client->setClientSecret("zBvGwtOs7iyJ96OMg9j2Q8kB");
        $client->setRedirectUri("http://localhost:8000/loginSuccess");
        $service = new \Google_Service_Oauth2($client);
        $code=$client->authenticate($request->query->get("code"));
        $client->setAccessToken($code);
        $userDetails=$service->userinfo->get();
        //$_SESSION["token"] = $code;
        $session = $this->get('session');
        $session->set('auth', array(
          "token" => $code,
        ));
        $data = array("title"=>"Welcome", "session" => $session);
        return $this->render('default/welcome.html.twig', $data);
        /*echo '<pre>';
        var_dump($userDetails);die;
        echo '</pre>';
        echo '<a href="/index">Back To Home</a>';*/
     }

     /**
      * @Route("/logout", name="logout")
      */
     public function logout(Request $request){
       $session = null;
       $data = array("title"=>"Home", "session" => $session);
       return $this->render('default/welcome.html.twig', $data);
     }
}
