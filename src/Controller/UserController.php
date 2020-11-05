<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $passwordEncoder;
    private $session;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, SessionInterface $session){
        $this->passwordEncoder = $passwordEncoder;
        $this->session = $session;
    }

    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        if($this->session->get('is_logged_in') == true){
            return $this->render('user/index.html.twig', [
                'users' => $userRepository->findAll(),
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $plainPassword = $user->getPassword();
            $encoded       = $this->passwordEncoder->encodePassword($user, $plainPassword);

            $user->setPassword($encoded);
            $user->setCreated(date('Y-m-d H:i:s'));

            $entityManager->persist($user);
            $entityManager->flush();

            $is_logged_in = $this->session->get('is_logged_in');

            if($is_logged_in === true){
                // if logged in redirect here
                return $this->redirectToRoute('user_index');
            } else {
                // otherwise after creating account go back to login
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        if($this->session->get('is_logged_in') == true) {
            return $this->render('user/show.html.twig', [
                'user' => $user,
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }

    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        if($this->session->get('is_logged_in') == true) {
            if($this->session->get('user_id') == $user->getId()) {
                $form = $this->createForm(UserType::class, $user);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager = $this->getDoctrine()->getManager();

                    $plainPassword = $user->getPassword();
                    $encoded = $this->passwordEncoder->encodePassword($user, $plainPassword);
                    $user->setPassword($encoded);

                    $entityManager->persist($user);
                    $entityManager->flush();

                    return $this->redirectToRoute('user_index');
                }
            } else {
                return $this->redirectToRoute('user_index');
            }

            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if($this->session->get('is_logged_in') == true) {
            if($this->session->get('user_id') == $user->getId()) {
                if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($user);
                    $entityManager->flush();

                    $this->_delete_yourself();
                }
            }
            return $this->redirectToRoute('user_index');
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    private function _delete_yourself(){
        // if you delete yourself, automatically, destroy session and logout
        session_destroy();
        return $this->redirectToRoute('app_login');
    }
}
