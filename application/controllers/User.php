<?php

class UserController extends \ImReworks\Controller
{
    public function listAction()
    {
        $param['page'] = $this->getLegalParam('page', 'int');
        $model = new UserModel();
        $result = $model->get(1);
        //var_dump($result);
        //var_dump($model);
        $this->assign('user_list', $result);
        //$this->render('user/list.phtml');
    }

    public function userAction()
    {
        $param['page'] = $this->getLegalParam('page', 'id');
    }

    public function redisAction()
    {
        $model = new UserModel;
        $model->redis();
        
    }
}


