<?php

namespace App\Controller;

use App\Entity\Products;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Psr\Log\LoggerInterface;


class ProductController extends AbstractController
{
    /**
     * @Route("/products", name="products_get", methods={"GET"})
     */
    
    public function index(): Response
    {
        $products = $this->getDoctrine()->getRepository(Products::class)->findAll();

        $productsData = [];

        foreach ($products as $product){
            $productData = [
                "id" => $product->getId(),
                "name" => $product->getName(),
                "description" => $product->getDescription(),
                "price" => $product->getPrice(),
                "description" => $product->getDescription(),
                "img" => stream_get_contents($product->getImg()),
            ];
            array_push($productsData, $productData);
        };

        return $this->json([
            'products' => $productsData,
        ]);
    }

    /**
     * @Route("/products/new", name="products_create", methods={"POST"})
     */
    public function create(Request $request, LoggerInterface $logger)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $product = new Products();

        $product->setName($request->request->get("name"));
        $product->setPrice($request->request->get("price"));
        $product->setDescription($request->request->get("description"));

        $img = $request->files->get("img");
        $product->setImg(base64_encode(file_get_contents($img)));

        $entityManager->persist($product);

        $entityManager->flush();

        return $this->json([
            'message' => "Product created",
            'productId' => $product->getId(),
        ]);
    }

    /**
     * @Route("/product/delete/{id}")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id) 
    {
      $product = $this->getDoctrine()->getRepository(Products::class)->find($id);

      if(empty($product)) {
          return $this->json([
            'message' => "Product does not exist",
        ]);
      }

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->remove($product);
      $entityManager->flush();

      return $this->json([
            'message' => "Product deleted",
        ]);
    }

    /**
     * @Route("/product/edit/{id}", name="edit_product")
     * Method({"POST"})
     */

    public function edit(Request $request, $id, LoggerInterface $logger)
    {
        $product = new Products();
        $product = $this->getDoctrine()->getRepository(Products::class)->find($id);

        if(empty($product)) {
            return $this->json([
                'message' => "Product does not exist",
            ]);
        }

        $logger->info($request->get("name"));


        $entityManager = $this->getDoctrine()->getManager();
        $product->setName($request->request->get("name"));
        $product->setPrice($request->request->get("price"));
        $product->setDescription($request->request->get("description"));

        $img = $request->files->get("img");
        $product->setImg(base64_encode(file_get_contents($img)));

        $entityManager->persist($product);

        $entityManager->flush();

         return $this->json([
            'message' => "Product edited",
            'productId' => $product->getId(),
        ]);
    }

}
