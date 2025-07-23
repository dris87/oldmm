<?php

/*
 * This file is part of the `All4One/Ujallas.hu` project.
 *
 * (c) https://ujallas.hu
 *
 * Developed by: Ferencz Dávid Tamás <fdt0712@gmail.com>
 * Contributed: Sipos Zoltán <sipiszoty@gmail.com>, Pintér Szilárd <leaderlala00@gmail.com >
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace All4One\AppBundle\Controller\Blog;

use All4One\NewsBundle\Entity\NewsPost;
use All4One\NewsBundle\Manager\NewsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

/**
 * Class IndexController.
 */
class IndexController extends AbstractController
{
    /**
     * @Route("hir/{slug}", name="show_news")
     * @Method("GET")
     *
     * @param NewsPost    $post
     * @param Breadcrumbs $breadcrumbs
     *
     * @return Response
     */
    public function show(NewsPost $post, Breadcrumbs $breadcrumbs): Response
    {
        $router = $this->get('router');
        $breadcrumbs->addItem('Főoldal', $router->generate('homepage'));
        $breadcrumbs->addItem('Híreink', $router->generate('list_news'));
        $breadcrumbs->addItem($post->getTitle());

        return $this->render('pages/blog/post_show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/hirek", defaults={"page" = "1"}, name="list_news")
     * @Route("/hirek/{page}", requirements={"page" = "[1-9]\d*"}, name="list_news_paginated")
     * @Method("GET")
     *
     * @param int         $page
     * @param NewsManager $newsManager
     * @param Breadcrumbs $breadcrumbs
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return Response
     */
    public function newsList(int $page, NewsManager $newsManager, Breadcrumbs $breadcrumbs): Response
    {
        $router = $this->get('router');
        $breadcrumbs->addItem('Főoldal', $router->generate('homepage'));
        $breadcrumbs->addItem('Híreink', $router->generate('list_news'));

        return $this->render('pages/blog/news_list.html.twig', [
            'posts' => $newsManager->getPostRepository()->findPosts($page),
        ]);
    }
}
