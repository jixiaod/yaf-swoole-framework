<?php

class Bootstrap extends Yaf_Bootstrap_Abstract
{

    public function _initConfig()
    {
        //把配置保存起来
        //$arrConfig = Yaf_Application::app()->getConfig();
        //Yaf_Registry::set('config', $arrConfig);
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher)
    {
        //注册一个插件
        //$objSamplePlugin = new SamplePlugin();
        //$dispatcher->registerPlugin($objSamplePlugin);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
        //在这里注册自己的路由协议,默认使用简单路由
        $router = $dispatcher->getRouter();

        //创建一个路由协议实例
        $router->addRoute('index',new Yaf_Route_Regex('#^/$#', array('controller'=>'index', 'action'=>'index'))); 
        $router->addRoute('country', new Yaf_Route_Rewrite('route/:country', array('controller' => 'router','action' => 'country')));
        $router->addRoute('province', new Yaf_Route_Rewrite('route/:country/:province', array('controller' => 'router','action' => 'province')));
        $router->addRoute('city', new Yaf_Route_Rewrite('route/:country/:province/:city', array('controller' => 'router','action' => 'city')));
    }

    public function _initView(Yaf_Dispatcher $dispatcher)
    {
        //在这里注册自己的view控制器，例如smarty,firekylin
    }

}
