<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $prod = $this->getDoctrine()->getRepository(Product::class)->findAll();

        return $this->render('default/index.html.twig', [
            'Products' => $prod
        ]);
    }

    /**
     * @Route("/products/{id}/", name="view_prod")
     */
    public function view_product($id)
    {
        $prod = $this->getDoctrine()->getRepository(Product::class)->find($id);

        return $this->render('default/viewproduct.html.twig', [
            'Products' => $prod
        ]);
    }

    /**
     * @Route("/checkout", name="checkout"])
     */
    public function checkout()
    {
        $session = $this->session->get('cart');
        $cartarray = [];
        $totaal = 0;
        foreach($session as $yee => $product){
            $res = $this->getDoctrine()->getRepository(Product::class)->find($yee);
            array_push($cartarray, [$yee, $product['aantal'], $res]);
            $totaal = $totaal + ($product['aantal'] * $res->getPrice());
        }

        $em = $this->getDoctrine()->getManager();

        $order = new Order();
        $order->setUser($this->getUser());
        $em->persist($order);

        $orderprod = new OrderProduct();
        $orderprod->setProduct($res);
        $orderprod->setOrderr($order);
        $orderprod->setAantal($product['aantal']);
        $em->persist($orderprod);

        $em->flush();

        $this->session->clear();

        return $this->render('default/index.html.twig', [

        ]);
    }

    /**
     * @Route("/toevoegen/{id}", name="toevoegen", methods={"GET", "POST"})
     */
    public function addtocart(Product $product)
    {
        $id = $product->getId();
        $session = $this->session->get('cart', []);
        $totaal = 0;
        if(isset($session[$id])){
            $session[$id]['aantal']++;
        } else{
            $session[$id] = array(
                'aantal' => 1,
                'naam' => $product->getName(),
                'prijs' => $product->getPrice(),
            );
        }
        $this->session->set('cart', $session);

        $cartarray = [];
        foreach($session as $yee => $product){
            $res = $this->getDoctrine()->getRepository(Product::class)->find($yee);
            array_push($cartarray, [$yee, $product['aantal'], $res]);
            $totaal = $totaal + ($product['aantal'] * $res->getPrice());
        }

        return $this->render('default/addtocart.html.twig', [
            'cart' => $cartarray,
            'totaal' => $totaal
        ]);
    }
}
