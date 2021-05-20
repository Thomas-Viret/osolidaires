<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Department;
use App\Entity\Proposition;
use App\Form\UserLambdaType;
use App\Repository\RequestRepository;
use App\Entity\Request as RequestEntity;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PropositionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/front/requests", name="front_requests", methods={"GET"})
     */
    public function browseRequests(RequestRepository $requestRepository): Response
    {
        $requests = $requestRepository->findAllOrderedByIdDesc();
        return $this->render('front/main/request.html.twig', [
            'requests' => $requests,
        ]);
    }


    /**
     * Get one request
     * 
     * @Route("/front/request/{id<\d+>}", name="front_request_read", methods={"GET"})
     */
    public function readRequest(RequestEntity $requestModel = null): Response
    {
        if ($requestModel === null) {
            throw $this->createNotFoundException('Demande non trouvée.');
        }

        return $this->render('front/main/request_read.html.twig', [
            'request' => $requestModel,

        ]);
    }


    /**
     * @Route("/front/propositions", name="front_propositions", methods={"GET"})
     */
    public function browsePropositions(PropositionRepository $propositionRepository): Response
    {
       
        $propositions = $propositionRepository->findAllOrderedByIdDesc();
        return $this->render('front/main/proposition.html.twig', [
            'propositions' => $propositions,
        ]);
    }

    /**
     * Get one proposition
     * 
     * @Route("/front/proposition/{id<\d+>}", name="front_proposition_read", methods={"GET"})
     */
    public function readProposition(Proposition $proposition = null): Response
    {
        if ($proposition === null) {
            throw $this->createNotFoundException('Proposition non trouvée.');
        }

        return $this->render('front/main/proposition_read.html.twig', [
            'proposition' => $proposition,

        ]);
    }
    


     /**
     * 
     * 
     * @Route("/front/contact/user/{id<\d+>}", name="front_user_contact", methods={"GET"})
     */
    public function userContact(User $user)
    {
        
        return $this->render('front/main/contact.html.twig', [
            'user' => $user,
        ]);
    }


    /**
     * Show one user
     * 
     * @Route("/front/profile/user/{id<\d+>}", name="front_user_profile", methods={"GET"})
     */
    public function profile(User $user)
    {
        
        return $this->render('front/main/profile.html.twig', [
            'user' => $user,
        ]);
    }


    /**
     * Form to Edit a user
     * 
     * @Route("/front/user/edit/{id<\d+>}", name="front_user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder)
    {
         // Le User courant a-t-il le droits de modifier cette question
         $this->denyAccessUnlessGranted('patchUser', $user);
        // Creates and returns a Form instance from the type of the form (UserType).
        $form = $this->createForm(UserLambdaType::class, $user);

        // The user's password will be overwritten by $request 
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // If the form's password field is not empty 
            // that means we want to change it !
            if ($form->get('password')->getData() !== '') {

                $hashedPassword = $passwordEncoder->encodePassword($user, $form->get('password')->getData());
                $user->setPassword($hashedPassword);
            }

            // Sets the new datetime in the database updated_at field
            $user->setUpdatedAt(new \DateTime());

            // Saves the edits 
            $this->getDoctrine()->getManager()->flush();

            // Flash
            $this->addFlash('warning', 'Utilisateur modifié !');

            $id = $user->getId();

           
                
                return $this->redirectToRoute('front_user_profile',['id' => $id]);
            
        }

        return $this->render('front/main/user_edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

     /**
     * Form to add a user
     * @var UploadedFile $uploadedFile 
     * @Route("/front/user/add", name="front_user_add")
     */
    public function add(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, SluggerInterface $slugger)
    {
        // the entity to create
        $user = new User();

        // generates form
        $form = $this->createForm(UserLambdaType::class, $user);

        // we inspect the request and map the datas posted on the form
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // We encode the User's password that's inside our variable $admin
            $hashedPassword = $passwordEncoder->encodePassword($user, $user->getPassword());
            // We reassing the encoded password in the User object via $admin
            $user->setPassword($hashedPassword);

           

            // saves the new user
            $entityManager->persist($user);
            $entityManager->flush();

            // Flash
            $this->addFlash('success', 'Utilisateur créé avec succès !');

            $role = $user->getRoles()[0];

           
            return $this->redirectToRoute('front_requests');
       
        }

        return $this->render('front/main/user_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
