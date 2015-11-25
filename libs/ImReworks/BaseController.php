<?php
namespace ImReworks;

class BaseController extends \ImReworks\Controller
{

    public function initialize()
    {
        //echo 'exec initialize()';   
        \Yaf_Dispatcher::getInstance()->disableView();
    }

    public function renderPage($name)
    {
        try {

            $this->assign('sitename', $this->yaf->config['app'][YAF_ENVIRON]['sitename']);
            $this->assign('webdomain', $this->yaf->config['app'][YAF_ENVIRON]['webdomain']);
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
