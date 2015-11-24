<?php

class TemplateController extends \ImReworks\BaseController
{
    public function indexAction()
    {
        $user_list = array('小明', '小强');
        $this->assign('user_list', $user_list);
        $this->renderPage('user/list');
    }
}


