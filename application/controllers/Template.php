<?php

class TemplateController extends BaseController
{
    public function listAction()
    {
        $model = new UserModel();
        $result = $model->get(1);
        //var_dump($result);
        //var_dump($model);
        $this->assign('user_list', $result);
        $this->render('user/list.phtml');
    }
}


