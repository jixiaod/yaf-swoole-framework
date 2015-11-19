<?php

class Base extends \ImReworks\Controller
{
    
    public function renderPage()
    {
        try {
            $site = C('APP', 'SITE');
            $this->_view->assign('jsurl', $site['jsurl']);
            $module = $this->_request->getModuleName();
            $this->_view->setScriptPath(APPLICATION_PATH . 'modules/' . $module . '/views/');
            $this->_view->assign('content_html', $this->_view->render('pages/' .$name. '.phtml'));
            $this->_view->setScriptPath(TPL_VIEW_PATH);
            echo $this->_view->render('pages/layout.phtml');
            exit;

        } catch (Exception $e) {
            Logger::write($e->__toString(), ERR);
        }
    }

    protected function renderTemplate($name)
    {
        try {
            Yaf_Dispatcher::getInstance()->disableView();
            $module = $this->_request->getModuleName();
            $this->_view->setScriptPath(APPLICATION_PATH . 'modules/' . $module . '/views/');
            return $this->_view->render('templates/' .$name. '.tpl');

        } catch (Exception $e) {
            Logger::write($e->__toString(), Zend_Log::ERR);
            return false;
        }

        return true;
    }

}
