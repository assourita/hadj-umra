<?php

namespace App\Controller\Dashboard;

use App\Entity\News;
use App\Form\NewsType;
use App\Repository\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/dashboard/news")
 */
class NewsController extends AbstractController
{
    /**
     * @Route("/", name="dashboard_news_index", methods={"GET"})
     */
    public function index(NewsRepository $newsRepository): Response
    {
        return $this->render('dashboard/news/index.html.twig', [
            'news' => $newsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="dashboard_news_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $news = new News();
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('news_directory'),
                        $newFilename
                    );
                    $news->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            // Génération du slug
            $slug = $slugger->slug($news->getTitle());
            $news->setSlug($slug);

            $entityManager->persist($news);
            $entityManager->flush();

            $this->addFlash('success', 'Actualité créée avec succès');
            return $this->redirectToRoute('dashboard_news_index');
        }

        return $this->render('dashboard/news/new.html.twig', [
            'news' => $news,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="dashboard_news_show", methods={"GET"})
     */
    public function show(News $news): Response
    {
        return $this->render('dashboard/news/show.html.twig', [
            'news' => $news,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="dashboard_news_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, News $news, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('news_directory'),
                        $newFilename
                    );
                    $news->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            // Génération du slug
            $slug = $slugger->slug($news->getTitle());
            $news->setSlug($slug);

            $entityManager->flush();

            $this->addFlash('success', 'Actualité modifiée avec succès');
            return $this->redirectToRoute('dashboard_news_index');
        }

        return $this->render('dashboard/news/edit.html.twig', [
            'news' => $news,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="dashboard_news_delete", methods={"POST"})
     */
    public function delete(Request $request, News $news, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$news->getId(), $request->request->get('_token'))) {
            $entityManager->remove($news);
            $entityManager->flush();
            $this->addFlash('success', 'Actualité supprimée avec succès');
        }

        return $this->redirectToRoute('dashboard_news_index');
    }
}
