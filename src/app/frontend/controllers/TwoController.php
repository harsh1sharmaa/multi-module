<?php

namespace app\frontend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;



class TwoController extends Controller
{

    public function getDataAction()
    {
        $m = $this->mongo;
        $db = $m->store;
        $collection = $db->products;
        $ans = $collection->find();
        return $ans;
    }



    public function productsAction()
    {

        $ans = $this->getDataAction();

        $this->view->message = $ans;
    }
}
