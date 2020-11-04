<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    protected $session;
    public function __construct(SessionInterface $session)
    {
        // must be logged in to view this controller
        $this->session = $session;
    }

    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        if($this->session->get('is_logged_in') == true){
            return $this->render('post/index.html.twig', ['posts' => $postRepository->findAll()]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if($this->session->get('is_logged_in') == true){
            $post = new Post();
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();

                // set user id
                $post->setUserId(intval($this->session->get('user_id')));

                $entityManager->persist($post);
                $entityManager->flush();

                return $this->redirectToRoute('post_index');
            }

            return $this->render('post/new.html.twig', [
                'post' => $post,
                'form' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET"})
     */
    public function show(Post $post): Response
    {
        if($this->session->get('is_logged_in') == true) {
            return $this->render('post/show.html.twig', [
                'post' => $post,
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post): Response
    {
        if($this->session->get('is_logged_in') == true) {
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('post_index');
            }

            return $this->render('post/edit.html.twig', [
                'post' => $post,
                'form' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if($this->session->get('is_logged_in') == true)
        {
            if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token')))
            {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($post);
                $entityManager->flush();
            }

            return $this->redirectToRoute('post_index');
        } else {
            return $this->redirectToRoute('app_login');
        }
    }
}
