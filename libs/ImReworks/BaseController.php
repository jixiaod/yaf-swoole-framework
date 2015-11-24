<?php
namespace ImReworks;

class BaseController extends \ImReworks\Controller
{

    public function initialize()
    {
        //echo 'exec initialize()';   
    }

    public function renderPage($name)
    {
        try {
            \Yaf_Dispatcher::getInstance()->disableView();

            $this->view->assign('css_inc_html', $this->view->render('common/css_inc.phtml'));
            $this->view->assign('js_inc_html', $this->view->render('common/js_inc.phtml'));
            $this->view->assign('content_html', $this->view->render($name. '.phtml'));

            echo $this->view->render('common/layout.phtml');

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
