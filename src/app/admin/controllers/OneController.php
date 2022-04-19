<?php

namespace app\admin\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;
use app\component\Myescaper;





class OneController extends Controller
{
    public function indexAction()
    {


        echo "helo index";
    }
    public function helloAction()
    {

        echo "helo hello";
    }

    public function loginAction()
    {
        $data = $this->request->getPost();
        if (isset($data['submit'])) {
            $escp = new Myescaper();
            $email = $escp->sanitize($data['email']);
            $password = $escp->sanitize($data['password']);


            // // $m= $escp->sanitize($email);
            // echo $email;
            // die;
            if ($email == "harsh" && $password == "123") {

                $this->response->redirect('admin/one/insert');
            }
        }
    }

    public function getDataAction()
    {
        $m = $this->mongo;

        $db = $m->store;

        $collection = $db->products;
        $ans = $collection->find();
        return $ans;
    }

    public function insertAction()
    {
        echo "helo insert";
        $m = $this->mongo;
        $db = $m->store;
        $collection = $db->products;

        $data = $this->request->getPost();
        if (isset($data['search'])) {
            $productname = $data['productname'];
            $success = $collection->find(array("info.name" => $productname));
            $this->view->message = $success;
        } elseif (isset($data['submit'])) {

            $doc = $this->createdocAction($data);

            $success = $collection->insertOne($doc);
        } else {

            $data = $this->getDataAction();

            $this->view->message = $data;
        }
    }

    /**
     * this function delete the documents from collection
     *
     * @return void
     */
    public function deleteAction()
    {
        $data = $this->request->get();
        if (isset($data['submit'])) {
            $id = $data['id'];
            $this->deleteHelperAction($id);
        }
        $this->response->redirect("admin/one/insert");
    }


    /**
     * this function return the document of given id
     *
     * @return void
     */
    public function getdatabyidAction()
    {

        $id = $this->request->getpost('id');

        // echo "hello";
        // die();
        $m = $this->mongo;
        $db = $m->store;
        $collection = $db->products;

        $success = $collection->findOne(array("_id" => new \MongoDB\BSON\ObjectId("$id")));

        return  json_encode($success);
        // die;
    }
    /**
     * this function insert documents into the collection
     *
     * @return void
     */
    public function orderAction()
    {

        $data = $this->getDataAction();
        $post = $this->request->getPost();
        if (isset($post['submit']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

            $doc = $this->createOrderDocAction($post);

            $m = $this->mongo;
            $db = $m->store;
            $collection = $db->order;
            $success = $collection->insertOne($doc);
        }

        $this->view->message = $data;
    }

    public function orderlistAction()
    {
        $data = $this->request->getPost();
        $m = $this->mongo;
        $db = $m->store;
        $collection = $db->order;
        if (isset($data['status'])) {

            $status = $data['status'];
            $orderId = $data['orderId'];

            $collection->updateOne(["_id" => new \MongoDB\BSON\ObjectId("$orderId")], ['$set' => ["status" => $status]]);
            $ans = $collection->find();
            $this->view->message = $ans;
        } elseif (isset($data['submit'])) {

            $data = $this->request->getPost();
            $status = $data['getstatus'];
            $date = $data['filterdate'];

            $filterdata = $this->filterAction($status, $date);
            $this->view->message = $filterdata;
        } else {

            $ans = $collection->find();
            $this->view->message = $ans;
        }
    }

    /**
     * this function filter based on condition
     *
     * @param [type] $status
     * @param [type] $date
     * @return void
     */
    public function filterAction($status, $date)
    {

        $m = $this->mongo;
        $db = $m->store;
        $collection = $db->order;
        $todaydate = date("Y-m-d");
        $selecteddate = $todaydate;
        if ($date == "today") {
            $selecteddate = $todaydate;
        } elseif ($date == "this week") {
            $selecteddate = date('d-m-Y', strtotime($todaydate . ' -7 days'));
        } elseif ($date == "this month") {
            $selecteddate = date('d-m-Y', strtotime($todaydate . ' -30 days'));
        } else {

            $data = $this->request->getpost();
            echo "<pre>";
            print_r($data);
            $stdate = $data['stdate'];
            $endate = $data['endate'];
            $ans = $collection->find(['$and' => [["created" => ['$lte' => $endate]], ["created" => ['$gte' => $stdate]]]]);
        }
        $ans = $collection->find(['$and' => [["status" => "$status"], ['$and' => [["created" => ['$lte' => $todaydate]], ["created" => ['$gte' => $selecteddate]]]]]]);
        return $ans;
    }
    /**
     * this function create the document of inserted product
     *
     * @param [type] $data
     * @return void
     */
    public function createdocAction($data)
    {
        // $myescap = new Myescaper();
        // die;

        $myescap = new Myescaper();

        $addiCount = $data['max'];
        $variCount = $data['varimax'];

        // echo "<pre>";
        // print_r($data);
        // die();
        $name = $myescap->sanitize($data['name']);
        $myescap->sanitize($name);
        // echo $name;
        // die();
        $category = $myescap->sanitize($data['category']);
        $price = $myescap->sanitize($data['price']);
        $stock = $myescap->sanitize($data['stock']);
        $detail = array("name" => $name, "category" => $category, "price" => $price, "stock" => $stock);
        $additional = array();
        $variation = array();

        for ($i = 0; $i < $addiCount; $i++) {

            $additional = $additional + [ $myescap->sanitize($data["atname" . $i]) =>  $myescap->sanitize($data["atvalue" . $i])];
        }

        for ($i = 0; $i < $variCount; $i++) {

            $attributecount = $data['attricount' . $i];
            $objOfVariation = array();
            for ($j = 0; $j <= $attributecount; $j++) {
                $key = $myescap->sanitize($data['attriname' . $i . '' . $j]);
                $val = $myescap->sanitize($data['attrival' . $i . '' . $j]);


                $objOfVariation = $objOfVariation + [$key => $val];
            }
            array_push($variation, $objOfVariation);
        }
        $doc = array("info" => $detail, "additional" => $additional, "variation" => $variation);

        return $doc;
    }
    /**
     * this function create document of order 
     *
     * @param [type] $post
     * @return void
     */
    public function createOrderDocAction($post)
    {
        $myescap = new Myescaper();


        $productId = $post['id'];
        $variation = $post['variname'];
        $coustomername = $myescap->sanitize($post['coustomername']);
        $quantity = $myescap->sanitize($post['quantity']);
        $createdate = $myescap->sanitize(date("Y-m-d"));
        $doc = array("productId" => $productId, "variation" => $variation, "coustomername" => $coustomername, "quantity" => $quantity, "created" => $createdate, "status" => "paid");
        return $doc;
    }
    /**
     * this function delete a product of given id
     *
     * @param [type] $id
     * @return void
     */
    public function deleteHelperAction($id)
    {

        $m = $this->mongo;
        $db = $m->store;
        $collection = $db->products;
        $success = $collection->deleteOne(array("_id" => new \MongoDB\BSON\ObjectId("$id")));
        return;
    }
}
