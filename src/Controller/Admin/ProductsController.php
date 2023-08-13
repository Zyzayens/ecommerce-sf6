<?php

namespace App\Controller\Admin;

use App\Entity\Products;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProductsFormType;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/products/index.html.twig');
    }
    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = new Products();
        
        //form et traitement 
        $productForm = $this->createForm(ProductsFormType::class, $product);
        $productForm->handleRequest($request);

        
        if($productForm->isSubmitted() && $productForm->isValid()){
            //slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);
            //prix
            $prix = $product->getPrice() * 100;
            $product->setPrice($prix);
            //traitement
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');
            return $this->redirectToRoute('admin_products_index');
        }

        return $this->renderForm('admin/products/add.html.twig', compact('productForm'));
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Products $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        //utilisateur peut editer
        $this->denyAccessUnlessGranted('PRODUCT_EDIT', $product);
        //gestion prix
        $prix = $product->getPrice() / 100;
        $product->setPrice($prix);

        //form creation et validation
        $productForm = $this->createForm(ProductsFormType::class, $product);
        $productForm->handleRequest($request);

        if($productForm->isSubmitted() && $productForm->isValid()){

            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            $prix = $product->getPrice() * 100;
            $product->setPrice($prix);

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');
            return $this->redirectToRoute('admin_products_index');
        }
        return $this->renderForm('admin/products/edit.html.twig', compact('productForm'));

    }
    
    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {
        //utilisateur peut supprimer
        $this->denyAccessUnlessGranted('PRODUCT_DELETE', $product);

        return $this->render('admin/products/index.html.twig');
    }
}