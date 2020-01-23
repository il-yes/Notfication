<?php

namespace NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $title = 'Monitor page';


        // available adapters: filesystem, apcu, redis, array, doctrine cache, etc.
        $cache = new FilesystemAdapter();
        dump($cache);
        // create a new item getting it from the cache
        $numProducts = $cache->getItem('stats.num_products');
        dump($cache);
        // assign a value to the item and save it
        $data = new \stdClass();
        $data->user = 456;
        $data->msg = 'test lorem ...';
        $numProducts->set($data);
        $cache->save($numProducts);
        dump($cache);
        // retrieve the cache item
        $piece = $cache->getItem('stats');
        dump($piece);
        $numProducts = $cache->getItem('stats.num_products');
        $numProducts->tag(['soleil', 'test']);
        $cache->save($numProducts);

        dump($numProducts->getPreviousTags(), $numProducts);
        if (!$numProducts->isHit()) {
            // ... item does not exists in the cache
            echo 'hit !!!';
        }
        // retrieve the value stored by the item
        $total = $numProducts->get();

        // remove the cache item
        $cache->deleteItem('stats.num_products');

        return $this->render('Notifications/Default/index.html.twig', ['title' => $title]);
    }
}
